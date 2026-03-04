FROM php:8.4-cli-alpine3.23 AS pharbuilder


WORKDIR /app


# Installing composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# Installing box
RUN wget -O box.phar "https://github.com/box-project/box/releases/latest/download/box.phar" \
  && chmod +x box.phar 


# Copying composer.json
COPY composer.json ./


# Installing packages
RUN composer install --optimize-autoloader --no-dev


# Copying src
COPY app ./app
COPY src ./src
COPY tinybox.php ./


# Copying box config
COPY box.json ./


# Building phar
RUN ./box.phar compile

FROM php:8.4-cli-alpine3.23 AS pscbuilder


WORKDIR /app


# Installing spc
RUN wget -qO- https://dl.static-php.dev/static-php-cli/spc-bin/nightly/spc-linux-x86_64.tar.gz | tar -xz
RUN chmod +x ./spc
RUN ./spc download php-src --for-extensions "iconv,phar,zlib" --with-php=8.4
RUN ./spc install-pkg upx
RUN ./spc doctor --auto-fix
RUN ./spc build --build-micro "iconv,phar,zlib" --with-upx-pack


# Copying phar
COPY --from=pharbuilder /build/artifact.phar ./artifact.phar


# Building binary
RUN ./spc micro:combine artifact.phar --output=artifact

FROM scratch AS export


ARG VERSION


COPY --from=pscbuilder /app/artifact /tinybox-${VERSION}-linux64
