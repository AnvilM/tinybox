FROM php:8.4-cli-alpine3.23 AS builder

# Arguments
ARG VERSION
RUN test -n "$VERSION" || (echo "VERSION is required" && exit 1)

WORKDIR /app

# Installing spc
RUN wget -qO- https://dl.static-php.dev/static-php-cli/spc-bin/nightly/spc-linux-x86_64.tar.gz | tar -xz
RUN chmod +x ./spc
RUN ./spc download php-src --for-extensions "iconv" --with-php=8.4
RUN ./spc install-pkg upx
RUN ./spc doctor --auto-fix
RUN ./spc build --build-micro "iconv" --with-upx-pack

# Installing composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installing packages 
COPY composer.json ./
RUN composer install --optimize-autoloader --no-dev

# Copying src
COPY app ./app
COPY src ./src
COPY composer.json ./
COPY tinybox.php ./

# Building binary
RUN ./spc micro:combine tinybox.php --output=artifact

FROM scratch AS export

# Arguments
ARG VERSION

COPY --from=builder /app/artifact /tinybox-${VERSION}-linux64