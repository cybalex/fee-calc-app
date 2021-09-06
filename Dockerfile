FROM php:7.4-alpine

ENV COMPOSER_VERSION 2.1.6

RUN set -ex; \
    docker-php-ext-install bcmath 1>/dev/null

RUN set -ex; \
    mkdir -p /usr/local/bin \
    && curl -LsS https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar -o /usr/local/bin/composer \
    && chmod a+x /usr/local/bin/composer \
    && composer self-update --no-interaction 2>/dev/null

RUN set -ex; \
    apk add --update $PHPIZE_DEPS \
    && pecl install pcov \
	&& docker-php-ext-enable pcov

RUN mkdir -p /var/www/fee-calculator
WORKDIR /var/www/fee-calculator
