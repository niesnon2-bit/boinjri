FROM php:8.2-cli

# تثبيت الإضافات المطلوبة
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring

# تفعيل خيارات إضافية
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory.ini
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/memory.ini
RUN echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/memory.ini

# نسخ الملفات
COPY . /app
WORKDIR /app

# تشغيل PHP Server
CMD php -S 0.0.0.0:${PORT:-8080} -t .
