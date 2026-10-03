docker run --rm -p 8000:8000 -v "$(pwd):/app" -w /app \
  php:8.4-cli php -S 0.0.0.0:8000