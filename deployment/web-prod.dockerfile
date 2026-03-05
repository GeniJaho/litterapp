FROM php:8.2.13-fpm

WORKDIR /app

# Arguments defined in docker-compose.yml
ARG user=laravel
ARG uid=1000

# Install system dependencies
RUN apt-get upgrade && apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    zip \
    unzip \
    redis-tools \
    default-mysql-client \
    supervisor \
    vim

# Clear cache
RUN apt autoremove && apt-get clean && rm -rf /var/lib/apt/lists/*

# Newer Node Version
RUN apt-get update && apt-get install -y ca-certificates curl gnupg
RUN mkdir -p /etc/apt/keyrings && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
ENV NODE_MAJOR=20
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install -y nodejs

# memcached
#RUN pecl install memcached && docker-php-ext-enable memcached
# mcrypt
#RUN pecl install mcrypt && docker-php-ext-enable mcrypt
# install xdebug
#RUN pecl install xdebug && docker-php-ext-enable xdebug
# install imagick
#RUN pecl install imagick && docker-php-ext-enable imagick

# Install PHP extensions
#RUN docker-php-ext-configure gd --with-jpeg --with-freetype
#RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# configure, install and enable all php packages
RUN set -eux; \
	docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-configure intl; \
	docker-php-ext-configure pcntl --enable-pcntl; \
	docker-php-ext-configure mysqli --with-mysqli=mysqlnd; \
	docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd; \
	docker-php-ext-configure zip; \
	docker-php-ext-install -j "$(nproc)" \
		gd \
		intl \
		pcntl \
		mysqli \
		opcache \
		pdo_mysql \
		zip


# Get latest Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user \
    && chown -R $user:$user /app

COPY --chown=$user . /app

#USER $user
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --optimize-autoloader --no-dev
RUN php artisan optimize && php artisan storage:link
RUN php artisan filament:optimize

RUN npm install && npm run build

# Copy supervisor configuration
COPY ./deployment/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the entry script
COPY ./deployment/web-prod.entrypoint.sh /usr/local/bin/entrypoint.sh
# Give the script execute permissions
RUN chmod +x /usr/local/bin/entrypoint.sh
# Use the entry script as the default command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
