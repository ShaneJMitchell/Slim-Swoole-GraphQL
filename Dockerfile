FROM php:7.3-cli

#Serve port
ARG PORT=8888

#Install Modules
RUN apt-get update -y && \
    apt-get install -y \
    wget \
    libmcrypt-dev \
    openssl \
    libssl-dev \
    curl \
    libcurl4-gnutls-dev \
    zlib1g-dev \
    libpng-dev \
    zip \
    libzip-dev \
    unzip \
    libc6 \
    libxml2-dev \
    libxslt1-dev \
    libicu-dev \
     && pecl install mcrypt-1.0.2

#Enable Extensions
RUN docker-php-ext-install \
    mbstring \
    zip \
    json \
    ctype \
    curl \
    soap \
    xml \
    intl \
    mbstring \
    iconv \
    bcmath \
    && docker-php-ext-enable mcrypt

#Install Swoole
RUN cd /tmp && \
    wget https://pecl.php.net/get/swoole-4.4.0.tgz && \
    tar zxvf swoole-4.4.0.tgz && \
    cd swoole-4.4.0  && \
    phpize  && \
    ./configure  --enable-openssl && \
    make && \
    make install && \
    touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

#Dont forget composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
CMD ["php","/usr/bin/composer install"]

WORKDIR /app
COPY . /app

ENV APP_PORT=${PORT}
CMD ["php","/app/public/index.php"]
