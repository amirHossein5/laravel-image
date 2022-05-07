<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Intervention\Image\Facades\Image as Intervention;

class PathableTest extends TestCase
{
    public function test_be_method()
    {
        $image = Image::raw($this->image)
            ->be('logo name.is.png')
            ->save();

        $this->assertEquals(pathinfo(public_path($image['index']), PATHINFO_EXTENSION), 'png');
        $this->assertEquals(
            pathinfo(public_path($image['index']), PATHINFO_FILENAME),
            'logo name.is'
        );

        $image = Image::raw($this->image)
            ->be('logo-name.png')
            ->save();

        $this->assertEquals(pathinfo(public_path($image['index']), PATHINFO_EXTENSION), 'png');
        $this->assertEquals(
            pathinfo(public_path($image['index']), PATHINFO_FILENAME),
            'logo-name'
        );
    }
}
