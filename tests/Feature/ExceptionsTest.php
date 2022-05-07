<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Intervention\Image\Facades\Image as Intervention;

class ExceptionsTest extends TestCase
{
    public function test_make_method_needs_exclusive_directory()
    {
        $this->expectException(LogicException::class);

        Image::make($this->image)
            ->save();
    }

    public function test_if_default_size_sets_wrong_throws_exception()
    {
        Image::fake();

        config(['image.default_size' => 'wrong-key']);

        $this->expectException(\ErrorException::class);

        Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();
    }
}
