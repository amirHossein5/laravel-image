<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Intervention\Image\Facades\Image as Intervention;

class FakeTest extends TestCase
{
    public function test_fake_method()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->save();

        $this->assertFalse(file_exists(public_path($image['index'])));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();
            
        foreach ($image['index'] as $image) {
            $this->assertFalse(file_exists(public_path($image)));
        }
    }
}
