# Use an official Node.js runtime as a parent image
FROM node:20-alpine

# Set the working directory in the container
WORKDIR /app

# Copy package.json and package-lock.json (if available)
COPY src/package*.json ./

# Install app dependencies
RUN npm install --omit=dev

# Copy the rest of the application code from src to /app
COPY src/ .

# Define the directory for the session cache
ENV SESSION_CACHE_DIR=/app/cache
# Declare /app/cache as a volume. Docker will manage this directory.
# If no volume is mounted externally, Docker creates an anonymous volume here,
# ensuring data persistence even with --rm.
VOLUME ${SESSION_CACHE_DIR}

# Make port 8080 available to the world outside this container (if the app runs a server, adjust if needed)
# EXPOSE 8080 # Assuming the app might run a server, uncomment if needed.

# Define the command to run the app
ENTRYPOINT ["node", "index.js"]

# Default command arguments (can be overridden)
CMD []