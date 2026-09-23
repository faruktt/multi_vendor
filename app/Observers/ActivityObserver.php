<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    public function created(Model $model): void
    {
        ActivityLogger::log('created', $model);
    }

    public function updated(Model $model): void
    {
        ActivityLogger::log('updated', $model);
    }

    public function deleted(Model $model): void
    {
        ActivityLogger::log('deleted', $model);
    }
}
