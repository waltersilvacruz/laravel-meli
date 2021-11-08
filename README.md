# MercadoLibre API Integration for Laravel

Integrate your Laravel Application with MercadoLibre's API.

Based on [vcoud/mercadolibre](https://github.com/vcoud/mercadolibre) (thank you, man!).

PHP client wrapper for [MercadoLibre API](https://developers.mercadolibre.com/).

This package was designed to work with [Laravel](https://www.laravel.com), so it was written with Laravel in mind.

## Installation

1. Install with composer:

```bash
$ composer require waltersilvacruz/laravel-meli
```

2. Run the migration command to create the `meli_app_tokens` table:

```bash
$ php artisan migrate --package=waltersilvacruz/laravel-meli
```

The package uses the [auto registration feature](https://laravel.com/docs/packages#package-discovery) of Laravel.

## Configuration

**IMPORTANT**: Before you start, check the following:

- You **must** have a MercadoLibre account
- You **must** create an APP into ML's [DevSite](https://developers.mercadolibre.com/devcenter/)
- The APP **must** have the `offline_access` scope enabled (otherwise the _refresh token_ will not be generated)
- You **must** have both APP Code and Client Secret in hands to setup into your Laravel application.

1. Add the appropriate values to your ```.env```

     ```bash
     MELI_APP_ID=<YOUR-ML-APP-ID>
     MELI_CLIENT_SECRET=<YOUR-ML-CLIENT-SECRET>
     MELI_REDIRECT_ROUTE=<ROUTE-NAME>     # The user will be redirected to this route after connect/disconnect
     ```
     example:
   
      ```bash
     MELI_APP_ID=4181152627684157
     MELI_CLIENT_SECRET=kbJ1YVaWpqmFYhj1PsnKSOcVKEvWxp1a
     MELI_REDIRECT_ROUTE=home
     ```

2. Publish configs & views _[optional]_

   #### Config file
   A configuration file named ```meli.php``` can be published to ```config/``` by running the following command:

    ```bash
    php artisan vendor:publish --tag=meli-config
    ```

   #### Publishing Views
   View files can also be published by using:

    ```bash
    php artisan vendor:publish --tag=meli-views
    ```

   The blade's templates will be published into `resources/views/vendor/meli` folder, so you can customize the templates to apply your application's look and feel.


3. Clear your route and config caches:
   ``` 
   php artisan config:clear && php artisan route:clear
   ```
## Usage

### First, connect your Laravel Application into MercadoLibre via Oauth

You can connect to ML opening this link on browser:
`<your-app-site>/meli/connect/<state>`. Here, the `<state>` parameter is mandatory!

This wrapper uses `<state>` as unique identifier for each authentication token, allowing your site to connect to multiples ML accounts.

Example:
```
https://my-awesome-site.com/meli/connect/store-demo-1
```

### Using methods to retrieve data from ML

Once connected, you can perform queries to ML's API using one of the following methods:
```php
MeliAppService::get(string $path, array $params = [], bool $assoc = false): stdClass
MeliAppService::post(string $path, array $body = []): stdClass
MeliAppService::put(string $path, array $body = [], array $params = []): stdClass
MeliAppService::delete(string $path, array $params = []): stdClass
MeliAppService::options(string $path, array $params = []): stdClass
```

Sample code:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller
use WebDEV\Meli\Services\MeliApiService;

class HomeController extends Controller
{
    public function index() {
        // it's important to inform the same state used on connection link 
        $service = new MeliApiService('store-demo-1');
        $data = $service->get('/users/me');
        dd($data);            
    }
}
```

Every call returns an `stdClass` object:
``` 
$data->httpCode; // the http code returned by MercadoLibre's API. If !== 200, there is something wrong!
$data->response; // the complete response returned 
```
Please refer to ML API docs to learn about all available endpoints and its response data.

## Multiples Connections

Yes, we can! It is possible to connect to multiples ML accounts at same time.

Let's imagine the following scenario:
- Your app has multiple users.
- Each user can connect to their ML account.
- Each user must access only his own ML data. 

Make sure each user will have a *UNIQUE* state identification.

```
# User A connection link:
https://my-awesome-site.com/meli/connect/1281300c-a19c-42e7-9de8-6d0badccc9ad
                                         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

# User B connection link:
https://my-awesome-site.com/meli/connect/115e1b1c-fa1b-4b6c-9781-aa2f4e5f2373
                                         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

# User C connection link:
https://my-awesome-site.com/meli/connect/f237ff76-963b-4dbc-b1d3-978d1534bf63
                                         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
```

Each user will be prompted to log in via ML OAuth, then they must allow your application to connect into their accounts. The auth and refresh tokens for each state will be stored into `meli_app_tokens` table. The `state` column will be the key to fetch the correct token when instantiate the service.

```php
$service = new MeliApiService('1281300c-a19c-42e7-9de8-6d0badccc9ad');
// or
$service = new MeliApiService('115e1b1c-fa1b-4b6c-9781-aa2f4e5f2373');
// or
$service = new MeliApiService('f237ff76-963b-4dbc-b1d3-978d1534bf63');

// then you can perform queries
$order = $service->get('/orders/768570754');
dd($order->response);
```

## Command line utility

This package comes with a utility command to help you create test users on MercadoLibre.

```bash
php artian meli:create-test-user [state] [site]
```

You can use one of the following options for the [site] parameter:

```
MLA = Argentina
MLB = Brazil
MCO = Colombia
MCR = Costa Rica
MEC = Ecuador
MLC = Chile
MLM = Mexico
MLU = Uruguay
MLV = Venezuela
MPA = Panama
MPE = Peru
MPT = Portugal
MRD = Dominican Republic
```

Example:

```bash
php artisan meli:create-test-user "1281300c-a19c-42e7-9de8-6d0badccc9ad" MLB

// Sample output:
Creating new test user...
DONE! New user created successfully:

ID: 1014941445
Nickname: TESTYTEZPVLJ
Password: qatest2911
Email: test_user_60793708@testuser.com
```

## Automatic token refreshing

The auth token last for 6 hours after its creation. After this, the auth token expires and a new one will be automatically generated using the *refresh token* before sending you query.

The _auth token_ refreshing is automatic and no user action is required.

In other hand, the *refresh token* will expire after 6 months and the user MUST log in again to grab a fresh new one. 

## Documentation & Important notes

##### The URIs are relative to https://api.mercadolibre.com
##### Don’t forget to check out the [MercadoLibre developer site](https://developers.mercadolibre.com/)
##### Official Laravel [documentation](https://developers.mercadolibre.com/)
