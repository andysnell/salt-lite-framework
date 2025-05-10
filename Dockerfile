# syntax=docker/dockerfile:1
FROM php:8.4-fpm AS base
ENV COMPOSER_HOME "/app/build/composer"
ENV PATH "/app/bin:/app/vendor/bin:/app/build/composer/bin:$PATH"
ENV PHP_PEAR_PHP_BIN="php -d error_reporting=E_ALL&~E_DEPRECATED"
ENV SALT_BUILD_STAGE "development"
ENV XDEBUG_MODE "off"
WORKDIR /

# Create a non-root user to run the application
RUN groupadd --gid 1000 dev && useradd --uid 1000 --gid 1000 --groups www-data --shell /bin/bash dev

# Update the package list and install the latest version of the packages
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get update && apt-get dist-upgrade --yes

# Install system dependencies
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get install --yes --quiet --no-install-recommends \
    curl \
    jq \
    less \
    unzip \
    vim-tiny \
    zip \
  && ln -s /usr/bin/vim.tiny /usr/bin/vim

# Install PHP Extensions
FROM base AS php-extensions
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get install --yes --quiet --no-install-recommends \
    libgmp-dev \
    libicu-dev \
    libzip-dev \
    librabbitmq-dev \
    zlib1g-dev
RUN --mount=type=tmpfs,target=/tmp/pear <<-EOF
  set -eux
  docker-php-ext-install -j$(nproc) bcmath exif gmp intl opcache pcntl pdo_mysql zip
  MAKEFLAGS="-j$(nproc)" pecl install amqp igbinary redis timezonedb
  docker-php-ext-enable amqp igbinary redis timezonedb
  cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
EOF

# The Sodium extension originally compiled with PHP is based on an older version
# of the libsodium library provided by Debian. Since it was compiled as a shared
# extension, we can compile the latest stable version of libsodium from source and
# rebuild the extension.
FROM base AS libsodium
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get install --yes --quiet --no-install-recommends \
    autoconf  \
    automake \
    build-essential \
    git \
    libtool \
    tcc
RUN git clone --branch stable --depth 1 --no-tags  https://github.com/jedisct1/libsodium /usr/src/libsodium
WORKDIR /usr/src/libsodium
RUN <<-EOF
  ./configure
  make -j$(nproc) && make -j$(nproc) check
  make -j$(nproc) install
  docker-php-ext-install -j$(nproc) sodium
EOF

FROM base AS development-php
ARG GIT_COMMIT="undefined"
ENV GIT_COMMIT=${GIT_COMMIT}
ENV SALT_BUILD_STAGE="development"
WORKDIR /app
COPY --link --from=php-extensions /usr/lib/x86_64-linux-gnu /usr/lib/x86_64-linux-gnu/
# Header files from zlib are needed for the xdebug extension
COPY --link --from=php-extensions /usr/include/ /usr/include/
COPY --link --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --link --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --link --from=php-extensions /usr/local/etc/php/php.ini /usr/local/etc/php/php.ini
COPY --link --from=composer/composer:latest-bin /composer /usr/local/bin/composer
COPY --link --from=libsodium /usr/local/lib/ /usr/local/lib/
COPY --link php-development.ini /usr/local/etc/php/conf.d/settings.ini
RUN --mount=type=tmpfs,target=/tmp/pear <<-EOF
  set -eux
  MAKEFLAGS="-j$(nproc)" pecl install xdebug
  docker-php-ext-enable xdebug
EOF
USER dev

FROM base AS production-php
ARG GIT_COMMIT="undefined"
ENV GIT_COMMIT=${GIT_COMMIT}
ENV SALT_BUILD_STAGE="production"
ENV COMPOSER_ROOT_VERSION=1.0.0
WORKDIR /app
COPY --link --from=php-extensions /usr/lib/x86_64-linux-gnu/ /usr/lib/x86_64-linux-gnu/
COPY --link --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --link --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --link --from=php-extensions /usr/local/etc/php/php.ini /usr/local/etc/php/php.ini
COPY --link --from=composer/composer:latest-bin /composer /usr/local/bin/composer
COPY --link --from=libsodium /usr/local/lib/ /usr/local/lib/
COPY --link php-production.ini /usr/local/etc/php/conf.d/settings.ini
COPY --link --chown=1000:1000 ./bin /app/bin
COPY --link --chown=1000:1000 ./config /app/config
COPY --link --chown=1000:1000 ./database /app/database
COPY --link --chown=1000:1000 ./public /app/public
COPY --link --chown=1000:1000 ./resources /app/resources
COPY --link --chown=1000:1000 ./src /app/src
COPY --link --chown=1000:1000 ./storage /app/storage
COPY --link --chown=1000:1000 ./composer.json ./composer.lock /app/
COPY --link --chown=1000:1000 --from=production-redocly /spec/openapi.yaml /app/resources/views/openapi.yaml
COPY --link --chown=1000:1000 --from=production-redocly /spec/openapi.html /app/resources/views/openapi.html
RUN <<-EOF
    set -eux;
    mkdir -p /app/build/composer;
    chown -R dev:dev /app
    find /app/storage -type d -exec chmod 0777 {} \;
    find /app/storage -type f -exec chmod 0666 {} \;
EOF
USER dev
RUN --mount=type=cache,mode=0777,uid=1000,gid=1000,target=/app/build/composer/cache \
    --mount=type=secret,id=GITHUB_TOKEN,uid=1000,gid=1000,required=true <<-EOF
    set -eux
    composer config --global github-oauth.github.com $(cat /run/secrets/GITHUB_TOKEN)
    composer install --classmap-authoritative --no-dev
    SALT_APP_KEY=AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= salt orm:generate-proxies
    SALT_APP_KEY=AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= salt routing:cache
    rm -f /app/storage/bootstrap/config.cache.php
    # Remove the auth.json file to avoid baking the github key into the build
    rm -f /app/build/composer/auth.json
EOF

FROM caddy:latest AS development-web
COPY --link caddy/ /etc/caddy/
RUN caddy fmt --overwrite /etc/caddy/Caddyfile

FROM development-web AS production-web
ARG GIT_COMMIT="undefined"
ENV GIT_COMMIT=${GIT_COMMIT}
COPY --link ./public /app/public
