# Arris Connected Devices Fetcher

This script connects to an Arris router (like the TG3442DE), logs in, and fetches the list of currently connected devices.

## Configuration

The script can be configured using a `.env` file in the project root, environment variables, or command-line arguments. The order of precedence is:

1.  **Command-line arguments** (e.g., `--user`, `--pass`)
2.  **Environment variables** (e.g., `ROUTER_USER`, `ROUTER_PASS`) - These can be set directly in your shell or passed via Docker's `--env` or `--env-file` options.
3.  **`.env` file** (loaded automatically when running locally with `node index.js`, or via `docker run --env-file .env`)
4.  **Default values** hardcoded in the script (least preferred).

**Configuration Options:**

| Feature         | Argument        | Environment Variable / `.env` Key | Default Value          | Description                                      |
|-----------------|-----------------|-----------------------------------|------------------------|--------------------------------------------------|
| Router URL      | `--url`         | `ROUTER_URL`                      | `http://10.0.0.1`      | The base URL of the router's web interface.      |
| Username        | `--user`        | `ROUTER_USER`                     | `admin`                | The username for logging into the router.        |
| Password        | `--pass`        | `ROUTER_PASS`                     | EMPTY         | The password for logging into the router.        |
| Cache Directory | `--cache-dir`   | `SESSION_CACHE_DIR`               | `/app/cache` (Volume)  | Directory to store the `.session_cache.json`.    |

**`.env` File:**

A sample configuration file `.env.sample` is provided in the project root. To use it:

1.  Copy the sample file to `.env`:
    ```bash
    # On Linux/macOS/Git Bash
    cp .env.sample .env
    # On Windows Command Prompt
    copy .env.sample .env
    # On Windows PowerShell
    Copy-Item .env.sample .env
    ```
2.  Edit the new `.env` file and replace the placeholder values with your actual router credentials and URL.

Example `.env` content:
```dotenv
ROUTER_URL=http://10.0.0.1
ROUTER_USER=admin
ROUTER_PASS=your_actual_password
# SESSION_CACHE_DIR=/optional/path/for/cache
```
*(The `.env` file itself is ignored by Git and Docker builds by default, but `.env.sample` can be committed.)*

**Note on Cache Directory:**
*   When running **without Docker**, the default cache directory is the one containing `index.js` (i.e., the `src` directory). You can override this with `--cache-dir` or `SESSION_CACHE_DIR` in the `.env` file or environment.
*   When running **with Docker**, the `/app/cache` directory is declared as a `VOLUME` in the Dockerfile. This means:
    *   If you **do not** explicitly mount anything to `/app/cache` using `-v`, Docker will automatically create an anonymous volume. This volume **persists** the cache file even if you use `--rm`.
    *   If you **do** mount a host directory (`-v /host/path:/app/cache`) or a named volume (`-v my-volume:/app/cache`), that external storage will be used instead, overriding the anonymous volume.

## Running with Docker

**Prerequisites:**
*   Docker installed and running.

**1. Build the Image:**
Navigate to the project root directory (where the `Dockerfile` and `.env` files are located) and run:
```bash
# Ensure you have rebuilt the image after recent changes
docker build -t arris-devices .
```

**2. Run the Container:**

*   **Using the `.env` file (Recommended):**
    Place your configuration in the `.env` file and run:
    ```bash
    docker run --rm --env-file .env arris-devices
    ```
    *(This passes the variables from `.env` into the container's environment. Cache persists in an anonymous volume.)*

*   **Using `.env` file and mounting a host cache directory:**
    ```bash
    docker run --rm --env-file .env \
      -v /path/on/host/for/cache:/app/cache \
      arris-devices
    ```
    *(Replace `/path/on/host/for/cache` with an actual path on your machine.)*

*   **Overriding `.env` with command-line arguments (cache persists in anonymous volume):**
    ```bash
    docker run --rm \
      arris-devices \
      --url "http://your.router.ip" \
      --user "your_username" \
      --pass "your_password"
    ```
    *(Cache will be stored in an anonymous Docker volume at `/app/cache` and persist across runs)*

*   **Using arguments and mounting a host directory for cache (overrides anonymous volume):**
    ```bash
    docker run --rm \
      -v /path/on/host/for/cache:/data \
      arris-devices \
      --url "http://your.router.ip" \
      --user "your_username" \
      --pass "your_password" \
      --cache-dir /data
    ```
    *(Note: The internal path `/data` must match the `--cache-dir` argument.)*

## Running without Docker (using Node.js)

**Prerequisites:**
*   Node.js (which includes npm) installed.

**1. Navigate to the Source Directory:**
From the project root directory:
```bash
cd src
```

**2. Install Dependencies (if not already done):**
```bash
npm install
```
*(This installs the `dotenv` package needed to read the `.env` file from the parent directory)*

**3. Run the Script:**

*   **Using the `.env` file (Recommended):**
    Place your `.env` file in the directory from which you run the `node` command. The script will automatically load it from `process.cwd()`.
    Example (running from project root):
    ```bash
    # Ensure .env is in the project root
    node src/index.js
    ```
    Example (running from src directory):
    ```bash
    # Ensure .env is in the src directory (if you want to use one here)
    cd src
    node index.js
    ```
    *(Cache file will be created in `src/.session_cache.json` by default unless overridden in `.env` or by arguments)*

*   **Using command-line arguments (Overrides `.env` and defaults):**
    ```bash
    node index.js --url "http://your.router.ip" --user "your_username" --pass "your_password" --cache-dir "/path/for/cache"
    ```
    *(Replace placeholders. If `--cache-dir` is omitted, the cache file will be created in the `src` directory unless `SESSION_CACHE_DIR` is set in `.env`)*

*   **Using environment variables (Overrides `.env` and defaults):**
    (Example for PowerShell)
    ```powershell
    $env:ROUTER_URL="http://other.router.ip"; node index.js
    ```