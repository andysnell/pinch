# syntax=docker/dockerfile:1
ARG BASE_IMAGE=php:8.4-fpm

##------------------------------------------------------------------------------
# Caddy Webserver Build Stages
##------------------------------------------------------------------------------

FROM caddy:latest AS pinch-web
RUN apk upgrade --no-cache

##------------------------------------------------------------------------------
# Prettier Code Formatter Build Stages
##------------------------------------------------------------------------------

FROM node:alpine AS pinch-prettier
ENV NPM_CONFIG_PREFIX=/home/node/.npm-global
ENV PATH=$PATH:/home/node/.npm-global/bin
WORKDIR /app
RUN npm install --global --save-dev --save-exact npm@latest prettier
ENTRYPOINT ["prettier"]

##------------------------------------------------------------------------------
# PHP Base Stage for Application Development
##------------------------------------------------------------------------------

# The Sodium extension originally compiled with PHP is based on an older version
# of the libsodium library provided by Debian. Since it was compiled as a shared
# extension, we can compile the latest stable version of libsodium from source and
# rebuild the extension. We grab the latest stable version of libsodium from their
# official releases, verify its authenticity with minisign and their published
# Ed25519 public key, and then get it ready for compilation.
FROM ${BASE_IMAGE} AS libsodium
WORKDIR /usr/src/libsodium
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get update
RUN --mount=type=cache,target=/var/lib/apt apt-get install --yes --quiet --no-install-recommends minisign
RUN curl -fsSL --remote-name-all https://download.libsodium.org/libsodium/releases/libsodium-1.0.20-stable.tar.gz{,.minisig}
RUN minisign -VP RWQf6LRCGA9i53mlYecO4IzT51TGPpvWucNSCh1CBM0QTaLn73Y7GFO3 -m libsodium-1.0.20-stable.tar.gz
RUN tar -xzf libsodium-1.0.20-stable.tar.gz --strip-components=1
RUN ./configure
RUN make -j $(nproc) && make -j $(nproc) check

FROM ${BASE_IMAGE} AS pinch-php
WORKDIR /
SHELL ["/bin/bash", "-c"]
ENV COMPOSER_CACHE_DIR="/app/build/composer/cache"
ENV COMPOSER_HOME="/home/dev/.composer"
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
ENV PHP_EXT_DIR="/usr/local/lib/php/extensions/no-debug-non-zts-20240924"
ENV PINCH_BUILD_STAGE="development"
ENV XDEBUG_MODE="off"

# Start with the base image's production defaults for PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Update the package list and install the latest version of the packages
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked <<-EOF
  set -eux;
  export DEBIAN_FRONTEND="noninteractive"
  apt-get update;
  apt-get upgrade --yes --quiet;
  apt-get install --yes --quiet --no-install-recommends \
      git \
      jq \
      less \
      libgmp-dev \
      libzip-dev \
      librabbitmq-dev \
      unzip \
      vim-tiny \
      zip;
  # Create a symlink for vim to use the tiny version
  ln -s /usr/bin/vim.tiny /usr/bin/vim;
  mkdir -p /home/dev/.composer;
EOF

RUN docker-php-ext-install bcmath exif gmp pcntl pdo_mysql zip

# Install Composer, the PHP package manager, using the official Composer image
COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
COPY --link --from=composer/composer /tmp/* /home/dev/.composer/
COPY --link --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

RUN <<-EOF
    set -eux;
    pie install -j$(nproc) php-amqp/php-amqp:dev-latest
    pie install -j$(nproc) igbinary/igbinary:dev-master
    pie install -j$(nproc) phpredis/phpredis
    pie install -j$(nproc) xdebug/xdebug --skip-enable-extension;
EOF

# Compile the libsodum library and install the sodium extension using the libsodium library files prepared earlier
RUN --mount=type=bind,from=libsodium,source=/usr/src/libsodium,target=/usr/src/libsodium <<-EOF
    set -eux;
    make -C /usr/src/libsodium -j $(nproc) install
    docker-php-ext-install -j$(nproc) sodium
EOF

# Create a non-root user for running the application (Note: the build args can invalidate the cache)
WORKDIR /app
