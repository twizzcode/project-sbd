FROM php:8.1-fpm

# Install system dependencies + Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install Redis
RUN pecl install redis && docker-php-ext-enable redis

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (PENTING: mysqli harus di-install)
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Set working directory ke /app (sesuai error path)
WORKDIR /app

# Copy existing application directory
COPY . /app

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Change ownership of our applications
RUN chown -R www-data:www-data /app

# Expose port
EXPOSE 8080

# Start PHP-FPM and Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
