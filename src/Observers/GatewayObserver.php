<?php

namespace Shieldforce\CheckoutPayment\Observers;

use Illuminate\Database\Eloquent\Model;
use Shieldforce\CheckoutPayment\Models\CppGateways;

class GatewayObserver
{
    public function creating(Model $model)
    {
        //
    }

    public function created(Model $model): void
    {
        //
    }

    public function saved(Model $model): void
    {
        if ($model->active) {
            CppGateways::where('id', '!=', $model->id)->update([
                'active' => 0,
            ]);
        }
    }

    public function updating(Model $model): void
    {
        //
    }

    public function updated(Model $model): void
    {
        //
    }

    public function deleted(Model $model): void
    {
        //
    }

    public function restored(Model $model): void
    {
        //
    }

    public function forceDeleted(Model $model): void
    {
        //
    }

    public function deleting(Model $model): void
    {
        //
    }
}
