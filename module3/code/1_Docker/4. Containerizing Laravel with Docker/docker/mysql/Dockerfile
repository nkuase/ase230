FROM mysql:8.0

# Allow remote connections (for Docker networking)
ENV MYSQL_ROOT_HOST=%

# Minimal configuration for MySQL 8.0 compatibility
RUN echo '[mysqld]' > /etc/mysql/conf.d/laravel.cnf \
    && echo 'default-authentication-plugin=mysql_native_password' >> /etc/mysql/conf.d/laravel.cnf

EXPOSE 3306
