#!/bin/bashs

php artisan optimize:clear &

php artisan serve --host=127.0.0.1 --port=8002 &

npm run dev
