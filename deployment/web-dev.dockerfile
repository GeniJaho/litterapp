FROM php:8.1.10-fpm

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
    vim

# Clear cache
RUN apt autoremove && apt-get clean && rm -rf /var/lib/apt/lists/*

# Newer Node Version
RUN apt-get update && apt-get install -y ca-certificates curl gnupg
RUN mkdir -p /etc/apt/keyrings && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
ENV NODE_MAJOR=20
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

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

RUN composer update
RUN composer install --optimize-autoloader --no-dev
RUN php artisan config:clear && php artisan cache:clear && php artisan config:cache && php artisan key:generate

RUN npm install && npm run build

#EXPOSE 9000
#RUN php artisan migrate
#php artisan migrate:fresh --seed
CMD php artisan serve --host=0.0.0.0 --port=9000
