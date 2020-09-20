<?php

namespace IliaKologrivov\Centrifuge;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\View\Compilers\BladeCompiler;

/**
 * Class CentrifugeServiceProvider
 * @package IliaKologrivov\Centrifuge
 */
class CentrifugeServiceProvider extends ServiceProvider
{
    /**
     * Add centrifuge broadcaster.
     *
     * @param  BroadcastManager  $broadcastManager
     * @param  BladeCompiler  $blade
     */
    public function boot(BroadcastManager $broadcastManager, BladeCompiler $blade)
    {
        $broadcastManager->extend('centrifuge', function ($app) {
            return new CentrifugeBroadcaster($app->make('centrifuge'));
        });

        $blade->directive('centrifugeConnectToken', function ($expression) {
            return "<?php echo app('centrifuge')->connToken($expression); ?>";
        });

        $blade->directive('centrifugeAuthConnectToken', function ($expression) {
            if (!empty($expression)) {
                return "<?php echo app('centrifuge')->connToken(Illuminate\Support\Facades\Auth::user()->getKey(), $expression); ?>";
            }

            return "<?php echo app('centrifuge')->connToken(Illuminate\Support\Facades\Auth::user()->getKey()); ?>";
        });

        $blade->directive('centrifugeWsConnectUrl', function ($expression) {
            return "<?php echo app('centrifuge')->getWsConnectUrl(); ?>";
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/centrifuge.php', 'centrifuge');

        $this->publishes([
            __DIR__ . '/../config/centrifuge.php' => config_path('centrifuge.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Centrifuge::class, function($app) {
            $config = $app->make('config')->get('centrifuge');

            return new Centrifuge($config, new Client());
        });

        $this->app->alias(Centrifuge::class, 'centrifuge');
    }
}
