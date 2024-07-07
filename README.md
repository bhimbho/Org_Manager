# Steps to setup
- Clone the repository
- Run `composer install`
- To generate client Id and secret key run `php artisan passport:client`
- duplicate .env.example and name it .env
- replace values for `PASSPORT_PERSONAL_ACCESS_CLIENT_ID` and `PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET` with the generated IDs
- run `php artisan passport:keys` to generate private and public keys
- setup postgres DB and replace DB credentials in .env

# Setup testing
- Create a db `testing` in postgres
- Ensure correct credentials in .env.testing
- Generate keys `php artisan passport:keys`. Note: ignore this step if you've done it above
- ensure .env.testing doesn't have `PASSPORT_PERSONAL_ACCESS_CLIENT_ID` and `PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET`
- run `php artisan test`



