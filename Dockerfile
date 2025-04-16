# syntax=docker/dockerfile:1
FROM php:8.4-fpm as development
ENV PATH "/app/bin:/app/vendor/bin:/app/build/composer/bin:$PATH"
ENV COMPOSER_HOME "/app/build/composer"
ENV SALT_BUILD_STAGE "development"
ENV PHP_PEAR_PHP_BIN="php -d error_reporting=E_ALL&~E_DEPRECATED"
ENV XDEBUG_MODE "off"
WORKDIR /

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
    pkg-config \
    unzip \
    vim-tiny \
    zip \
    zlib1g-dev
  apt-get clean
  ln -s /usr/bin/vim.tiny /usr/bin/vim
EOF

# The Sodium extension originally compiled with PHP is based on an older version
# of the libsodium library provided by Debian. Since it was compiled as a shared
# extension, we can compile the latest stable version of libsodium from source and
# rebuild the extension.
RUN <<-EOF
  set -eux
  MAKEFLAGS="-j$(nproc --ignore=2)"
  git clone --branch stable --depth 1 --no-tags  https://github.com/jedisct1/libsodium /usr/src/libsodium
  cd /usr/src/libsodium
  ./configure
  make && make check
  make install
  rm -rf /usr/src/libsodium
EOF

# Install PHP Extensions
RUN <<-EOF
  set -eux
  MAKEFLAGS="-j$(nproc --ignore=2)"
  docker-php-ext-install -j$(nproc --ignore=2) bcmath exif gmp intl opcache pcntl pdo_mysql sodium zip
  pecl install amqp igbinary redis timezonedb xdebug
  docker-php-ext-enable amqp igbinary redis timezonedb xdebug
  find "$(php-config --extension-dir)" -name '*.so' -type f -exec strip --strip-all {} \;
  rm -rf /tmp/pear
  cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
EOF

COPY --link settings.ini /usr/local/etc/php/conf.d/settings.ini

COPY --link --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /app
USER dev
