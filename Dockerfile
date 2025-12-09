FROM php:8.1-apache

# ติดตั้ง Extension ที่จำเป็น (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# เปิดใช้งาน mod_rewrite ของ Apache (สำคัญมากสำหรับการทำ Router หรือลบ .php ทิ้ง)
RUN a2enmod rewrite

# ตั้งค่า Permission (Optional: แก้ปัญหาเขียนไฟล์ไม่ได้ในบางเครื่อง)
RUN chown -R www-data:www-data /var/www/html