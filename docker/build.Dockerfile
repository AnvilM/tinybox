FROM php:8.5-cli-alpine AS pharbuilder

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN wget -q -O /usr/local/bin/box \
        "https://github.com/box-project/box/releases/latest/download/box.phar" \
    && chmod +x /usr/local/bin/box

COPY ../composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --classmap-authoritative \
        --no-scripts \
        --no-plugins \
        --no-interaction \
        --ignore-platform-reqs \
        --quiet

COPY ../box.json ./

COPY ../app ./app
COPY ../src ./src
COPY ../tinybox.php ./tinybox.php

RUN php -d phar.readonly=0 /usr/local/bin/box compile


FROM alpine:3.21 AS spcbuilder

WORKDIR /spc

RUN apk add --no-cache \
        bash \
        curl \
        wget \
        git \
        zip \
        unzip \
        tar \
        xz \
        make \
        cmake \
        ninja \
        autoconf \
        automake \
        libtool \
        pkgconf \
        patch \
        bison \
        flex \
        re2c \
        perl \
        python3 \
        g++ \
        gcc \
        musl-dev \
        linux-headers \
        upx

RUN curl -fsSL \
        -o ./spc \
        "https://dl.static-php.dev/static-php-cli/spc-bin/nightly/spc-linux-x86_64" \
    && chmod +x ./spc

RUN ./spc doctor --auto-fix

ARG PHP_VERSION=8.5
ARG EXTENSIONS="bcmath,curl,iconv,intl,mbstring,openssl,sodium,phar,zlib,filter"

RUN ./spc download \
        --for-extensions="${EXTENSIONS}" \
        --with-php="${PHP_VERSION}" \
        --prefer-pre-built

RUN ./spc install-pkg upx

RUN ./spc build "${EXTENSIONS}" \
        --build-micro \
        --with-upx-pack



FROM alpine:3.21 AS combiner

WORKDIR /out

COPY --from=spcbuilder /spc/buildroot/bin/micro.sfx ./micro.sfx
COPY --from=pharbuilder /build/app.phar ./app.phar

ARG APP_NAME=app
ARG VERSION=dev

RUN cat micro.sfx app.phar > "${APP_NAME}-${VERSION}-linux-x86_64" \
    && chmod +x "${APP_NAME}-${VERSION}-linux-x86_64"



FROM scratch AS export

ARG APP_NAME=app
ARG VERSION=dev

COPY --from=combiner /out/${APP_NAME}-${VERSION}-linux-x86_64 /${APP_NAME}-${VERSION}-linux-x86_64