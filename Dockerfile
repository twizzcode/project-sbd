FROM php:8.2-fpm

# Install Nginx
RUN apt-get update && apt-get install -y nginx

# Copy project files
COPY . /app
WORKDIR /app

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port
EXPOSE 8080

# Start PHP-FPM dan Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
