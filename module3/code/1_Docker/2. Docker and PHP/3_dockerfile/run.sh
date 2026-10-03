# Build image with tag "my-php-app"
docker build -t my-php-app .

docker run --rm -p 8000:8000 my-php-app