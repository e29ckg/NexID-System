FROM php:8.1-apache

# 1. ติดตั้ง System Dependencies ที่จำเป็น (Git, Zip, Unzip)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip

# 2. ติดตั้ง PHP Extensions
RUN docker-php-ext-install pdo pdo_mysql

# 3. ติดตั้ง Composer (ดึงมาจาก Official Image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Config Apache
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html