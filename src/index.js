#!/usr/bin/env node

// Load environment variables from .env file located in the parent directory
const fs = require("fs").promises; // Use promises for async file operations
const path = require("path");
const sjcl = require("sjcl");
const axios = require("axios");
const { CookieJar } = require("tough-cookie");
const { wrapper } = require("axios-cookiejar-support");
const { argv } = require("process"); // For argument parsing
const { Console } = require("console");
// Load environment variables from .env file located in the current working directory (process.cwd())
// This makes loading optional by default if the file doesn't exist.
require('dotenv').config({ path: path.resolve(process.cwd(), '.env') });

// --- Configuration ---
// Helper function to parse simple command line arguments like --key=value or --key value
function getArgValue(key) {
  const argIndex = argv.findIndex(arg => arg === key || arg.startsWith(key + '='));
  if (argIndex === -1) return undefined;
  const arg = argv[argIndex];
  if (arg.includes('=')) {
    return arg.split('=')[1];
  }
  // Check if the next argument is the value (and not another flag)
  if (argIndex + 1 < argv.length && !argv[argIndex + 1].startsWith('--')) {
    return argv[argIndex + 1];
  }
  return undefined; // Or handle cases where flag is present but value is missing
}

// Helper function to check for flags (like --quiet or -q)
function hasArgFlag(key, shortKey) {
    return argv.includes(key) || (shortKey && argv.includes(shortKey));
}

// --- Logging Control ---
const isQuiet = hasArgFlag('--quiet', '-q');

// Conditional logger for informational messages
const logInfo = (...args) => {
    if (!isQuiet) {
        console.log(...args);
    }
};

// Logger for errors (always prints to stderr)
const logError = (...args) => {
    console.error(...args);
};

// Logger for the final result (always prints to stdout)
const logResult = (...args) => {
    console.log(...args);
};


// Router Credentials (Args > Env > Defaults)
const argUser = getArgValue('--user');
const argPass = getArgValue('--pass');
const envUser = process.env.ROUTER_USER;
const envPass = process.env.ROUTER_PASS;
const routerUser = argUser || envUser; // Default user
const routerPassword = argPass || envPass; // Default password (consider removing default for security)

if (!routerUser || !routerPassword) {
  logError("Router username and password must be provided either as command line arguments or environment variables.");
  process.exit(1);
  return;
}



// Router URL (Args > Env > Default)
const argUrl = getArgValue('--url');
const envUrl = process.env.ROUTER_URL;
const routerUrl = argUrl || envUrl || "http://10.0.0.1"; // Default URL

// Session Cache Path (Args > Env > Default)
const argCacheDir = getArgValue('--cache-dir');
const envCacheDir = process.env.SESSION_CACHE_DIR;
const cacheDir = argCacheDir || envCacheDir || __dirname; // Default to script directory if not set
const cacheFileName = ".session_cache.json";
const cacheFilePath = path.resolve(cacheDir, cacheFileName); // Use resolve for absolute path

// Ensure cache directory exists
(async () => {
  try {
    await fs.mkdir(path.dirname(cacheFilePath), { recursive: true });
  } catch (err) {
    logError(`Error creating cache directory ${path.dirname(cacheFilePath)}:`, err);
    process.exit(1);
  }
})();


// Other Constants
// const ROUTER_URL = "http://10.0.0.1"; // Replaced by dynamic routerUrl
const LOGIN_PAGE_URL = `${routerUrl}/login.php`; // Use dynamic URL
const LOGIN_ACTION_URL = `${routerUrl}/actionHandler/ajaxSet_login.php`; // Use dynamic URL
const SESSION_TAKEOVER_URL = `${routerUrl}/actionHandler/ajaxSet_SessionActive.php`; // Use dynamic URL
const DEVICES_URL = `${routerUrl}/actionHandler/ajaxSet_connected_devices.php`; // Use dynamic URL
const AUTH_DATA = "ARRIS";
const PBKDF2_ITERATIONS = 1000;
const KEY_SIZE_BITS = 128;
const TAG_LENGTH_BITS = 128;
// const CACHE_FILE_PATH = path.join(__dirname, ".session_cache.json"); // Replaced by dynamic path

// --- Global Axios Client with Cookie Jar ---
// Initialize jar globally, but it might be replaced by loaded cache later
let jar = new CookieJar();
const client = wrapper(
  axios.create({
    jar, // Use the global jar instance
    baseURL: routerUrl, // Use dynamic URL
    timeout: 15000,
    validateStatus: (status) => status >= 200 && status < 300,
  })
);

// --- SJCL Helper Functions ---

function sjclPbkdf2(password, saltHex, iterations, keySizeBits) {
  const salt = sjcl.codec.hex.toBits(saltHex);
  return sjcl.misc.pbkdf2(password, salt, iterations, keySizeBits);
}

function sjclCCMEncrypt(key, plaintext, ivHex, authData, tagLenBits) {
  const prf = new sjcl.cipher.aes(key);
  const iv = sjcl.codec.hex.toBits(ivHex);
  const plaintextBits = sjcl.codec.utf8String.toBits(plaintext);
  let authDataBits = sjcl.codec.utf8String.toBits(authData);
  authDataBits = sjcl.codec.hex.fromBits(authDataBits);
  authDataBits = sjcl.codec.hex.toBits(authDataBits);
  const ciphertextBits = sjcl.mode.ccm.encrypt(prf, plaintextBits, iv, authDataBits, tagLenBits);
  return sjcl.codec.hex.fromBits(ciphertextBits);
}

function sjclCCMDecrypt(key, ciphertextHex, ivHex, authData, tagLenBits) {
  if (typeof ciphertextHex !== "string") {
    if (typeof ciphertextHex === "object" && ciphertextHex.hasOwnProperty("EncryptedData")) {
      ciphertextHex = ciphertextHex.EncryptedData;
    } else {
      throw new Error("Invalid ciphertext format.");
    }
  }
  if (!ciphertextHex) throw new Error("Invalid ciphertext: Received empty or null value.");

  const prf = new sjcl.cipher.aes(key);
  const iv = sjcl.codec.hex.toBits(ivHex);
  const ciphertext = sjcl.codec.hex.toBits(ciphertextHex);
  let authDataBits = sjcl.codec.utf8String.toBits(authData);
  authDataBits = sjcl.codec.hex.fromBits(authDataBits);
  authDataBits = sjcl.codec.hex.toBits(authDataBits);
  const plaintextBits = sjcl.mode.ccm.decrypt(prf, ciphertext, iv, authDataBits, tagLenBits);
  return sjcl.codec.utf8String.fromBits(plaintextBits);
}

// --- Session Cache Functions ---

/**
 * Saves session data to the cache file.
 * @param {string} csrfToken
 * @param {sjcl.BitArray} derivedKey
 * @param {string} sessionIv
 * @param {CookieJar} currentJar
 */
async function saveSessionCache(csrfToken, derivedKey, sessionIv, currentJar) {
  try {
    const cacheData = {
      csrfToken,
      derivedKeyHex: sjcl.codec.hex.fromBits(derivedKey), // Store key as hex
      sessionIv,
      cookieJarJson: currentJar.toJSON(), // Serialize the jar
      timestamp: Date.now(), // Add timestamp for potential expiry logic later
    };
    await fs.writeFile(cacheFilePath, JSON.stringify(cacheData, null, 2)); // Use dynamic path
    logInfo(`Session data cached successfully to ${cacheFilePath}.`);
  } catch (error) {
    logError("Error saving session cache:", error.message);
    // Don't exit, just log the error
  }
}

/**
 * Loads session data from the cache file.
 * @returns {Promise<{csrfToken: string, derivedKey: sjcl.BitArray, sessionIv: string, loadedJar: CookieJar}|null>}
 */
async function loadSessionCache() {
  try {
    const cacheContent = await fs.readFile(cacheFilePath, "utf8"); // Use dynamic path
    const cacheData = JSON.parse(cacheContent);

    // Basic validation
    if (!cacheData.csrfToken || !cacheData.derivedKeyHex || !cacheData.sessionIv || !cacheData.cookieJarJson) {
      logInfo("Cache file is incomplete. Ignoring cache.");
      await clearSessionCache(); // Clear invalid cache
      return null;
    }

    // Optional: Add expiry check based on timestamp if needed
    // const cacheAge = Date.now() - cacheData.timestamp;
    // if (cacheAge > SOME_EXPIRY_TIME) {
    //     logInfo("Cache expired. Ignoring cache.");
    //     await clearSessionCache();
    //     return null;
    // }

    const derivedKey = sjcl.codec.hex.toBits(cacheData.derivedKeyHex); // Convert hex back to BitArray
    const loadedJar = CookieJar.fromJSON(cacheData.cookieJarJson); // Deserialize jar

    logInfo("Session data loaded from cache.");
    return {
      csrfToken: cacheData.csrfToken,
      derivedKey,
      sessionIv: cacheData.sessionIv,
      loadedJar,
    };
  } catch (error) {
    if (error.code === "ENOENT") {
      logInfo("Cache file not found. Will perform login.");
    } else {
      logError("Error loading session cache:", error.message);
      // Attempt to clear potentially corrupt cache
      await clearSessionCache();
    }
    return null;
  }
}

/**
 * Deletes the session cache file.
 */
async function clearSessionCache() {
    try {
      await fs.unlink(cacheFilePath); // Use dynamic path
      logInfo(`Session cache cleared (${cacheFilePath}).`);
    } catch (error) {
      if (error.code !== 'ENOENT') { // Ignore if file doesn't exist
        logError("Error clearing session cache:", error.message);
      }
    }
}


// --- Router Interaction Functions ---

function extractLoginCryptoParams(htmlContent) {
  const saltMatch = htmlContent.match(/sjclEncryptObj\.salt\s*=\s*"([a-fA-F0-9]+)"/);
  const ivMatch = htmlContent.match(/sjclEncryptObj\.iv\s*=\s*"([a-fA-F0-9]+)"/);
  if (!saltMatch || !saltMatch[1]) throw new Error("Could not extract salt from login page HTML.");
  if (!ivMatch || !ivMatch[1]) throw new Error("Could not extract IV from login page HTML.");
  return { salt: saltMatch[1], iv: ivMatch[1] };
}

async function login() {
  logInfo("Attempting login...");
  let dynamicSalt, dynamicIv, loginKey;
  try {
    logInfo(`Fetching login page (${LOGIN_PAGE_URL})...`);
    const getLoginResponse = await client.get(LOGIN_PAGE_URL, { headers: { Accept: "text/html,*/*", "User-Agent": "Mozilla/5.0" } });
    const loginPageHtml = getLoginResponse.data;

    const cryptoParams = extractLoginCryptoParams(loginPageHtml);
    dynamicSalt = cryptoParams.salt;
    dynamicIv = cryptoParams.iv;
    logInfo(`Extracted Salt: ${dynamicSalt}, IV: ${dynamicIv}`);

    loginKey = sjclPbkdf2(routerPassword, dynamicSalt, PBKDF2_ITERATIONS, KEY_SIZE_BITS); // Use dynamic password

    const loginData = { username: routerUser, password: routerPassword }; // Use dynamic user/pass
    const encryptedLoginData = sjclCCMEncrypt(loginKey, JSON.stringify(loginData), dynamicIv, AUTH_DATA, TAG_LENGTH_BITS);
    const payload = { EncryptedData: encryptedLoginData, user: routerUser.toLowerCase() }; // Use dynamic user

    logInfo(`Sending login credentials for user '${routerUser}' to ${LOGIN_ACTION_URL}...`);
    let putLoginResponse;
    try {
        putLoginResponse = await client.put(LOGIN_ACTION_URL, payload, {
          headers: { "Content-Type": "application/json", Accept: "*/*", "X-Requested-With": "XMLHttpRequest", Referer: LOGIN_PAGE_URL, Origin: routerUrl, "User-Agent": "Mozilla/5.0" }, // Use dynamic URL for Origin
        });
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 471) {
             logError("Login failed: Server responded with 471 (Decryption Failure).");
        } else {
            logError("Initial login PUT request failed.");
        }
        throw error;
    }

    let csrfToken = putLoginResponse.headers["x-csrf-token"];

    if (!csrfToken) {
      logInfo("CSRF token missing. Checking for session takeover...");
      let decryptedResponseData;
      try {
        decryptedResponseData = JSON.parse(sjclCCMDecrypt(loginKey, putLoginResponse.data, dynamicIv, AUTH_DATA, TAG_LENGTH_BITS));
      } catch (e) {
        logError("Failed to decrypt/parse login response body:", e.message, "Raw data:", putLoginResponse.data);
        throw new Error("Login failed: CSRF token missing and response body couldn't be processed.");
      }

      if (decryptedResponseData?.session_overtake === true) {
        logInfo("Session takeover required. Attempting...");
        const takeoverPayload = { session_takeover: true };
        const encryptedTakeoverPayload = sjclCCMEncrypt(loginKey, JSON.stringify(takeoverPayload), dynamicIv, AUTH_DATA, TAG_LENGTH_BITS);
        const finalTakeoverPayload = { EncryptedData: encryptedTakeoverPayload, user: routerUser.toLowerCase() }; // Use dynamic user

        const takeoverResponse = await client.put(SESSION_TAKEOVER_URL, finalTakeoverPayload, {
           headers: { "Content-Type": "application/json", Accept: "*/*", "X-Requested-With": "XMLHttpRequest", Referer: LOGIN_PAGE_URL, Origin: routerUrl, "User-Agent": "Mozilla/5.0" }, // Use dynamic URL for Origin
        });

        csrfToken = takeoverResponse.headers["x-csrf-token"];
        if (!csrfToken) throw new Error("Login failed: Session takeover attempted, but still no CSRF token.");
        logInfo("Session takeover successful.");
      } else {
        throw new Error("Login failed: CSRF token missing and session takeover condition not met.");
      }
    }

    logInfo("Login successful (CSRF token obtained).");
    // IMPORTANT: The global 'jar' has been updated by axios-cookiejar-support during the requests.
    // We return the key/iv/token, and the caller will save the *current* global jar state.
    return { csrfToken, derivedKey: loginKey, sessionIv: dynamicIv };

  } catch (error) {
    logError("Login process failed:");
    if (axios.isAxiosError(error)) {
      logError(`Error Message: ${error.message}`);
      if (error.response) logError(`Status: ${error.response.status}, Data: ${JSON.stringify(error.response.data)}`);
      else if (error.request) logError("No response received.");
    } else logError(`Error: ${error.message}`);
    if (dynamicSalt && dynamicIv) logError(`Attempted with Salt: ${dynamicSalt}, IV: ${dynamicIv}`);
    process.exit(1);
  }
}

async function fetchConnectedDevices(csrfToken) {
  logInfo(`Fetching connected devices from ${DEVICES_URL}...`);
  try {
    const response = await client.get(DEVICES_URL, {
      headers: { Accept: "*/*", "X-Requested-With": "XMLHttpRequest", "X-CSRF-Token": csrfToken, Referer: `${routerUrl}/connected_devices.php`, "User-Agent": "Mozilla/5.0" }, // Use dynamic URL for Referer
    });
    if (!response.data?.EncryptedData) throw new Error("EncryptedData not found in response body.");
    logInfo("Encrypted device data received.");
    return response.data.EncryptedData;
  } catch (error) {
    // Only log the error details if it's NOT an expected auth error in quiet mode
    const isExpectedAuthError = axios.isAxiosError(error) && (error.response?.status === 401 || error.response?.status === 403);
    if (!isQuiet || !isExpectedAuthError) {
        logError("Failed to fetch connected devices:");
        if (axios.isAxiosError(error)) {
            logError(`Error Message: ${error.message}`);
            if (error.response) logError(`Status: ${error.response.status}, Data: ${JSON.stringify(error.response.data)}`);
            else if (error.request) logError("No response received.");
        } else {
            logError(`Error: ${error.message}`);
        }
    }

    // Mark the error if it's an auth error, regardless of quiet mode
    if (isExpectedAuthError) {
        error.isAuthError = true; // Mark error for cache handling in main()
    }

    throw error; // Re-throw the error to be handled in main()
  }
}

// --- Main Execution ---

async function main() {
  let sessionData = null;
  let encryptedDevicesData = null;
  let needsLogin = true; // Assume login is needed initially

  // 1. Try loading from cache
  const cachedSession = await loadSessionCache();

  if (cachedSession) {
    logInfo("Attempting to use cached session...");
    // IMPORTANT: Update the global jar instance used by the client
    jar = cachedSession.loadedJar;
    client.defaults.jar = jar; // Update the client's jar reference

    try {
      // 2. Try fetching data with cached credentials
      encryptedDevicesData = await fetchConnectedDevices(cachedSession.csrfToken);
      // If fetch succeeded, use cached data for decryption
      sessionData = cachedSession;
      needsLogin = false; // Login not needed
      logInfo("Cached session is valid.");
    } catch (error) {
      // Check if the error indicates an invalid/expired session
      if (error.isAuthError || (axios.isAxiosError(error) && (error.response?.status === 401 || error.response?.status === 403))) {
        logInfo("Cached session is invalid or expired. Clearing cache and logging in again.");
        await clearSessionCache();
        needsLogin = true; // Force login
      } else {
        // Different error occurred during fetch, log it and exit
        logError("An unexpected error occurred while using cached session:", error.message);
        process.exit(1);
      }
    }
  }

  // 3. Perform login if needed
  if (needsLogin) {
    sessionData = await login(); // This performs the full login/takeover logic
    // Save the newly obtained session data (csrf, key, iv, and the *current* state of the global jar)
    await saveSessionCache(sessionData.csrfToken, sessionData.derivedKey, sessionData.sessionIv, jar);
    // Fetch devices using the new session
    encryptedDevicesData = await fetchConnectedDevices(sessionData.csrfToken);
  }

  // 4. Decrypt and display data (using either cached or new session data)
  if (sessionData && encryptedDevicesData) {
    let decryptedText = null;
    try {
      logInfo("Decrypting device data...");
      decryptedText = sjclCCMDecrypt(sessionData.derivedKey, encryptedDevicesData, sessionData.sessionIv, AUTH_DATA, TAG_LENGTH_BITS);

      logInfo("Parsing decrypted data...");
      const connectedDevices = JSON.parse(decryptedText);
      // Always log the final result to stdout
      logResult(JSON.stringify(connectedDevices, null, 2));
    } catch (error) {
      // Handle decryption/parsing errors
      if (error instanceof sjcl.exception.corrupt) {
        logError("Decryption failed: Data corrupt or key/IV incorrect.");
        if (sessionData.sessionIv) logError(`Decryption attempted with IV: ${sessionData.sessionIv}`);
      } else if (error instanceof SyntaxError && error.message.includes("JSON")) {
        logError("Decryption likely succeeded, but result is not valid JSON.");
        if (decryptedText !== null) logError("Decrypted text (first 500 chars):", decryptedText.slice(0, 500) + (decryptedText.length > 500 ? "..." : ""));
      } else {
        logError("An error occurred during decryption/parsing:", error.message);
      }
      // If login was successful but decryption failed, maybe clear cache?
      // await clearSessionCache(); // Optional: clear cache if decryption fails
      process.exit(1);
    }
  } else {
      logError("Could not obtain session data or device data. Exiting.");
      process.exit(1);
  }
}

main();

module.exports = {
  login, fetchConnectedDevices, sjclCCMDecrypt, sjclCCMEncrypt, sjclPbkdf2, extractLoginCryptoParams, saveSessionCache, loadSessionCache, clearSessionCache
};
