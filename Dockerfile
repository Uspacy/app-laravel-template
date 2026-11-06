#Use pre-builded container
ARG CORE_TAG
FROM ghcr.io/uspacy/laravel-core-docker:${CORE_TAG}

MAINTAINER ms@alterego.digital

### CONST ENV
ENV COMPOSER_MEMORY_LIMIT='-1'
###
ARG COMPOSER_AUTH

#Copy code
COPY ./ ./
# Fix permissions
RUN chown -R app:app ./

#Switch user
USER app

# Install packages from composer
RUN composer install
RUN php artisan route:cache && composer dump-autoload
