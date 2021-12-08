<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class ImageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/image.php' => config_path('image.php'),
        ], 'image');
        $this->mergeConfigFrom(
            __DIR__ . '/../config/image.php',
            'image'
        );
    }

    public function register()
    {
        $this->app->bind('laravelImage', function ($app) {
            return new Image();
        });
        $this->app->singleton('image', function ($app) {
            return new ImageManager(config('image'));
        });
    }
}
