<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TrackCreator
{
    protected static function bootTrackCreator(): void
    {
        static::creating(function ($model) {
            if (empty($model->created_by) && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (empty($model->updated_by) && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
