# Kos Rental

## How to run locally

1. Make sure that `mysql` already installed on you machine. Or if you have any remote mysql server, it can be used to
2. Make sure that `php` already installed on your machine.
3. Make sure that `composer` already installed on your machine. Otherwise, you can follow the instalation instruction in following url https://getcomposer.org/doc/00-intro.md
4. Create new database on your mysql server with name `kos_rental`
5. Rename file `.env.example` to `.env` and then adjust the value of `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` with you database credential
6. Open terminal / CMD and go to the root directory of this project
7. Type `php artisan serve`. if it is success, you can start test the API based on the documentation.

## API documentation

You can refer to following link for detail API spec:
https://documenter.getpostman.com/view/362285/UUy37kpw


