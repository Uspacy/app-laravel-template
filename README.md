# Project Setup

This guide outlines the steps to set up the Laravel project. You can choose to run the project locally or using Docker
Compose.

## 1. Configure Environment Variables

Copy the example environment file and configure it with your database and other settings.

```bash
cp .env.example .env
```

### 1.1. Set Up Client ID and Secret ENVs

After registering your application in the system, you will receive the following credentials:

- **APP_CLIENT_ID**
- **APP_CLIENT_SECRET**

Once you have these, add them to your `.env` file:

```bash
APP_CLIENT_ID=your_client_id
APP_CLIENT_SECRET=your_client_secret
```

### 1.2. Configure API ENVs

The following environment variables are used to form the base path for your application's API endpoints. These values
are critical for building the correct URL structure in your API routes:

- **API_CODE**: This value identifies your application or API within the system. It is used as part of the base path for
  all API endpoints. For example, if you set `API_CODE=app_laravel_template`, the API URL will include this code in the
  path like so: `app_laravel_template/v1/{endpoint_path}`.
- **API_VERSION**: Defines the API version and determines which routes file is used. For example, with API_VERSION=v1,
  routes will be loaded from routes/v1.php.

Example in `.env`:

```env
API_CODE=your_application_code
API_VERSION=v1
```

### 1.3. Configure database ENVs

Set the following environment variables to configure your database connection:

**Note:** If you're using Docker Compose, set the `DB_HOST` value to `mysql` to match the service name in the
configuration.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_name
DB_USERNAME=db_username
DB_PASSWORD=db_password
```

## 2. Launching an application using a docker

We've already set up the docker compose, so you'll need to take the following steps to run the project:

### 2.1. Vendor directory (Composer)

Since we're using the Sail image for the Laravel container, which is located in the vendor folder, we need to install
the dependencies first. To do this, run the following command:

```bash
docker run --rm \                                                       ✔  5s  
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer istall --ignore-platform-reqs
```

### 2.2. Run application

After installing the dependencies, run the following command to launch the application:

```bash
docker compose up -d
```

### 2.3. Stop application

```bash
docker compose down
```

### 2.4. Executing laravel commands

There are two ways to run any laravel command:

#### 2.4.1. Sail

Since we're using the Laravel Sail image, you can simplify the command usage by creating an alias. This will allow you
to use sail instead of typing ./vendor/bin/sail each time. Run the following command to create the alias:

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

Example command to start migrations

```bash
sail artisan migrate
```

#### 2.4.1. Docker compose

You can also execute the command directly in the container by running the following command:

```bash
docker compose exec -it laravel.test php artisan route:list 
```