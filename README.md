1. Чтобы получить доступ, запустите приложение и перейдите по следующему маршруту:

http://localhost:86/login

2. Убедитесь, что у вас установлен Docker и Docker Compose.

3. Клонируйте репозиторий:
   git@github.com:merffff/test2.git
4. Настройте файл .env:
   
DB_CONNECTION=pgsql

DB_HOST=db

DB_PORT=5432

DB_DATABASE=laravel

DB_USERNAME=postgres

DB_PASSWORD=root

5. Запустите сервер:

docker-compose up --build

6. Перейти в контейнер php-fpm:

docker compose exec php-fpm bash

7. Из контейнера php-fpm установите зависимости:

composer install

8. Из контейнера php-fpm запустите миграции:

php artisan migrate


Автор: [Irina]
