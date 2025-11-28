# Base image with PHP 8.4
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    nano \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    libldap2-dev \
    zlib1g-dev \
    zip \
    git \
    supervisor \
    telnet \
    net-tools \
    libldap2-dev \
    nginx \
    ffmpeg \
    cron  # Install cron

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql ldap mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel application to container
COPY . .

# Set permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Add custom Nginx and PHP configuration
COPY ./php.ini /usr/local/etc/php/
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf

# Install Supervisor configuration
COPY ./supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Add cron job
RUN echo "* * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

# Expose HTTP and HTTPS ports
EXPOSE 80
EXPOSE 443

# Entrypoint script to start Supervisor and services
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

ENTRYPOINT ["/start.sh"]
