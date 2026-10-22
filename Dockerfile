# Official PHP 8.5 with Apache.
# PHP 8.5.12 includes fixes for CVE-2026-109618 and CVE-2026-109619.
FROM php:8.5.12-apache

# Show the PHP version in Render's build logs.
RUN php -v

# 1. Install system dependencies for cURL and PDO.
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# 2. Install PHP extensions.
RUN docker-php-ext-install pdo pdo_mysql curl

# 3. Set the web root to public_html.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public_html

# Apache resolves this environment variable when reading its configuration.
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Enable Apache mod_rewrite.
RUN a2enmod rewrite

# 5. Copy the repository into the container.
COPY . /var/www/html/

WORKDIR /var/www/html/

EXPOSE 80
