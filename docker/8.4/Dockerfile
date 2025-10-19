FROM php:8.4-fpm

ARG WWWGROUP

WORKDIR /var/www/html

ENV TZ=Europe/Kyiv
ENV SUPERVISOR_PHP_COMMAND="/usr/local/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y \
    git \
    curl \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    librdkafka-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*


RUN pecl install rdkafka-6.0.5 && \
    docker-php-ext-enable rdkafka

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini

RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
