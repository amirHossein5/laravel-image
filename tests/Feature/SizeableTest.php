<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;

class SizeableTest extends TestCase
{
    public function test_when_no_or_one_size_make_method()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName($random = $this->random())
            ->setImageFormat('png')
            ->autoResize()
            ->save();

        $this->assertEquals($this->directorySeparator('images/post/arch'), $image['imageDirectory']);
        $this->assertEquals(
            $this->directorySeparator("images/post/arch/$random.png"),
            $image['index']
        );
        $this->assertFileExists(public_path($image['index']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName($random = $this->random())
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals($this->directorySeparator('images/post/arch'), $image['imageDirectory']);
        $this->assertFileExists(public_path($image['index']));
        $this->assertEquals(
            $this->directorySeparator("images/post/arch/{$random}_0.png"),
            $image['index']
        );
        $this->assertFileExists(public_path($image['index']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName($random = $this->random())
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals($this->directorySeparator('images/post/arch'), $image['imageDirectory']);
        $this->assertEquals(
            $this->directorySeparator("images/post/arch/{$random}_large.png"),
            $image['index']
        );
        $this->assertFileExists(public_path($image['index']));
    }

    public function test_when_one_size_raw_method()
    {
        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName($random = $this->random())
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals($this->directorySeparator("post/{$random}_0.png"), $image['index']);
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName($random = $this->random())
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals($this->directorySeparator("post/{$random}_large.png"), $image['index']);
        $this->assertFileExists(public_path($image['index']));
    }

    public function test_when_more_than_one_size_make_method()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName($random = $this->random(false))
            ->setImageFormat('png')
            ->resize(50, 20)
            ->alsoResize(100, 50, 'large')
            ->alsoResize(100, 50,)
            ->save();

        $expectedPath = $this->directorySeparator('images/post/arch/' . $random);
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertFileExists(public_path($path));
            $this->assertEquals(
                $expectedPath . DIRECTORY_SEPARATOR . "{$random}_{$sizeName}.png",
                $path
            );
        }
    }

    public function test_when_more_than_one_size_raw_method()
    {
        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName($random = $this->random(false))
            ->setImageFormat('png')
            ->resize(50, 20)
            ->alsoResize(100, 50, 'large')
            ->alsoResize(100, 50)
            ->save();

        $expectedPath = 'post' . DIRECTORY_SEPARATOR . $random;
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . $random . '_0.png', $image['index'][0]);
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . $random . '_large.png', $image['index']['large']);

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertFileExists(public_path($path));
            $this->assertEquals(
                $expectedPath . DIRECTORY_SEPARATOR . "{$random}_{$sizeName}.png",
                $path
            );
        }
    }

    public function test_auto_resize_removes_sizes()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertFileExists(public_path($image['index']));
    }

    public function test_resize_removes_predefined_sizes_and_adds_given_size()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertFileExists(public_path($image['index']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->resize(10, 10)
            ->resize(10, 10, 'large')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(10, 10, 'large')
            ->save();

        $this->assertStringContainsString($image['index'], 'post' . DIRECTORY_SEPARATOR . 'name_large.png');
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(10, 10)
            ->resize(10, 10, 'large')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(10, 10, 'large')
            ->save();

        $this->assertStringContainsString($image['index'], 'post' . DIRECTORY_SEPARATOR . 'name_large.png');
        $this->assertFileExists(public_path($image['index']));
    }

    public function test_also_resize_adds_size()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->alsoResize(100, '200')
            ->save();

        $this->assertFalse(is_string($image['index']));
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->alsoResize(100, '200', 'small')
            ->save();

        $this->assertFalse(is_string($image['index']));
        $this->assertArrayHasKey('small', $image['index']);
        $this->assertEquals(count($image['index']), 3);
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(100, '200')
            ->alsoResize(100, '200')
            ->save();

        $this->assertFalse(is_string($image['index']));
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }
    }

    public function test_resize_by_adds_array_of_sizes()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width'  => '200',
                    'height' => 300,
                ],
            ])
            ->save();
            
        $this->assertTrue(is_array($image['index']));
        foreach ($image['index'] as $key => $path) {
            $this->assertStringContainsString("_$key.png", $image['index'][$key]);
        }

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width'  => '200',
                    'height' => 300,
                ],
            ])
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertStringContainsString('_large.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width'  => '200',
                    'height' => 300,
                ],
                'small' => [
                    'width'  => 50,
                    'height' => 20,
                ],
            ])
            ->save();

        $this->assertFalse(is_string($image['index']));
        foreach ($image['index'] as $sizeName => $size) {
            $this->assertStringContainsString("_{$sizeName}.png", $image['index'][$sizeName]);
        }

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width'  => '200',
                    'height' => 300,
                ],
                'small' => [
                    'width'  => 50,
                    'height' => 20,
                ],
            ])
            ->save();

        $this->assertFalse(is_string($image['index']));
        foreach ($image['index'] as $sizeName => $size) {
            $this->assertStringContainsString("_{$sizeName}.png", $image['index'][$sizeName]);
        }
    }

    public function test_set_default_size_effects_on_output()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertArrayNotHasKey('default_size', $image);

        config(['image.default_size' => 'large']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertArrayHasKey('default_size', $image);

        $image = Image::raw($this->image)
            ->in('post')
            ->save();

        $this->assertArrayNotHasKey('default_size', $image);
    }

    public function test_changing_default_size_after_created_image()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);

        $image = Image::raw($this->image)
            ->in('post')
            ->resizeBy(config('image.imageSizes'))
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);

        config(['image.default_size' => 'small']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);

        $image = Image::raw($this->image)
            ->in('post')
            ->resizeBy(config('image.imageSizes'))
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);
    }

    public function test_changing_default_size_after_created_image_and_gotten_manually()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);

        $image = Image::raw($this->image)
            ->in('post')
            ->resizeBy(config('image.imageSizes'))
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $image = Image::setDefaultSizeFor($image, 'small', 'paths');

        $this->assertEquals('small', $image['default_size']);

        config(['image.default_size' => 'small']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $image = Image::setDefaultSizeFor($image, 'small', 'paths');

        $this->assertEquals('small', $image['default_size']);

        $image = Image::raw($this->image)
            ->in('post')
            ->resizeBy(config('image.imageSizes'))
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);
    }

    public function test_disk_works()
    {
        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->save();

        foreach ($images['index'] as $image) {
            $this->assertFileExists(storage_path('app/' . $image));
        }

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('public')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(storage_path('app/' . $image));
        }

        config(['image.disks.storage-public' => storage_path('public')]);

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage-public')
            ->setRootDirectory('all')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(storage_path('public/' . $image));
        }

        $image = Image::raw($this->image)
            ->disk('public')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $image = Image::raw($this->image)
            ->disk('storage')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(storage_path('app/' . $image));

        $image = Image::raw($this->image)
            ->disk('storage-public')
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(storage_path('public/' . $image));
    }
}
