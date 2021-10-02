# Kos Rental

## Feature

- [x] Register as owner / regular user / premium user. Regular user will be given 20 credit, premium user will be given 40 credit after register. Owner will have no credit. 
- [x] Allow owner to add, update, and delete kost 
- [x] Allow owner to see his kost list 
- [x] Allow user to search kost that have been added by owner. Search kost by several criteria: name, location, price. And also can be sorted by: price
- [x] Allow user to see kost detail 
- [x] Allow user to ask about room availability. Ask about room availability will reduce user credit by 5 point 
- [x] Auto recharge for user credit. It's implemented by scheduled command

## Feature Boundary

1. 1 account can have 2 type of roles (owner & user). If account registered with roles owner & user, then the account will have initial credit
2. Owner can add more than 1 kost but only 1 kost can be added for 1 request.

## How to run locally

1. Make sure that `mysql` already installed on you machine. Or if you have any remote mysql server, it can be used to
2. Make sure that `php` already installed on your machine. Minimum PHP version is 7.3.x
3. Make sure that `composer` already installed on your machine. Otherwise, you can follow the instalation instruction in following url https://getcomposer.org/doc/00-intro.md
4. Create new database on your mysql server with name `kos_rental`
5. Rename file `.env.example` to `.env` and then adjust the value of `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` with you database credential
6. Open terminal / CMD and go to the root directory of this project
7. Type `composer install` to install all dependencies.
8. Run the DB migration & DB seeder by type `php artisan migrate:fresh --seed`
9. Type `php artisan serve`. if it is success, you can start test the API based on the documentation.

## How to run auto topup

This project also implement scheduled task for recharge point on the first day of every month at 01:00 named topup:credit.

To run manually the scheduling:
1. Check all task with command `php artisan list`
2. If task `topup:credit` is exist, we can run the task by type `php artisan topup:credit`

or you can use Crontab:
1. Open crontab file `crontab -e`
2. Edit crontab file and add `0 0 1 * * cd /your-project-path && php artisan topup:credit >> /dev/null 2>&1`

## API documentation

You can refer to following link for detail API spec:
https://documenter.getpostman.com/view/362285/UUy37kpw


