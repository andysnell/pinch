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
# PHP Base Stages for Development & Production
##------------------------------------------------------------------------------

# Docker does not support variable expansion in "COPY --from" statements, so we have to
# use an intermediate image that essentially retags the base image.
FROM ${BASE_IMAGE} AS build-php-source

FROM debian:trixie-slim AS build-apt-list
RUN DEBIAN_FRONTEND="noninteractive" apt-get update;

FROM debian:trixie-slim AS build-debian
ENV DEBIAN_FRONTEND="noninteractive"
SHELL ["/bin/bash", "-c"]
RUN --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw <<-EOF
    set -eux;
    echo -e "Package: php*\nPin: release *\nPin-Priority: -1" > /etc/apt/preferences.d/no-debian-php;
    apt-get upgrade --yes --quiet --no-install-recommends;
    apt-get install --yes --quiet --no-install-recommends ca-certificates curl minisign xz-utils
EOF

##------------------------------------------------------------------------------
# The Sodium extension originally compiled with PHP is based on an older version
# of the libsodium library provided by Debian. Since it was compiled as a shared
# extension, we can compile the latest stable version of libsodium from source and
# rebuild the extension. We grab the latest stable version of libsodium from their
# official releases, verify its authenticity with minisign and their published
# Ed25519 public key, and then get it ready for compilation.
##------------------------------------------------------------------------------

FROM build-debian AS build-libsodium
WORKDIR /usr/src/libsodium
RUN --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw \
    --mount=type=cache,target=/var/cache/apt,sharing=locked <<-EOF
    set -eux;
    apt-get install --yes --quiet --no-install-recommends build-essential;
EOF
RUN curl -fsSL --remote-name-all https://download.libsodium.org/libsodium/releases/libsodium-1.0.20-stable.tar.gz{,.minisig}
RUN minisign -VP RWQf6LRCGA9i53mlYecO4IzT51TGPpvWucNSCh1CBM0QTaLn73Y7GFO3 -m libsodium-1.0.20-stable.tar.gz
RUN tar -xzf libsodium-1.0.20-stable.tar.gz --strip-components=1
RUN ./configure
RUN make -j $(nproc) && make -j $(nproc) check

FROM build-debian AS build-php-ext-libs
RUN --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw \
    --mount=type=cache,target=/var/cache/apt,sharing=locked <<-EOF
  set -eux;
  apt-get install --yes --quiet --no-install-recommends \
    libargon2-dev \
    libcurl4-openssl-dev \
    libgmp-dev \
    libicu-dev \
    libonig-dev \
    librabbitmq-dev \
    libreadline-dev \
    libsqlite3-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev
EOF
COPY --link --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
COPY --link --from=composer/composer /tmp/* /home/dev/.composer/

FROM build-php-ext-libs AS build-php
SHELL ["/bin/bash", "-c"]
RUN --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw \
    --mount=type=cache,target=/var/cache/apt,sharing=locked <<-EOF
  set -eux;
  apt-get install --yes --quiet --no-install-recommends \
    autoconf \
    dpkg-dev \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkg-config \
    re2c;
EOF

FROM build-php-ext-libs AS pinch-php
STOPSIGNAL SIGQUIT
ENTRYPOINT ["docker-php-entrypoint"]
EXPOSE 9000
CMD ["php-fpm"]
ENV PHP_INI_DIR="/usr/local/etc/php"
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_LDFLAGS="-Wl,-O1 -pie"
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
COPY --link --from=build-php-source /usr/local/bin/docker-* /usr/local/bin/
RUN --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw \
    --mount=type=cache,target=/var/cache/apt,sharing=locked <<-EOF
  set -eux;
  apt-get install --yes --quiet --no-install-recommends \
      git \
      less \
      unzip \
      vim-tiny \
      zip;
  ln -s /usr/bin/vim.tiny /usr/bin/vim;
  mkdir -p /app;
  mkdir -p /home/dev/
  chown www-data:www-data /app;
  chmod 1777 /app
EOF

RUN --mount=type=bind,from=build-php,source=/usr/bin,target=/usr/bin \
    --mount=type=bind,from=build-php,source=/usr/include,target=/usr/include \
    --mount=type=bind,from=build-php,source=/usr/lib,target=/usr/lib \
    --mount=type=bind,from=build-php,source=/usr/libexec,target=/usr/libexec \
    --mount=type=bind,from=build-php,source=/usr/share,target=/usr/share \
    --mount=type=bind,from=build-libsodium,source=/usr/src/libsodium,target=/usr/src/libsodium <<-EOF
    set -eux;
    make -C /usr/src/libsodium -j $(nproc) install
    find /usr/local/lib/ -type f -name '*.a' -delete
EOF

RUN --mount=type=bind,rw,from=build-php,source=/usr/bin,target=/usr/bin \
    --mount=type=bind,rw,from=build-php,source=/usr/include,target=/usr/include \
    --mount=type=bind,rw,from=build-php,source=/usr/lib,target=/usr/lib \
    --mount=type=bind,rw,from=build-php,source=/usr/libexec,target=/usr/libexec \
    --mount=type=bind,rw,from=build-php,source=/usr/share,target=/usr/share \
    --mount=type=bind,from=build-php-source,source=/usr/src/php.tar.xz,target=/usr/src/php.tar.xz \
    --mount=type=bind,from=build-apt-list,source=/var/lib/apt,target=/var/lib/apt,rw \
    --mount=type=cache,target=/root/.pie \
    --mount=type=cache,target=/root/.composer \
    --mount=type=cache,target=/var/cache/apt,sharing=locked <<-EOF
  set -eux;
  cp -r "/usr/include/$(dpkg-architecture --query DEB_BUILD_MULTIARCH)/curl" /usr/local/include/curl
  cd /usr/src;
  export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" PHP_BUILD_PROVIDER='https://github.com/docker-library/php' PHP_UNAME='Linux - Docker' ;
  docker-php-source extract;
  cd /usr/src/php;
  ./configure \
    --build="$(dpkg-architecture --query DEB_BUILD_GNU_TYPE)" \
    --disable-cgi \
    --disable-phpdbg \
    --enable-bcmath \
    --enable-exif \
    --enable-fpm \
    --enable-intl \
    --enable-mbstring \
    --enable-mysqlnd \
    --enable-opcache \
    --enable-option-checking=fatal \
    --enable-pcntl \
    --with-config-file-path="$PHP_INI_DIR" \
    --with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
    --with-curl \
    --with-fpm-group=www-data \
    --with-fpm-user=www-data \
    --with-gmp \
    --with-iconv \
    --with-libdir="lib/$(dpkg-architecture --query DEB_BUILD_MULTIARCH)" \
    --with-mhash \
    --with-openssl \
    --with-password-argon2 \
    --with-pdo-sqlite=/usr \
    --with-pdo-mysql \
    --with-pic \
    --with-readline \
    --with-sodium \
    --with-sqlite3=/usr \
    --with-zip \
    --with-zlib;
  make -j$(nproc)
  find -type f -name '*.a' -delete
  make -j$(nproc) install
  make clean;
  mkdir -p /usr/local/etc/php/conf.d;
  cp -v php.ini-* "$PHP_INI_DIR/";
  cp -v php.ini-production "$PHP_INI_DIR/php.ini";
  cd /;
  docker-php-source delete;
  find /usr/local -type f -executable -exec ldd '{}' '; ' | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' | sort -u | xargs -rt dpkg-query --search | awk 'sub(":$", "", $1) { print $1 }' | sort -u | xargs -r apt-mark manual ;
  php --version

  # Enable Opcache (Will Always Be Enabled in PHP 8.5+)
  docker-php-ext-enable opcache

  # Note: Must target the "latest" branch until a new release is made (see https://github.com/php/pie/issues/445)
  # For Xdebug, install but do not enable it, allowing same base image for production and development
  pie install -j$(nproc) php-amqp/php-amqp:dev-latest;
  pie install -j$(nproc) igbinary/igbinary:^3.2.17@beta
  pie install -j$(nproc) phpredis/phpredis
  pie install -j$(nproc) xdebug/xdebug --skip-enable-extension;

  cd /usr/local/etc;
  if [ -d php-fpm.d ]; then \
      sed 's!=NONE/!=!g' php-fpm.conf.default | tee php-fpm.conf > /dev/null; \
      cp php-fpm.d/www.conf.default php-fpm.d/www.conf; \
  else \
      mkdir php-fpm.d; \
      cp php-fpm.conf.default php-fpm.d/www.conf; \
      { echo '[global]'; echo 'include=etc/php-fpm.d/*.conf'; } | tee php-fpm.conf; \
  fi;

  { echo '[global]'; echo 'error_log = /proc/self/fd/2'; echo; echo '; https://github.com/docker-library/php/pull/725#issuecomment-443540114'; echo 'log_limit = 8192'; echo; echo '[www]'; echo '; php-fpm closes STDOUT on startup, so sending logs to /proc/self/fd/1 does not work.'; echo '; https://bugs.php.net/bug.php?id=73886'; echo 'access.log = /proc/self/fd/2'; echo; echo 'clear_env = no'; echo; echo '; Ensure worker stdout and stderr are sent to the main error log.'; echo 'catch_workers_output = yes'; echo 'decorate_workers_output = no'; } | tee php-fpm.d/docker.conf; { echo '[global]'; echo 'daemonize = no'; echo; echo '[www]'; echo 'listen = 9000'; } | tee php-fpm.d/zz-docker.conf; mkdir -p "$PHP_INI_DIR/conf.d"; { echo '; https://github.com/docker-library/php/issues/878#issuecomment-938595965'; echo 'fastcgi.logging = Off'; } > "$PHP_INI_DIR/conf.d/docker-fpm.ini"
EOF

WORKDIR /app
