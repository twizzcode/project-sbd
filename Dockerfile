FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Redis installation removed

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory
COPY . /var/www/html

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
