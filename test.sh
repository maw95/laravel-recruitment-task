#! /bin/bash

docker compose exec app bash -c "vendor/bin/pint --test && vendor/bin/phpstan analyse src tests && php artisan test"
