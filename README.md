# Laravel 8 - Ecommerce application

## Screenshots

![preview img](/preview.png)

## Run Locally

Clone the project

```bash
  git clone https://github.com/paundrafebrian/ecommerce.git project-name
```

Go to the project directory

```bash
  cd project-name
```

-   Copy .env.example file to .env and edit database credentials there

```bash
    composer install
```

```bash
    cp .env.example .env
```

```bash
    php artisan key:generate
```

```bash
    php artisan artisan migrate:fresh --seed
```

```bash
    php artisan storage:link
```

#### Login

-   email = admin@example.com
-   password = 123
