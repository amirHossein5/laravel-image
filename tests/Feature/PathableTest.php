<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Intervention\Image\Facades\Image as Intervention;

class PathableTest extends TestCase
{
    public function test_be_method()
    {
        $image = Image::raw($this->image)
            ->be('logo-name.is.png')
            ->save();

        $this->assertTrue($image);
        $this->assertFileExists(public_path($image));
    }
    public function test_be_method_sets_both_name_and_format_for_make_method()
    {
        $image = Image::make($this->image)
            ->setRootDirectory('root')
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('archive')
            ->setSizesDirectory('size')
            ->be('name.png')
            ->save();

        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size' . DIRECTORY_SEPARATOR . 'name_large.png', $image['index']['large']);
        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size', $image['imageDirectory']);

        $image = Image::make($this->image)
            ->setRootDirectory('root')
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('archive')
            ->setSizesDirectory('size')
            ->be('name.png.png')
            ->save();

        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size' . DIRECTORY_SEPARATOR . 'name.png_large.png', $image['index']['large']);
        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size', $image['imageDirectory']);
    }

    public function test_be_method_sets_both_name_and_format_for_raw_method()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->be('name.png')
            ->save();

        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name.png', $image['index']);
        $this->assertEquals('post', $image['imageDirectory']);

        $image = Image::raw($this->image)
            ->in('post')
            ->be('name.png.png')
            ->save();

        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name.png.png', $image['index']);
        $this->assertEquals('post', $image['imageDirectory']);
    }

}
