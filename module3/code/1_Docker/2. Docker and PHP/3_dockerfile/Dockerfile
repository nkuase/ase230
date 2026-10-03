# Dockerfile
FROM php:8.4-cli

# Set working directory
WORKDIR /app

# Copy PHP files
COPY . .

# Expose port 8000
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000"]
