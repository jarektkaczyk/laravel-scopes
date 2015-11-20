# Sofa/Laravel-Scopes

Handy scopes for Laravel's Query Builder.


## Installation

Package requires **PHP 5.5.9+** and works with **Laravel 5.1.20+** (or standalone **illuminate/database 5.1.20**.

1. Require the package in your `composer.json`:
    ```
        "require": {
            ...
            "sofa/laravel-scopes": "~1.0",
        },
    ```

2. Call `(new Sofa\LaravelScopes\PeriodsAdd)->apply()` when you bootstrap your app. In Laravel simply add `ServiceProvider` to your `config/app.php` file:
    ```php
        'providers' => [
            // ...
            Sofa\LaravelScopes\ServiceProvider::class,
            // ...
        ],
    ```

## Usage example

### `Periods`
provides extensions of the Query Builder for easy fetching records in given range, relative to NOW. Methods can be used on any eloquent model as well:

```php
// Given it's September 11th, 2015

// Query\Builder: count users created in August
DB::table('users')->lastMonth()->count();

// Eloquent\Buidler: get users created on September 10th
User::yesterday()->get();

// count users who logged-in in 2014 & 2015
User::periods('year', 1, 'last_login', true)->count();

// count users created in 2014 & 2015
User::periods('year', -1, null, true)->count();
// or
User::periods('year', -1, true)->count();

// Get subscriptions expiring in October
User::where(...)->nextMonth()->get();

// Get subscriptions expired in past 7 days
User::has(...)->periods('day', -7)->get();

// Get subscriptions expiring in next 30 days
User::periods('day', 30)->get();
```


## Roadmap

 - [x] Periods
 - [ ] Searchable
