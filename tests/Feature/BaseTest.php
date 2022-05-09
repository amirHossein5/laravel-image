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
    public function test_converts_to_correct_directory_separator()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('1\\2\\3\\')
            ->save();

        $this->assertStringContainsString(
            '1' . DIRECTORY_SEPARATOR . '2' . DIRECTORY_SEPARATOR . '3' . DIRECTORY_SEPARATOR,
            $image['imageDirectory']
        );
    }

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

        $image = Image::raw($this->image)->save(false, fn ($image) => $image->imagePath);

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

        $image = Image::raw($this->image)->resize(50, 30)->save();

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
        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }
        foreach ($image['index'] as $img) {
            $this->assertFileExists(public_path($img));
        }

        config(['image.default_size' => 'small']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertTrue(array_key_exists('default_size', $image));

        $image = Image::raw($this->image)
            ->in('post')
            ->save();

        $this->assertTrue(array_key_exists('index', $image));
        $this->assertTrue(array_key_exists('imageDirectory', $image));
        $this->assertTrue(!array_key_exists('default_size', $image));
    }

    public function test_result_array_can_be_get_manually()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return [
                    'index'          => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory,
                ];
            });

        $this->assertEquals(
            $this->directorySeparator(
                config('image.root_directory') . '/' . 'post' . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $this->random(false)
            ),
            $image['imageDirectory']
        );

        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }
    }

    public function test_saves_and_replaces_image()
    {
        $image = Image::raw($this->image)
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $image = Image::raw($this->image)
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::wasRecentlyRemoved());
        $this->assertTrue($image == true);
        $this->assertFileExists(public_path($image));

        // make method
        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageName('test')
            ->setSizesDirectory('test')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageName('test')
            ->setSizesDirectory('test')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue($images == true);
        $this->assertTrue(Image::wasRecentlyRemoved());

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }
    }

    public function test_manually_way_properties()
    {
        $random = $this->random(false);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setSizesDirectory($random)
            ->quality(0)
            ->save(false, function ($image) {
                return [
                    'image' => $image->image,
                    'sizes' => $image->sizes,
                    'defaultSize' => $image->defaultSize,
                    'imagePath' => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory,
                    'imageName' => $image->imageName,
                    'imageFormat' => $image->imageFormat,
                    'disk' => $image->disk,
                    'rootDirectory' => $image->rootDirectory,
                    'exclusiveDirectory' => $image->exclusiveDirectory,
                    'archiveDirectories' => $image->archiveDirectories,
                    'sizesDirectory' => $image->sizesDirectory,
                    'quality' => $image->quality,
                ];
            });

        $this->propertiesAreValid($image, config('image.imageSizes'), false, 0, $random);

        $image = Image::raw($this->image)
            ->save(false, function ($image) {
                return [
                    'image' => $image->image,
                    'sizes' => $image->sizes,
                    'defaultSize' => $image->defaultSize,
                    'imagePath' => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory,
                    'imageName' => $image->imageName,
                    'imageFormat' => $image->imageFormat,
                    'disk' => $image->disk,
                    'rootDirectory' => $image->rootDirectory,
                    'exclusiveDirectory' => $image->exclusiveDirectory,
                    'archiveDirectories' => $image->archiveDirectories,
                    'sizesDirectory' => $image->sizesDirectory,
                    'quality' => $image->quality,
                ];
            });

        $this->propertiesAreValid($image, [], true, 90, $random);

        $image = Image::raw($this->image)
            ->quality(20)
            ->save(false, function ($image) {
                return [
                    'image' => $image->image,
                    'sizes' => $image->sizes,
                    'defaultSize' => $image->defaultSize,
                    'imagePath' => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory,
                    'imageName' => $image->imageName,
                    'imageFormat' => $image->imageFormat,
                    'disk' => $image->disk,
                    'rootDirectory' => $image->rootDirectory,
                    'exclusiveDirectory' => $image->exclusiveDirectory,
                    'archiveDirectories' => $image->archiveDirectories,
                    'sizesDirectory' => $image->sizesDirectory,
                    'quality' => $image->quality,
                ];
            });

        $this->propertiesAreValid($image, [], true, 20, $random);

        $image = Image::raw($this->image)
            ->resizeBy(config('image.imageSizes'))
            ->setSizesDirectory($random)
            ->quality(0)
            ->in('post/')
            ->save(false, function ($image) {
                return [
                    'image' => $image->image,
                    'sizes' => $image->sizes,
                    'defaultSize' => $image->defaultSize,
                    'imagePath' => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory,
                    'imageName' => $image->imageName,
                    'imageFormat' => $image->imageFormat,
                    'disk' => $image->disk,
                    'rootDirectory' => $image->rootDirectory,
                    'exclusiveDirectory' => $image->exclusiveDirectory,
                    'archiveDirectories' => $image->archiveDirectories,
                    'sizesDirectory' => $image->sizesDirectory,
                    'quality' => $image->quality,
                ];
            });

        $this->propertiesAreValid($image, config('image.imageSizes'), true, 0, $random);
    }

    /**
     * Tests the output of manually given image.
     * 
     * @param array $image
     * @param array $sizes
     * @param bool $isRaw
     * @param int $quality
     *  
     * @return void
     */
    private function propertiesAreValid(array $image, array $sizes = [], bool $isRaw = false, int $quality = 90, int $random): void
    {
        $this->assertEquals(get_class($image['image']), \Intervention\Image\Image::class);
        if ($sizes) {
            foreach ($sizes as $sizeName => $dimensions) {
                $this->assertArrayHasKey($sizeName, $image['sizes']);
            };
        } else {
            $this->assertNull($image['sizes']);
        } 
        $this->assertNull($image['defaultSize']);
        if (is_array($image['imagePath'])) {
            foreach ($image['imagePath'] as $path) {
                $this->assertFileExists(public_path($path));
            }
        } else {
            $this->assertFileExists(public_path($image['imagePath']));
        }
        $this->assertDirectoryExists(public_path($image['imageDirectory']));
        $this->assertIsString($image['imageDirectory']);
        if ($sizes) {
            $fileName = pathinfo(public_path($image['imagePath']['small']), PATHINFO_FILENAME);
            $extension = pathinfo(public_path($image['imagePath']['small']), PATHINFO_EXTENSION);
            $this->assertEquals($fileName, $image['imageName'] . '_small');
        } else {
            $fileName = pathinfo(public_path($image['imagePath']), PATHINFO_FILENAME);
            $extension = pathinfo(public_path($image['imagePath']), PATHINFO_EXTENSION);
            $this->assertEquals($fileName, $image['imageName']);
        }
        $this->assertEquals($extension, $image['imageFormat']);
        $this->assertEquals('public', $image['disk']);
        if (!$isRaw) {
            $this->assertEquals(config('image.root_directory'), $image['rootDirectory']);
            $this->assertEquals('post', $image['exclusiveDirectory']);
            $this->assertEquals(
                $this->directorySeparator(date('Y') . '/' . date('m') . '/' . date('d')),
                $image['archiveDirectories']
            );
        } 
        $this->assertEquals($random, $image['sizesDirectory']);
        $this->assertEquals($quality, $image['quality']);
    }
}
