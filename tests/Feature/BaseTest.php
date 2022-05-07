<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Intervention\Image\Facades\Image as Intervention;

class BaseTest extends TestCase 
{
    public function test_image_that_defined_with_intervention_saves()
    {
        $img = $this->interventionCircle();
        $image = Image::raw($img)
            ->in('circle')
            ->save();

        $this->assertTrue(file_exists(public_path($image['index'])));

        $img = $this->interventionCircle();
        $image = Image::make($img)
            ->setExclusiveDirectory('circle')
            ->save();

        foreach ($image['index'] as $path) {
            $this->assertTrue(file_exists(public_path($path)));
        }

        $img = Intervention::make('https://avatars.githubusercontent.com/u/68776630?s=40&v=4');
        $image = Image::raw($img)
            ->in('avatar')
            ->save();

        $this->assertTrue(file_exists(public_path($image['index'])));
    }

    public function test_raw_creates_image_in_root_public_directory()
    {
        $image = Image::raw($this->image)
            ->in('')
            ->be('root.jpg')
            ->save(false, fn ($image) => $image->imagePath);

        $this->assertFileExists(public_path($image));

        $this->assertTrue(Image::rm($image));

        $image = Image::raw($this->image)
            ->be('root.jpg')
            ->save(false, fn ($image) => $image->imagePath);

        $this->assertFileExists(public_path($image));
    }

    public function test_result_array()
    {
        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals(3, count($image));
        $this->assertArrayNotHasKey('default_size', $image);
        $this->assertDirectoryExists(public_path($image['imageDirectory']));
        $this->assertEquals('public', $image['disk']);
        
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }
        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }

        config(['image.default_size' => 'small']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->save();

        $this->assertEquals('storage', $image['disk']);
        $this->assertEquals(4, count($image));
        $this->assertEquals($image['default_size'], 'small');

        // raw method
        $image = Image::raw($this->image)->disk('storage')->save();

        $this->assertEquals(3, count($image));
        $this->assertTrue(!is_array($image['index']));
        $this->assertArrayNotHasKey('default_size', $image);
        $this->assertDirectoryExists(storage_path('app/' . $image['imageDirectory']));
        $this->assertEquals('storage', $image['disk']);
        $this->assertFileExists(storage_path('app/' . $image['index']));

        $image = Image::raw($this->image)->resize(50,30)->save();

        $this->assertEquals(3, count($image));
        $this->assertTrue(!is_array($image['index']));
        $this->assertArrayNotHasKey('default_size', $image);
        $this->assertDirectoryExists(public_path($image['imageDirectory']));
        $this->assertEquals('public', $image['disk']);
        $this->assertFileExists(public_path($image['index']));

        $image = Image::raw($this->image)->resizeBy(config('image.imageSizes'))->save();

        $this->assertEquals(3, count($image));
        $this->assertTrue(is_array($image['index']));
        $this->assertArrayNotHasKey('default_size', $image);
        $this->assertDirectoryExists(public_path($image['imageDirectory']));
        $this->assertEquals('public', $image['disk']);
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }
    }

    
}