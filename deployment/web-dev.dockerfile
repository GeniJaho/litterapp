#FROM php:8.2.13-fpm
FROM php:8.2.27-fpm-alpine

WORKDIR /app

# Arguments defined in docker-compose.yml
ARG user=laravel
ARG uid=1000

# Install system dependencies
#RUN set -x \
RUN apk update && apk upgrade
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    #redis-tools \
    #default-mysql-client \
    libpng-dev \
    #libonig-dev \
    libxml2-dev \
    libzip-dev \
    libwebp-dev \
    libjpeg-dev \
    #libjpeg62-turbo-dev \
    #libfreetype6-dev \
    libxpm-dev

# Clear cache
#RUN apt autoremove && apt-get clean && rm -rf /var/lib/apt/lists/*

# Newer Node Version
#RUN apk update && apk add ca-certificates curl gnupg
#RUN mkdir -p /etc/apk/keys && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | apk add --no-cache --virtual apk-keyring && apk add --keyring /etc/apk/keys/nodesource.gpg http://deb.nodesource.com/node
#ENV NODE_MAJOR=20
#RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apk update && apk add nodejs 
#=20.*-r0 

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
		zip \
        exif 

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

RUN composer install --optimize-autoloader
RUN php artisan config:clear && php artisan cache:clear && php artisan storage:link

RUN npm install && npm run build

# Copy the entry script
COPY ./deployment/web-dev.entrypoint.sh /usr/local/bin/entrypoint.sh
# Give the script execute permissions
RUN chmod +x /usr/local/bin/entrypoint.sh
# Use the entry script as the default command
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
