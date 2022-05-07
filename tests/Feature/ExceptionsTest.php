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

    public function test_invalid_disk_throws_exception()
    {
        $this->expectException(InvalidParameterException::class);

        Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage-public')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->expectExceptionMessage('Undefined disk storage-public');
    }

    public function test_rm_wont_throw_exception_when_path_is_unlocatable()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->save();

        Image::rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::rm($image);
        $this->assertFalse(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        Image::rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::rm($image);
        $this->assertFalse(Image::wasRecentlyRemoved());

        // manually give index
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return ['test' => $image->imagePath];
            });

        Image::rm($image, 'test');
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::rm($image, 'test');
        $this->assertFalse(Image::wasRecentlyRemoved());

        // custom disk
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->disk('storage')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        Image::disk('storage')->rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::disk('storage')->rm($image);
        $this->assertFalse(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->autoResize()
            ->save();

        Image::disk('storage')->rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::disk('storage')->rm($image);
        $this->assertFalse(Image::wasRecentlyRemoved());

        // manually give index -- custom disk
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->disk('storage')
            ->save(false, function ($image) {
                return ['test' => $image->imagePath];
            });

        Image::disk('storage')->rm($image, 'test');
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::disk('storage')->rm($image, 'test');
        $this->assertFalse(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->autoResize()
            ->save(false, function ($image) {
                return ['test' => $image->imagePath];
            });

        Image::disk('storage')->rm($image, 'test');
        $this->assertTrue(Image::wasRecentlyRemoved());
        Image::disk('storage')->rm($image, 'test');
        $this->assertFalse(Image::wasRecentlyRemoved());
    }
}
