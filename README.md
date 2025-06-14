#Setup API

Make env, fill with your local database info

```terminal
touch .env
```
Run composer install

```terminal
composer i
```

Migrate

```terminal
php artisan migrate
```

Seeding
```terminal
php artisan db:seed
```

Link the storage

```terminal
php artisan storage:link
```

Run the API

```terminal
php artisan serve
```

