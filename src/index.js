#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const sjcl = require('sjcl');

/**
 * Wrapper for sjcl.mode.ccm.decrypt and sjcl.cipher.aes
 * @param {string} derivedKey - hex string
 * @param {string} cipherText - hex string
 * @param {string} initVector - hex string
 * @param {string} authData - ascii string
 * @param {number} tagLenBits - integer
 * @returns {string} clearText - ascii string
 */
function sjclCCMdecrypt(derivedKey, cipherText, initVector, authData, tagLenBits) {
  // AES pseudorandom function based on derived key
  const derivedKeyBits = sjcl.codec.hex.toBits(derivedKey);
  const prf = new sjcl.cipher.aes(derivedKeyBits);
  
  // Convert cipher text and iv to bitArrays
  const cipherTextBits = sjcl.codec.hex.toBits(cipherText);
  const initVectorBits = sjcl.codec.hex.toBits(initVector);
  
  // Convert ascii string authData to bitArray, then hex string, finally to bitArray
  let authDataBits = sjcl.codec.utf8String.toBits(authData);
  authDataBits = sjcl.codec.hex.fromBits(authDataBits);
  authDataBits = sjcl.codec.hex.toBits(authDataBits);
  
  // Decrypt with params
  const pt = sjcl.mode.ccm.decrypt(prf, cipherTextBits, initVectorBits, authDataBits, tagLenBits);
  
  if (pt.length > 0) {
    return sjcl.codec.utf8String.fromBits(pt);
  } else {
    console.log('Error: Decrypted text is empty');
    return '';
  }
}

/**
 * Extract encryption data from the Analysis.md file
 * @returns {Object} The encryption parameters
 */
function getEncryptionData() {
  try {
    const analysisPath = path.join(__dirname, '..', 'docs', 'Analysis.md');
    const analysisContent = fs.readFileSync(analysisPath, 'utf8');
    
    // Extract the JSON part from the markdown file
    const jsonMatch = analysisContent.match(/```json\s*([\s\S]*?)\s*```/);
    
    if (!jsonMatch || !jsonMatch[1]) {
      throw new Error('Could not find JSON data in Analysis.md');
    }
    
    return JSON.parse(jsonMatch[1]);
  } catch (error) {
    console.error('Error reading encryption data:', error.message);
    process.exit(1);
  }
}

/**
 * Main function to decrypt and display connected devices
 */
function decryptConnectedDevices() {
  try {
    // Get encryption data
    const encryptionData = getEncryptionData();
    
    // Extract parameters
    const { derivedKey, cipherText, initVector, authData, tagLenBits } = encryptionData;
    
    // Decrypt the data
    const decryptedText = sjclCCMdecrypt(derivedKey, cipherText, initVector, authData, tagLenBits);
    
    // Parse as JSON
    const connectedDevices = JSON.parse(decryptedText);
    
    // Output the result
    console.log(JSON.stringify(connectedDevices, null, 2));
    
    return connectedDevices;
  } catch (error) {
    console.error('Error decrypting connected devices:', error.message);
    process.exit(1);
  }
}

// Run the main function if this script is executed directly
if (require.main === module) {
  decryptConnectedDevices();
}

// Export for use as a module
module.exports = {
  decryptConnectedDevices,
  sjclCCMdecrypt
};