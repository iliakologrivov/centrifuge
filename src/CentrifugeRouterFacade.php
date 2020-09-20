<?php

namespace IliaKologrivov\Centrifuge;

use Illuminate\Support\Facades\Facade;


/**
 * Class CentrifugeRouterFacade
 *
 * @method static void subscribe(string $uri = '/centrifuge/subscribe')
 * @method static void refresh(string $uri = '/centrifuge/refresh')
 *
 * @package Centrifuge
 */
class CentrifugeRouterFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CentrifugeRouter::class;
    }
}
