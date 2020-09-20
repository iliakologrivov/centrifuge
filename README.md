<h1 align="center">Centrifuge Broadcaster for Laravel 5.4+</h1>

## Introduction
Centrifuge broadcaster for laravel >= 5.4

## Requirements

- PHP 7.1.3+ or newer
- Laravel 5.4 or newer
- Centrifugo Server 2.0 or newer (see [here](https://github.com/centrifugal/centrifugo))

## Installation

Require this package with composer:

```bash
composer require iliakologrivov/centrifuge
./artisan vendor:publish --provider='IliaKologrivov\Centrifuge\CentrifugeServiceProvider'
```

Open your web routes and add:

```php
IliaKologrivov\Centrifuge\CentrifugeRouterFacade::subscribe();
IliaKologrivov\Centrifuge\CentrifugeRouterFacade::refresh();
```

Open your config/broadcasting.php and add the following to it:

```php
'connections' => [
    'centrifuge' => [
        'driver' => 'centrifuge',
    ],
    // ...
],
```

You can also add a configuration to your .env file:

```
CENTRIFUGE_SECRET=<very-long-secret-key>
CENTRIFUGE_API_KEY=<secret-key-for-centrifuge-api>
CENTRIFUGE_CONNECTION_URL='ws://localhost:8000/connection/websocket
CENTRIFUGE_URL=http://localhost:8000
CENTRIFUGE_VERIFY=false
CENTRIFUGE_SSL_KEY=/etc/ssl/some.pem
CENTRIFUGE_BROADCAST_ERROR=true
```

Do not forget to install the broadcast driver

```
BROADCAST_DRIVER=centrifuge
```

## Basic Usage

To configure the Centrifugo server, read the [official documentation](https://fzambia.gitbooks.io/centrifugal/content)

For broadcasting events, see the [official documentation of laravel](https://laravel.com/docs/5.3/broadcasting)

A simple example:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use IliaKologrivov\Centrifuge\Channels\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class event implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastAs()
    {
        return 'event-name';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Centrifuge\Channels\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('example-channel');
    }
}
```

Use Blade directive in your views
```html
@centrifugeConnectToken(<custom_user_token>)//get connect token for custom id user
@centrifugeAuthConnectToken(86600)//get connect user for auth user
@centrifugeWsConnectUrl()//get connect url for ws
```

Example view
```html
<script>
const centrifugeUserToken = '@centrifugeAuthConnectToken(86600)';
const centrifugeConnectionUrl = '@centrifugeWsConnectUrl';
</script>
<script src="{!! mix('/js/app.js') !!}"></script>
```

Added in bootstrap.js
```js
window.Centrifuge = require('centrifuge');
window.centrifuge = new Centrifuge(centrifugeConnectionUrl);

centrifuge.setToken(centrifugeUserToken);
centrifuge.connect();

centrifuge.on('connect', function(context) {
    console.log('connect', context);
}).on('disconnect', function(context) {
    console.error('disconnect', context);
    centrifuge.disconnect();
}).on('error', function(error) {
    console.error(error);
});
```

## License

The MIT License (MIT). Please see [License File](https://github.com/LaraComponents/centrifuge-broadcaster/blob/master/LICENSE) for more information.
