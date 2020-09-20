<?php

namespace IliaKologrivov\Centrifuge;

use Illuminate\Routing\Router;

/**
 * Class CentrifugeRouter
 * @package IliaKologrivov\Centrifuge
 */
class CentrifugeRouter
{
    /**
     * @var Router
     */
    private $router;

    /**
     * CentrifugeRouter constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $uri
     */
    public function subscribe(string $uri = '/centrifuge/subscribe'): void
    {
        $this->router
            ->post($uri, [CentrifugeController::class, 'subscribe'])
            ->name('centrifuge.subscribe');
    }

    /**
     * @param string $uri
     */
    public function refresh(string $uri = '/centrifuge/refresh'): void
    {
        $this->router
            ->post($uri, [CentrifugeController::class, 'refresh'])
            ->name('centrifuge.refresh');
    }
}
