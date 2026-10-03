# Use official PHP with FPM (FastCGI Process Manager)
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install basic PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY www /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]