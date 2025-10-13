# Use Apache HTTP Server with PHP
FROM php:8.2-apache

# Copy the frontend files to the Apache document root
COPY frontend/ /var/www/html/

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
