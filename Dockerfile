#Use pre-builded container
FROM ghcr.io/uspacy/laravel-core-docker:main

ARG NEW_RELIC_LICENSE_KEY
ARG NEW_RELIC_APPNAME
ARG NEW_RELIC_DAEMON_SVC

RUN sed -i -e "s/REPLACE_WITH_REAL_KEY/${NEW_RELIC_LICENSE_KEY}/" \
    -e "s/newrelic.appname[[:space:]]=[[:space:]].*/newrelic.appname=\"${NEW_RELIC_APPNAME}\"/" \
    -e "s/;newrelic.framework =.*/newrelic.framework="laravel"/" \
    -e 's/;newrelic.transaction_tracer.enabled = true/newrelic.transaction_tracer.enabled = true/' \
    -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
    -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
    #-e 's/;newrelic.daemon.address = .*/newrelic.daemon.address=127.0.0.1:31339'/ \
    $(php -r "echo(PHP_CONFIG_FILE_SCAN_DIR);")/newrelic.ini

RUN rm /usr/local/etc/php/conf.d/newrelic.ini

MAINTAINER ms@alterego.digital

### CONST ENV
ENV COMPOSER_MEMORY_LIMIT='-1'
###
ARG COMPOSER_AUTH

#Copy code
COPY ./ ./
# Fix permissions
RUN chown -R app.app ./

#Switch user
USER app

#Fix storage directories
RUN mkdir -p storage/framework/sessions
RUN mkdir -p storage/framework/views
RUN mkdir -p storage/framework/cache
# Install packeges from composer
RUN composer install
