# bayusyaits-lumen
Lumen 8.0
Create database db_halodoc, username root and password null
cp .env.example .env
php artisan migrate
composer dump-autoload
php artisan serve --port=8003

open POSTMAN
import postman/order-backend-lumen.postman_collection.json

Login:
if expired bearer token, user must login via web or postman (user/login, after that copy token and replace to header Authorization)

Signature:
Hit (user/add client) in postman to add user client, Replace signature code to header X-Signature

Product:
Add Product via postman make sure header X-Signature and Authorization is mandatory has been input.
