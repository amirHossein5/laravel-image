<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class RemoveableTest extends TestCase
{
    public function test_rm_works()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        foreach ($image['index'] as $path) {
            $this->assertFileExists(public_path($path));
        }

        Image::rm($image);

        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFalse(file_exists(public_path($image['imageDirectory'])));

        foreach ($image['index'] as $path) {
            $this->assertFalse(file_exists(public_path($path)));
        }

        $random = $this->random();

        $image = Image::raw($this->image)
            ->be($random . '.png')
            ->save();

        $this->assertFileExists(public_path($random . '.png'));

        Image::rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFalse(file_exists(public_path($random . '.png')));

        $random = $this->random();

        $image = Image::raw($this->image)
            ->in('test')
            ->be($random . '.png')
            ->save();

        $this->assertFileExists(public_path('test/' . $random . '.png'));

        Image::disk('public')->rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(public_path('test/' . $random . '.png'));

        $random = $this->random();

        $image = Image::raw($this->image)
            ->be($random . '.png')
            ->disk('storage')
            ->save();

        $this->assertFileExists(storage_path('app/' . $random . '.png'));

        Image::disk('storage')->rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(storage_path('app/test/' . $random . '.png'));

        $random = $this->random();

        $image = Image::raw($this->image)
            ->in('test')
            ->disk('storage')
            ->be($random . '.png')
            ->save();

        $this->assertFileExists(storage_path('app/test/' . $random . '.png'));

        Image::disk('storage')->rm($image);
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(storage_path('app/test/' . $random . '.png'));

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('public')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }

        $this->assertTrue(Image::disk('public')->rm(['index' => $images], 'index'));
        foreach ($images as $image) {
            $this->assertFileDoesNotExist(public_path($image));
        }
        $this->assertTrue(Image::wasRecentlyRemoved());


        $image = Image::raw($this->image)
            ->disk('public')
            ->in('')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertFileDoesNotExist(public_path($image));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::raw($this->image)
            ->disk('public')
            ->in('')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));
        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertFileDoesNotExist(public_path($image));
        $this->assertTrue(Image::wasRecentlyRemoved());
    }

    public function test_rm_removes_directory_when_is_empty()
    {
        $image1 = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setSizesDirectory('test')
            ->save();

        $image2 = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setSizesDirectory('test')
            ->save();

        $image3 = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setSizesDirectory('test')
            ->save();

        Image::rm($image1);

        $FileSystem = new Filesystem();

        $files = $FileSystem->files(public_path($image1['imageDirectory']));

        $this->assertTrue($FileSystem->exists(public_path($image1['imageDirectory'])));
        $this->assertFalse(empty($files));
        $this->assertTrue(Image::wasRecentlyRemoved());

        Image::rm($image2);

        $files = $FileSystem->files(public_path($image2['imageDirectory']));

        $this->assertTrue($FileSystem->exists(public_path($image2['imageDirectory'])));
        $this->assertFalse(empty($files));
        $this->assertTrue(Image::wasRecentlyRemoved());

        Image::rm($image3);

        $this->assertFalse($FileSystem->exists(public_path($image3['imageDirectory'])));
        $this->assertTrue(Image::wasRecentlyRemoved());
    }

    public function test_rm_works_with_getted_menually_array()
    {
        $image = Image::raw($this->image)
            ->in('post/test')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(public_path($image));

        $image = Image::raw($this->image)
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(public_path($image));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->alsoResize(500, 200)
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(public_path($image));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $this->assertTrue(Image::disk('public')->rm($image, 'paths'));
        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertFileDoesNotExist(public_path($image['paths']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $this->assertTrue(Image::disk('public')->rm($image, 'paths'));
        $this->assertTrue(Image::wasRecentlyRemoved());
        foreach ($image['paths'] as $path) {
            $this->assertFileDoesNotExist(public_path($path));
        }
    }
}
