# syntax=docker/dockerfile:1
FROM php:8.4-fpm as development
ENV PATH "/app/bin:/app/vendor/bin:/home/dev/composer/bin:$PATH"
ENV COMPOSER_HOME "/home/dev/composer"
ENV SALT_BUILD_STAGE "development"
ENV PHP_PEAR_PHP_BIN="php -d error_reporting=E_ALL&~E_DEPRECATED"
ENV XDEBUG_MODE "off"

RUN <<-EOF
  set -eux
  groupadd --gid 1000 dev
  useradd --system --create-home --uid 1000 --gid 1000 --shell /bin/bash dev
  apt-get update
  apt-get upgrade -y
  apt-get install -y -q \
    apt-transport-https \
    autoconf  \
    build-essential \
    curl \
    git \
    jq \
    less \
    libgmp-dev \
    libicu-dev \
    libzip-dev \
    librabbitmq-dev \
    libsodium-dev \
    pkg-config \
    unzip \
    vim-tiny \
    zip \
    zlib1g-dev
  apt-get clean
  ln -s /usr/bin/vim.tiny /usr/bin/vim
EOF

# Install PHP Extensions
RUN <<-EOF
  set -eux
  docker-php-ext-install -j$(nproc) bcmath exif gmp intl opcache pcntl pdo_mysql zip
  MAKEFLAGS="-j $(nproc)" pecl install amqp igbinary redis timezonedb xdebug
  docker-php-ext-enable amqp igbinary redis timezonedb xdebug
  find "$(php-config --extension-dir)" -name '*.so' -type f -exec strip --strip-all {} \;
  rm -rf /tmp/pear
  cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
EOF

COPY --link settings.ini /usr/local/etc/php/conf.d/settings.ini

COPY --link --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /app
USER dev
