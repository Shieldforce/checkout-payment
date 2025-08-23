<?php

namespace Shieldforce\CheckoutPayment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Shieldforce\CheckoutPayment\CheckoutPayment
 */
class CheckoutPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Shieldforce\CheckoutPayment\CheckoutPayment::class;
    }
}
