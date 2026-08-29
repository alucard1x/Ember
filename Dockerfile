FROM php:8.2-apache

# mysqli is what config.php uses; rewrite is for .htaccess
RUN docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && printf 'display_errors=Off\nlog_errors=On\nerror_log=/dev/stderr\n' \
       > /usr/local/etc/php/conf.d/zz-production.ini

# Allow .htaccess overrides inside the docroot
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Render injects $PORT at runtime; Apache must bind to it instead of 80.
COPY docker-entrypoint.sh /usr/local/bin/render-entrypoint.sh
RUN chmod +x /usr/local/bin/render-entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/render-entrypoint.sh"]
CMD ["apache2-foreground"]
