<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;

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

    public function test_dierctory_setters_for_make_method()
    {
        $image = Image::make($this->image)
            ->setRootDirectory('root')
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('archive')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals(
            $this->directorySeparator("root/post/archive/{$this->random(false)}/name_large.png"),
            $image['index']['large']
        );
        $this->assertEquals(
            $this->directorySeparator("root/post/archive/{$this->random(false)}"),
            $image['imageDirectory']
        );
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }
    }

    public function test_directory_setters_for_raw_method()
    {
        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals($this->directorySeparator('post/name.png'), $image['index']);
        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->disk('storage')
            ->setSizesDirectory('size')
            ->resizeBy(config('image.imageSizes'))
            ->be('name.png')
            ->save();

        $this->assertEquals(
            $this->directorySeparator('post/size/name_large.png'),
            $image['index']['large']
        );
        $this->assertEquals(
            $this->directorySeparator('post/size'),
            $image['imageDirectory']
        );
        foreach ($image['index'] as $img) {
            $this->assertFileExists(storage_path('app/'.$img));
        }
    }
}
