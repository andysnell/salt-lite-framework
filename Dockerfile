# syntax=docker/dockerfile:1
FROM php:8.4-fpm AS base
ARG USER_UID=1000
ARG USER_GID=1000
WORKDIR /
SHELL ["/bin/bash", "-c"]
ENV PATH="/app/bin:/app/vendor/bin:/app/build/composer/bin:$PATH"
ENV XDEBUG_MODE="off"

# Create a non-root user to run the application
RUN groupadd --gid $USER_GID dev  \
    && useradd --uid $USER_UID --gid $USER_GID --groups www-data --create-home --shell /bin/bash dev

    # Update the package list and install the latest version of the packages
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get update && apt-get dist-upgrade --yes

# Install system dependencies
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get install --yes --quiet --no-install-recommends \
    curl \
    git \
    jq \
    less \
    unzip \
    vim-tiny \
    zip \
  && ln -s /usr/bin/vim.tiny /usr/bin/vim

# Install PHP Extensions
FROM base AS php-extensions
ENV PHP_PEAR_PHP_BIN="php -d error_reporting=E_ALL&~E_DEPRECATED"
RUN --mount=type=cache,target=/var/lib/apt,sharing=locked apt-get install --yes --quiet --no-install-recommends \
    libgmp-dev \
    libicu-dev \
    libzip-dev \
    librabbitmq-dev \
    zlib1g-dev
RUN --mount=type=tmpfs,target=/tmp/pear <<-EOF
  set -eux
  docker-php-ext-install -j$(nproc) bcmath exif gmp intl opcache pcntl pdo_mysql zip
  MAKEFLAGS="-j$(nproc)" pecl install amqp igbinary redis
  docker-php-ext-enable amqp igbinary redis
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
ENV COMPOSER_HOME="/home/dev/.composer"
ENV COMPOSER_CACHE_DIR="/app/build/composer/cache"
WORKDIR /app
# Header files from zlib are needed for the xdebug extension
COPY --link --from=php-extensions /usr/include/ /usr/include/
COPY --link --from=php-extensions /usr/lib/x86_64-linux-gnu /usr/lib/x86_64-linux-gnu/
COPY --link --from=php-extensions /usr/lib/x86_64-linux-gnu /usr/lib/x86_64-linux-gnu/
COPY --link --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --link --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --link php-development.ini /usr/local/etc/php/conf.d/settings.ini
COPY --link --from=libsodium /usr/local/lib/ /usr/local/lib/
COPY --link --from=composer/composer /usr/bin/composer /usr/local/bin/composer
RUN --mount=type=tmpfs,target=/tmp/pear <<-EOF
  set -eux
  MAKEFLAGS="-j$(nproc)" pecl install xdebug
  docker-php-ext-enable xdebug
  mkdir -p /home/dev/.composer
  composer self-update --clean-backups
  chown -R dev:dev /app /home/dev
EOF
USER dev

FROM base AS production-php
ARG GIT_COMMIT="undefined"
ENV GIT_COMMIT=${GIT_COMMIT}
ENV SALT_BUILD_STAGE="production"
ENV COMPOSER_ROOT_VERSION=1.0.0
ENV COMPOSER_HOME="/app/build/composer"
ENV COMPOSER_CACHE_DIR="/app/build/composer/cache"
WORKDIR /app
COPY --link --from=php-extensions /usr/lib/x86_64-linux-gnu/ /usr/lib/x86_64-linux-gnu/
COPY --link --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --link --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --link --from=php-extensions /usr/local/etc/php/php.ini /usr/local/etc/php/php.ini
COPY --link --from=libsodium /usr/local/lib/ /usr/local/lib/
COPY --link php-production.ini /usr/local/etc/php/conf.d/settings.ini
COPY --link --chown=$USER_UID:$USER_GID ./bin /app/bin
COPY --link --chown=$USER_UID:$USER_GID ./config /app/config
COPY --link --chown=$USER_UID:$USER_GID ./database /app/database
COPY --link --chown=$USER_UID:$USER_GID ./public /app/public
COPY --link --chown=$USER_UID:$USER_GID ./resources /app/resources
COPY --link --chown=$USER_UID:$USER_GID ./src /app/src
COPY --link --chown=$USER_UID:$USER_GID ./composer.json ./composer.lock /app/
RUN <<-EOF
    set -eux;
    mkdir -p /app/build/composer;
    mkdir -p /app/storage
    chown -R dev:dev /app
    find /app/storage -type d -exec chmod 0777 {} \;
    find /app/storage -type f -exec chmod 0666 {} \;
EOF
USER dev
RUN --mount=type=bind,from=composer/composer,source=/usr/bin/composer,target=/usr/local/bin/composer \
    --mount=type=cache,mode=0777,uid=$USER_UID,gid=$USER_GID,target=/app/build/composer \
    --mount=type=secret,id=GITHUB_TOKEN,env=GITHUB_TOKEN,required=false <<-EOF
    set -eux
    [ -n "${GITHUB_TOKEN}" ] && composer config --global github-oauth.github.com ${GITHUB_TOKEN}
    export SALT_APP_KEY=$(head -c 32 /dev/urandom | base64) # temporary key for build
    composer install --classmap-authoritative --no-dev
    salt orm:generate-proxies
    salt routing:cache

    # Remove the storage cache and auth.json file to avoid baking the them into the build
    rm -f /app/storage/bootstrap/config.cache.php
    rm -f /app/build/composer/auth.json
EOF

FROM caddy:latest AS development-web
COPY --link caddy/ /etc/caddy/
RUN caddy fmt --overwrite /etc/caddy/Caddyfile

FROM development-web AS production-web
ARG GIT_COMMIT="undefined"
ENV GIT_COMMIT=${GIT_COMMIT}
COPY --link ./public /app/public

FROM node:alpine AS prettier
ENV NPM_CONFIG_PREFIX=/home/node/.npm-global
ENV PATH=$PATH:/home/node/.npm-global/bin
WORKDIR /app
RUN npm install --global --save-dev --save-exact npm@latest prettier
ENTRYPOINT ["prettier"]
