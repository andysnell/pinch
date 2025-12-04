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
  apt-get install --yes --quiet --no-install-recommends \
      git \
      jq \
      less \
      libicu-dev \
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

# Install Composer and PIE from their , using the official Composer image
COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
COPY --link --from=composer/composer /tmp/* /home/dev/.composer/
COPY --link --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
RUN <<-EOF
  set -eux;
  composer completion bash > /etc/bash_completion.d/composer;
  pie completion bash > /etc/bash_completion.d/pie;
EOF

# Install "Bundled" PHP Extensions
RUN docker-php-ext-install -j$(nproc) bcmath exif gmp intl pcntl pdo_mysql zip

# Compile the libsodum library and install the sodium extension using the libsodium library files prepared earlier
RUN --mount=type=bind,from=libsodium,source=/usr/src/libsodium,target=/usr/src/libsodium <<-EOF
    set -eux;
    make -C /usr/src/libsodium -j $(nproc) install
    docker-php-ext-install -j$(nproc) sodium
EOF

RUN --mount=type=cache,target=/root/.pie \
    --mount=type=cache,target=/root/.composer \
    --mount=type=bind,target=/app <<-EOF
    set -eux;
    # Note: Must target the "latest" branch until a new release is made (see https://github.com/php/pie/issues/445)
    pie install -j$(nproc) php-amqp/php-amqp:dev-latest;
    # Install PHP extensions required in root composer.json
    pie install -j$(nproc) --allow-non-interactive-project-install --working-dir=/app
    # Install the Xdebug extension, but do not enable it, allowing same base image for production and development
    pie install -j$(nproc) xdebug/xdebug --skip-enable-extension;
EOF

# Create a non-root user for running the application (Note: the build args can invalidate the cache)
ENV COMPOSER_CACHE_DIR="/app/build/composer/cache"
ENV COMPOSER_HOME="/home/dev/.composer"
WORKDIR /app

FROM ${BASE_IMAGE} AS php-base-image

FROM debian:trixie-slim AS test
SHELL ["/bin/bash", "-c"]
ENV PHPIZE_DEPS="autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c"
ENV PHP_INI_DIR="/usr/local/etc/php"
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_LDFLAGS="-Wl,-O1 -pie"
ENV GPG_KEYS="AFD8691FDAEDF03BDF6E460563F15A9B715376CA 9D7F99A0CB8F05C8A6958D6256A97AF7600A39A6 0616E93D95AF471243E26761770426E17EBBB3DD"
ENV PHP_VERSION="8.4.15"
ENV PHP_URL="https://www.php.net/distributions/php-8.4.15.tar.xz"
ENV PHP_ASC_URL="https://www.php.net/distributions/php-8.4.15.tar.xz.asc"
ENV PHP_SHA256="a060684f614b8344f9b34c334b6ba8db1177555997edb5b1aceab0a4b807da7e"
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

RUN <<-EOF
  set -eux;
  echo -e "Package: php*\nPin: release *\nPin-Priority: -1" > /etc/apt/preferences.d/no-debian-php
  mkdir -p "$PHP_INI_DIR/conf.d";
  mkdir -p /app;
  chown www-data:www-data /app;
  chmod 1777 /app
EOF

RUN --mount=type=cache,target=/var/lib/apt,sharing=locked DEBIAN_FRONTEND="noninteractive" apt-get update;

RUN --mount=type=cache,target=/var/lib/apt,sharing=locked <<-EOF
  set -eux;
  export DEBIAN_FRONTEND="noninteractive"
  apt-get update;
  apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    ca-certificates \
    curl \
    git \
    gnupg \
    jq \
    less \
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
    unzip \
    vim-tiny \
    xz-utils \
    zip \
    zlib1g-dev
  mkdir -p /usr/src;
  cd /usr/src;
  curl -fsSL -o php.tar.xz "$PHP_URL";

  if [ -n "$PHP_SHA256" ]; then \
    echo "$PHP_SHA256 *php.tar.xz" | sha256sum -c -; \
  fi;

  curl -fsSL -o php.tar.xz.asc "$PHP_ASC_URL";
  export GNUPGHOME="$(mktemp -d)";
  for key in $GPG_KEYS; do \
      gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
  done;

  gpg --batch --verify php.tar.xz.asc php.tar.xz;
  gpgconf --kill all;
  rm -rf "$GNUPGHOME";

EOF

RUN --mount=type=bind,from=libsodium,source=/usr/src/libsodium,target=/usr/src/libsodium make -C /usr/src/libsodium -j $(nproc) install

COPY --link --from=php-base-image /usr/local/bin/docker-* /usr/local/bin/

RUN <<-EOF
  set -eux;
  export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" PHP_BUILD_PROVIDER='https://github.com/docker-library/php' PHP_UNAME='Linux - Docker' ;
  docker-php-source extract;
  cd /usr/src/php;

  gnuArch="$(dpkg-architecture --query DEB_BUILD_GNU_TYPE)";
  debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)";

  if [ ! -d /usr/include/curl ]; then \
      ln -sT "/usr/include/$debMultiarch/curl" /usr/local/include/curl; \
  fi;

  ./configure \
    --build="$gnuArch" \
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
    --with-libdir="lib/$debMultiarch" \
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

  make -j "$(nproc)";
  find -type f -name '*.a' -delete;
  make -j "$(nproc)" install;
  find /usr/local -type f -perm '/0111' -exec sh -euxc ' strip --strip-all "$@" || : ' -- '{}' + ;
  make clean;
  cp -v php.ini-* "$PHP_INI_DIR/";
  cd /;
  docker-php-source delete;
  find /usr/local -type f -executable -exec ldd '{}' '; ' | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' | sort -u | xargs -rt dpkg-query --search | awk 'sub(":$", "", $1) { print $1 }' | sort -u | xargs -r apt-mark manual ;
  php --version
EOF

# Install Composer and PIE from their , using the official Composer image
COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
COPY --link --from=composer/composer /tmp/* /home/dev/.composer/

RUN --mount=type=cache,target=/root/.pie \
    --mount=type=cache,target=/root/.composer \
    --mount=type=bind,from=ghcr.io/php/pie:bin,source=/pie,target=/usr/bin/pie <<-EOF
    set -eux;
    docker-php-ext-enable opcache
    # Note: Must target the "latest" branch until a new release is made (see https://github.com/php/pie/issues/445)
    # For Xdebug, install but do not enable it, allowing same base image for production and development
    pie install -j$(nproc) php-amqp/php-amqp:dev-latest;
    pie install -j$(nproc) igbinary/igbinary:^3.2.17@beta
    pie install -j$(nproc) phpredis/phpredis
    pie install -j$(nproc) xdebug/xdebug --skip-enable-extension;
EOF

ENTRYPOINT ["docker-php-entrypoint"]

WORKDIR /app

RUN <<-EOF
  set -eux;
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

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

STOPSIGNAL SIGQUIT

EXPOSE 9000

CMD ["php-fpm"]

FROM debian:trixie-slim AS test-php
SHELL ["/bin/bash", "-c"]
ENV PHPIZE_DEPS="autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c"
ENV PHP_INI_DIR="/usr/local/etc/php"
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_LDFLAGS="-Wl,-O1 -pie"
ENV GPG_KEYS="AFD8691FDAEDF03BDF6E460563F15A9B715376CA 9D7F99A0CB8F05C8A6958D6256A97AF7600A39A6 0616E93D95AF471243E26761770426E17EBBB3DD"
ENV PHP_VERSION="8.4.15"
ENV PHP_URL="https://www.php.net/distributions/php-8.4.15.tar.xz"
ENV PHP_ASC_URL="https://www.php.net/distributions/php-8.4.15.tar.xz.asc"
ENV PHP_SHA256="a060684f614b8344f9b34c334b6ba8db1177555997edb5b1aceab0a4b807da7e"
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
RUN <<-EOF
  set -eux;
  echo -e "Package: php*\nPin: release *\nPin-Priority: -1" > /etc/apt/preferences.d/no-debian-php
  mkdir -p /app;
  chown www-data:www-data /app;
  chmod 1777 /app
EOF
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked <<-EOF
  set -eux;
  export DEBIAN_FRONTEND="noninteractive"
  apt-get update;
  apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    less \
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
    unzip \
    vim-tiny \
    xz-utils \
    zip \
    zlib1g-dev
EOF

COPY --link --from=test /usr/local /usr/local
COPY --link --from=test /home /home
STOPSIGNAL SIGQUIT
EXPOSE 9000
ENTRYPOINT ["docker-php-entrypoint"]
WORKDIR /app
CMD ["php-fpm"]


FROM debian:trixie-slim AS test-all
SHELL ["/bin/bash", "-c"]
ENV PHPIZE_DEPS="autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c"
ENV PHP_INI_DIR="/usr/local/etc/php"
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ENV PHP_LDFLAGS="-Wl,-O1 -pie"
ENV GPG_KEYS="AFD8691FDAEDF03BDF6E460563F15A9B715376CA 9D7F99A0CB8F05C8A6958D6256A97AF7600A39A6 0616E93D95AF471243E26761770426E17EBBB3DD"
ENV PHP_VERSION="8.4.15"
ENV PHP_URL="https://www.php.net/distributions/php-8.4.15.tar.xz"
ENV PHP_ASC_URL="https://www.php.net/distributions/php-8.4.15.tar.xz.asc"
ENV PHP_SHA256="a060684f614b8344f9b34c334b6ba8db1177555997edb5b1aceab0a4b807da7e"
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

RUN <<-EOF
  set -eux;
  echo -e "Package: php*\nPin: release *\nPin-Priority: -1" > /etc/apt/preferences.d/no-debian-php
  mkdir -p "$PHP_INI_DIR/conf.d";
  mkdir -p /app;
  chown www-data:www-data /app;
  chmod 1777 /app
EOF

COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
COPY --link --from=composer/composer /tmp/* /home/dev/.composer/
COPY --link --from=php-base-image /usr/local/bin/docker-* /usr/local/bin/

RUN --mount=type=bind,from=libsodium,source=/usr/src/libsodium,target=/usr/src/libsodium \
    --mount=type=cache,target=/root/.pie \
    --mount=type=cache,target=/root/.composer \
    --mount=type=bind,from=ghcr.io/php/pie:bin,source=/pie,target=/usr/bin/pie \
    --mount=type=cache,target=/var/lib/apt,sharing=locked <<-EOF
  set -eux;
  export DEBIAN_FRONTEND="noninteractive"
  apt-get update;
  apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    ca-certificates \
    curl \
    git \
    gnupg \
    jq \
    less \
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
    unzip \
    vim-tiny \
    xz-utils \
    zip \
    zlib1g-dev
  mkdir -p /usr/src;
  cd /usr/src;
  curl -fsSL -o php.tar.xz "$PHP_URL";

  if [ -n "$PHP_SHA256" ]; then \
    echo "$PHP_SHA256 *php.tar.xz" | sha256sum -c -; \
  fi;

  curl -fsSL -o php.tar.xz.asc "$PHP_ASC_URL";
  export GNUPGHOME="$(mktemp -d)";
  for key in $GPG_KEYS; do \
      gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
  done;

  gpg --batch --verify php.tar.xz.asc php.tar.xz;
  gpgconf --kill all;
  rm -rf "$GNUPGHOME";

  make -C /usr/src/libsodium -j $(nproc) install

  export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" PHP_BUILD_PROVIDER='https://github.com/docker-library/php' PHP_UNAME='Linux - Docker' ;
  docker-php-source extract;
  cd /usr/src/php;

  gnuArch="$(dpkg-architecture --query DEB_BUILD_GNU_TYPE)";
  debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)";

  if [ ! -d /usr/include/curl ]; then \
    ln -sT "/usr/include/$debMultiarch/curl" /usr/local/include/curl; \
  fi;

    ./configure \
      --build="$gnuArch" \
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
      --with-libdir="lib/$debMultiarch" \
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

    make -j "$(nproc)";
    find -type f -name '*.a' -delete;
    make -j "$(nproc)" install;
    find /usr/local -type f -perm '/0111' -exec sh -euxc ' strip --strip-all "$@" || : ' -- '{}' + ;
    make clean;
    cp -v php.ini-* "$PHP_INI_DIR/";
    cd /;
    docker-php-source delete;
    find /usr/local -type f -executable -exec ldd '{}' '; ' | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' | sort -u | xargs -rt dpkg-query --search | awk 'sub(":$", "", $1) { print $1 }' | sort -u | xargs -r apt-mark manual ;
    php --version

    docker-php-ext-enable opcache
    # Note: Must target the "latest" branch until a new release is made (see https://github.com/php/pie/issues/445)
    # For Xdebug, install but do not enable it, allowing same base image for production and development
    pie install -j$(nproc) php-amqp/php-amqp:dev-latest;
    pie install -j$(nproc) igbinary/igbinary:^3.2.17@beta
    pie install -j$(nproc) phpredis/phpredis
    pie install -j$(nproc) xdebug/xdebug --skip-enable-extension;

    apt-get remove -y $PHPIZE_DEPS;
    apt-get autoremove -y
    apt-get clean
EOF

ENTRYPOINT ["docker-php-entrypoint"]

WORKDIR /app

RUN <<-EOF
  set -eux;
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

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

STOPSIGNAL SIGQUIT

EXPOSE 9000

CMD ["php-fpm"]
