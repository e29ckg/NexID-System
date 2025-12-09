FROM php:8.1-apache

# ติดตั้ง PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# เปิดใช้งาน mod_rewrite ของ Apache (ถ้าใช้ Router)
RUN a2enmod rewrite