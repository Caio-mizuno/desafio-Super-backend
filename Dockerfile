# ---------------------------------------------- Build Time Arguments --------------------------------------------------
ARG PHP_VERSION="8.3.3"
ARG PHP_ALPINE_VERSION="3.19"
ARG NGINX_VERSION="1.21"
ARG COMPOSER_VERSION="2"
ARG XDEBUG_VERSION="3.1.5"
ARG COMPOSER_AUTH
ARG APP_BASE_DIR="."

# -------------------------------------------------- Composer Image ----------------------------------------------------

FROM composer:${COMPOSER_VERSION} as composer

# ======================================================================================================================
#                                                   --- Base ---
# ---------------  This stage install needed extenstions, plugins and add all needed configurations  -------------------
# ======================================================================================================================

FROM php:${PHP_VERSION}-fpm-alpine${PHP_ALPINE_VERSION} AS base

# Required Args ( inherited from start of file, or passed at build )
ARG XDEBUG_VERSION

# Maintainer label
LABEL maintainer="sherifabdlnaby@gmail.com"

# Set SHELL flags for RUN commands to allow -e and pipefail
# Rationale: https://github.com/hadolint/hadolint/wiki/DL4006
SHELL ["/bin/ash", "-eo", "pipefail", "-c"]

# ------------------------------------- Install Packages Needed Inside Base Image --------------------------------------

RUN RUNTIME_DEPS="tini fcgi"; \
  SECURITY_UPGRADES="curl"; \
  apk add --no-cache --upgrade ${RUNTIME_DEPS} ${SECURITY_UPGRADES}

# ---------------------------------------- Install / Enable PHP Extensions ---------------------------------------------

RUN apk add --no-cache --virtual .build-deps \
  $PHPIZE_DEPS  \
  libzip-dev    \
  icu-dev       \
  libxml2-dev   \
  tzdata   \
  # PHP Extensions --------------------------------- \
  && docker-php-ext-install -j$(nproc) \
  intl        \
  opcache     \
  pdo_mysql   \
  zip         \
  exif        \
  bcmath      \
  pcntl       \
  soap

RUN # Pecl Extensions -------------------------------- \
  pecl install apcu xdebug-${XDEBUG_VERSION} && docker-php-ext-enable apcu soap xdebug
# ---------------------------------------------------------------------
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# RUN # Install Xdebug at this step to make editing dev image cache-friendly, we delete xdebug from production image later \
# pecl install xdebug-${XDEBUG_VERSION} && docker-php-ext-enable xdebug

RUN # Cleanup ---------------------------------------- \
  rm -r /tmp/pear; \
  # - Detect Runtime Dependencies of the installed extensions. \
  # - src: https://github.com/docker-library/wordpress/blob/master/latest/php8.0/fpm-alpine/Dockerfile \
  out="$(php -r 'exit(0);')"; \
  [ -z "$out" ]; err="$(php -r 'exit(0);' 3>&1 1>&2 2>&3)"; \
  [ -z "$err" ]; extDir="$(php -r 'echo ini_get("extension_dir");')"; \
  [ -d "$extDir" ]; \
  runDeps="$( \
  scanelf --needed --nobanner --format '%n#p' --recursive "$extDir" \
  | tr ',' '\n' | sort -u | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
  )"; \
  # Save Runtime Deps in a virtual deps
  apk add --no-network --virtual .php-extensions-rundeps $runDeps; \
  # Uninstall Everything we Installed (minus the runtime Deps)
  apk del --no-network .build-deps; \
  # check for output like "PHP Warning:  PHP Startup: Unable to load dynamic library 'foo' (tried: ...)
  err="$(php --version 3>&1 1>&2 2>&3)"; 	[ -z "$err" ]
# -----------------------------------------------

# RUN apk --no-cache add gcc musl-dev curl libaio libnsl libc6-compat && \
#   cd /tmp && \
#   curl -o instantclient-basiclite.zip https://download.oracle.com/otn_software/linux/instantclient/instantclient-basiclite-linuxx64.zip -sl && \
#   curl -o instantclient-sdk.zip https://download.oracle.com/otn_software/linux/instantclient/instantclient-sdk-linuxx64.zip -sl && \
#   unzip instantclient-basiclite.zip && \
#   unzip instantclient-sdk.zip && \
#   mv instantclient*/ /usr/lib/instantclient && \
#   rm instantclient-basiclite.zip && \
#   ln -s /usr/lib/instantclient/libclntsh.so.19.1 /usr/lib/libclntsh.so && \
#   ln -s /usr/lib/instantclient/libocci.so.19.1 /usr/lib/libocci.so && \
#   ln -s /usr/lib/instantclient/libociicus.so /usr/lib/libociicus.so && \
#   ln -s /usr/lib/instantclient/libnnz19.so /usr/lib/libnnz19.so && \
#   ln -s /usr/lib/libnsl.so.2 /usr/lib/libnsl.so.1 && \
#   ln -s /lib/libc.so.6 /usr/lib/libresolv.so.2 && \
#   ln -s /lib64/ld-linux-x86-64.so.2 /usr/lib/ld-linux-x86-64.so.2

#Opção 2 para download do Oracle oci8 
RUN apk --no-cache add gcc musl-dev curl libaio libnsl libc6-compat && \
  cd /tmp && \
  wget https://download.oracle.com/otn_software/linux/instantclient/214000/instantclient-sdk-linux.x64-21.4.0.0.0dbru.zip && \
  wget https://download.oracle.com/otn_software/linux/instantclient/214000/instantclient-sqlplus-linux.x64-21.4.0.0.0dbru.zip && \
  wget https://download.oracle.com/otn_software/linux/instantclient/214000/instantclient-basic-linux.x64-21.4.0.0.0dbru.zip && \
  mkdir -p /opt/oracle && \
  cp instantclient-* /opt/oracle/ && \
  cd /opt/oracle/ && \
  unzip instantclient-basic-linux.x64-21.4.0.0.0dbru.zip && \
  unzip instantclient-sdk-linux.x64-21.4.0.0.0dbru.zip && \
  unzip instantclient-sqlplus-linux.x64-21.4.0.0.0dbru.zip && \
  mv instantclient*/ /usr/lib/instantclient && \
  ln -s /usr/lib/instantclient/libclntsh.so.19.1 /usr/lib/libclntsh.so && \
  ln -s /usr/lib/instantclient/libocci.so.19.1 /usr/lib/libocci.so && \
  ln -s /usr/lib/instantclient/libociicus.so /usr/lib/libociicus.so && \
  ln -s /usr/lib/instantclient/libnnz19.so /usr/lib/libnnz19.so && \
  ln -s /usr/lib/libnsl.so.2 /usr/lib/libnsl.so.1 && \
  ln -s /lib/libc.so.6 /usr/lib/libresolv.so.2 && \
  ln -s /lib64/ld-linux-x86-64.so.2 /usr/lib/ld-linux-x86-64.so.2

ENV oracle_base /usr/lib/instantclient
ENV ld_library_path /usr/lib/instantclient
ENV tns_admin /usr/lib/instantclient
ENV oracle_home /usr/lib/instantclient

RUN docker-php-ext-configure oci8 --with-oci8=instantclient,$oracle_home && docker-php-ext-install oci8
RUN docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,$oracle_home && docker-php-ext-install pdo_oci

RUN apk update && \
  apk upgrade && \
  apk add --no-cache \
  curl \
  libmemcached-dev \
  zlib-dev \
  postgresql-dev \
  libjpeg-turbo-dev \
  libpng-dev \
  oniguruma-dev \
  freetype-dev \
  openssl-dev \
  libmcrypt-dev \
  zip \
  unzip \
  build-base \
  mariadb-dev \
  libaio \
  libzip-dev \
  supervisor \
  dcron \
  nodejs \
  npm \
  && rm -rf /var/cache/apk/*

RUN apk add --no-cache \
  freetype \
  libjpeg-turbo \
  libpng \
  freetype-dev \
  libjpeg-turbo-dev \
  libpng-dev \
  openssl-dev \
  curl-dev \
  c-ares-dev \
  brotli-dev \
  postgresql-dev \
  && docker-php-ext-configure gd \
  --with-freetype=/usr/include/ \
  --with-jpeg=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-ext-enable gd \
  && apk del --no-cache \
  freetype-dev \
  libjpeg-turbo-dev \
  libpng-dev \
  && rm -rf /tmp/*
# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis
# Install swoole extension
RUN pecl install swoole && docker-php-ext-enable swoole
# ------------------------------------------------- Permissions --------------------------------------------------------

# - Clean bundled config/users & recreate them with UID 1011 for docker compatability in dev container.
# - Create composer directories (since we run as non-root later)
# - Add Default Config
RUN deluser --remove-home www-data && adduser -u 1010 -D www-data && rm -rf /var/www /usr/local/etc/php-fpm.d/* && \
  mkdir -p /var/www/.composer /app && chown -R www-data:www-data /app /var/www/.composer; \
  # ------------------------------------------------ PHP Configuration ---------------------------------------------------
  mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add in Base PHP Config
COPY docker/php/base-*   $PHP_INI_DIR/conf.d

# ---------------------------------------------- PHP FPM Configuration -------------------------------------------------

# PHP-FPM config
COPY docker/fpm/*.conf  /usr/local/etc/php-fpm.d/


# --------------------------------------------------- Scripts ----------------------------------------------------------

COPY docker/entrypoint/*-base docker/post-build/*-base docker/pre-run/*-base \
  docker/fpm/healthcheck-fpm		\
  docker/scripts/command-loop*	\
  # to
  /usr/local/bin/

RUN  chmod +x /usr/local/bin/*-base /usr/local/bin/healthcheck-fpm /usr/local/bin/command-loop*

# ---------------------------------------------------- Composer --------------------------------------------------------

COPY --from=composer /usr/bin/composer /usr/bin/composer

# ----------------------------------------------------- MISC -----------------------------------------------------------

WORKDIR /app
USER www-data

# Common PHP Frameworks Env Variables
ENV APP_ENV prod
ENV APP_DEBUG 0

# Validate FPM config (must use the non-root user)
RUN php-fpm -t

# ---------------------------------------------------- HEALTH ----------------------------------------------------------

HEALTHCHECK CMD ["healthcheck-fpm"]

# -------------------------------------------------- ENTRYPOINT --------------------------------------------------------

ENTRYPOINT ["entrypoint-base"]
# CMD ["php-fpm"]

# ======================================================================================================================
#                                                  --- Vendor ---
# ---------------  This stage will install composer runtime dependinces and install app dependinces.  ------------------
# ======================================================================================================================

FROM composer as vendor

ARG PHP_VERSION
ARG COMPOSER_AUTH
ARG APP_BASE_DIR

# A Json Object with remote repository token to clone private Repos with composer
# Reference: https://getcomposer.org/doc/03-cli.md#composer-auth
ENV COMPOSER_AUTH $COMPOSER_AUTH

WORKDIR /app

# Copy Dependencies files
COPY $APP_BASE_DIR/composer.json composer.json
COPY $APP_BASE_DIR/composer.lock composer.lock

# Set PHP Version of the Image
RUN composer config platform.php ${PHP_VERSION}; \
  # Install Dependencies
  composer install -n --no-progress --ignore-platform-reqs --no-dev --prefer-dist --no-scripts --no-autoloader

# ======================================================================================================================
# ==============================================  PRODUCTION IMAGE  ====================================================
#                                                   --- PROD ---
# ======================================================================================================================

FROM base AS app

ARG APP_BASE_DIR
USER root

# Copy PHP Production Configuration
COPY docker/php/prod-*   $PHP_INI_DIR/conf.d/
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy Prod Scripts && delete xdebug
COPY docker/entrypoint/*-prod docker/post-build/*-prod docker/pre-run/*-prod docker/supervisor/supervisord.conf \
  # to
  /usr/local/bin/

RUN  chmod +x /usr/local/bin/*-prod && pecl uninstall xdebug

# ----------------------------------------------- Production Config -----------------------------------------------------

# Copy Vendor
COPY --chown=www-data:www-data --from=vendor /app/vendor /app/vendor

# Copy App Code
COPY --chown=www-data:www-data $APP_BASE_DIR/ .

## Run Composer Install again
## ( this time to run post-install scripts, autoloader, and post-autoload scripts using one command )
RUN composer install --optimize-autoloader --apcu-autoloader --no-dev -n --no-progress && \
  composer check-platform-reqs && \
  post-build-base && post-build-prod

COPY --chown=www-data:www-data --from=vendor /Application/vendor /app/vendor

COPY ./docker/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

# Defina as permissões de usuario e grupo
RUN addgroup -g 1011 appuser && \
  adduser -u 1011 -G appuser -D appuser && \
  adduser www-data appuser

# Defina as permissões para o storage
COPY ./Application /app
RUN chown -R appuser. /app && \
  chmod -R 775 /app

# Permissoes específicas do projeto
RUN touch /app/storage/logs/laravel.log \
  chown  appuser. /app/storage/logs/laravel.log && \
  chmod -R 760 /app/storage/logs/laravel.log

RUN rm -f /app/storage/logs/swoole_http.log

RUN touch /app/storage/logs/swoole_http.log \
  chmod 777 /app/storage/logs/swoole_http.log  && \
  chown appuser. /app/storage/logs/swoole_http.log 

RUN touch /app/supervisord.log \
  chown  appuser. /app/supervisord.log && \
  chmod -R 760 /app/supervisord.log

RUN chown appuser. /app/bootstrap/cache/ && \
  chmod -R 760 /app/bootstrap/cache

COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /etc/supervisor \
  && chown -R appuser. /etc/supervisor

RUN chmod 644 /etc/supervisor/conf.d/supervisord.conf


# Entrypoints
ENTRYPOINT ["entrypoint-prod"]

CMD ["/usr/local/bin/run.sh"]
USER appuser
# ======================================================================================================================
# ==============================================  DEVELOPMENT IMAGE  ===================================================
#                                                   --- DEV ---
# ======================================================================================================================

FROM base as app-dev


ENV APP_ENV dev
ENV APP_DEBUG 1

# Switch root to install stuff
USER root

# For Composer Installs
RUN apk --no-cache add git openssh bash supervisor;
# Enable Xdebug
# docker-php-ext-enable xdebug

# For Xdebuger to work, it needs the docker host IP
# - in Mac AND Windows, `host.docker.internal` resolve to Docker host IP
# - in Linux, `172.17.0.1` is the host IP
# By default, `host.docker.internal` is set as extra host in docker-compose.yml, so it also works in Linux without any
# additional setting. This env is reserved for people who want to customize their own compose configuration.
ENV XDEBUG_CLIENT_HOST="host.docker.internal"

# ---------------------------------------------------- Scripts ---------------------------------------------------------

# Copy Dev Scripts

COPY docker/php/dev-*   $PHP_INI_DIR/conf.d/
COPY docker/entrypoint/*-dev  docker/post-build/*-dev docker/pre-run/*-dev  docker/supervisor/supervisord.conf \
  # to
  /usr/local/bin/

RUN chmod +x /usr/local/bin/*-dev; \
  mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# # ------------------------------------------------- Entry Point --------------------------------------------------------
USER root
COPY ./docker/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh
ENTRYPOINT ["entrypoint-dev"]

CMD ["/usr/local/bin/run.sh" ]

RUN addgroup -g 1011 appuser && \
  adduser -u 1011 -G appuser -D appuser && \
  adduser www-data appuser

# Defina as permissões para o storage
COPY ./Application /app
RUN chown -R appuser. /app && \
  chmod -R 775 /app

# Permissoes específicas do projeto
RUN touch /app/storage/logs/laravel.log \
  chown  appuser. /app/storage/logs/laravel.log && \
  chmod -R 760 /app/storage/logs/laravel.log

RUN rm -f /app/storage/logs/swoole_http.log

RUN touch /app/storage/logs/swoole_http.log \
  chmod 777 /app/storage/logs/swoole_http.log  && \
  chown appuser. /app/storage/logs/swoole_http.log 

RUN touch /app/supervisord.log \ 
  chown  appuser. /app/supervisord.log && \
  chmod -R 760 /app/supervisord.log

RUN chown appuser. /app/bootstrap/cache/ && \
  chmod -R 760 /app/bootstrap/cache

COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /etc/supervisor \
  && chown -R appuser. /etc/supervisor

RUN chmod 644 /etc/supervisor/conf.d/supervisord.conf

# Entrypoints
USER appuser



# ======================================================================================================================
# ======================================================================================================================
#                                                  --- NGINX ---
# ======================================================================================================================
# ======================================================================================================================
FROM nginx:${NGINX_VERSION}-alpine${NGINX_VERSION2} AS nginx
ARG APP_BASE_DIR

USER root

RUN rm -rf /var/www/* /etc/nginx/conf.d/* && adduser -u 1011 -D -S -G www-data www-data

COPY docker/nginx/nginx-*   /usr/local/bin/
COPY docker/nginx/nginx-entrypoint   /usr/local/bin/nginx-entrypoint
COPY docker/nginx/          /etc/nginx/

RUN chown -R www-data:www-data /etc/nginx/ && chmod +x /usr/local/bin/nginx-*

#ADD ${APP_BASE_DIR}/docker/logs/nginx /var/log/nginx

# The PHP-FPM Host
## Localhost is the sensible default assuming image run on a k8S Pod
RUN touch /var/log/nginx/access.log \
  chown  www-data:www-data /var/log/nginx/access.log && \
  chmod -R 775 /var/log/nginx/access.log

RUN touch /var/log/nginx/error.log \
  chown  www-data:www-data /var/log/nginx/error.log && \
  chmod -R 775 /var/log/nginx/error.log

###Ajustar aqui adicionado permissao do ususario www-data ao log
RUN chown -R www-data:www-data /var/log/nginx && chmod 775 /var/log/nginx

ENV PHP_FPM_HOST "localhost"
ENV PHP_FPM_PORT "9000"
ENV NGINX_LOG_FORMAT "json"

# For Documentation
EXPOSE 8080

# Add Healthcheck
HEALTHCHECK CMD ["docker/nginx/nginx-healthcheck"]

# Add Entrypoint
ENTRYPOINT ["nginx-entrypoint"]

# Switch User
USER www-data

# ======================================================================================================================
#                                                 --- NGINX PROD ---
# ======================================================================================================================

FROM nginx AS web

USER root

RUN SECURITY_UPGRADES="curl"; \
  apk add --no-cache --upgrade ${SECURITY_UPGRADES}


USER www-data

# Copy Public folder + Assets that's going to be served from Nginx
COPY --chown=www-data:www-data --from=app /app/public /app/public
COPY --chown=www-data:www-data docker/nginx/prod/*.conf   /etc/nginx/conf.d/


# ======================================================================================================================
#                                                 --- NGINX DEV ---
# ======================================================================================================================
FROM nginx AS web-dev

ENV NGINX_LOG_FORMAT "combined"

COPY --chown=www-data:www-data docker/nginx/dev/*.conf   /etc/nginx/conf.d/
COPY --chown=www-data:www-data docker/nginx/dev/certs/   /etc/nginx/certs/

