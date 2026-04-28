FROM php:8.2-cli

# تثبيت المكتبات الأساسية
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# تثبيت الإضافات
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    zip

# إعدادات PHP
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory.ini && \
    echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/memory.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/memory.ini

# نسخ الملفات
COPY . /app
WORKDIR /app

# البورت
EXPOSE 8080

# تشغيل الخادم
CMD php -S 0.0.0.0:${PORT:-8080} -t .
