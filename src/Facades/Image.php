<?php

namespace AmirHossein5\LaravelImage\Facades;

use Illuminate\Support\Facades\Facade;

class Image extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravelImage';
    }
}
