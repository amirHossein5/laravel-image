<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Facade\FlareClient\Http\Exceptions\MissingParameter;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class ImageTest extends TestCase
{
    public function test_when_fake_method_uses_wont_create_image()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->save();

        $this->assertFalse(file_exists(public_path($image['index'])));
    }

    public function test_make_method_needs_exclusive_directory()
    {
        Image::fake();

        $this->expectException(MissingParameter::class);

        Image::make($this->image)
            ->save();
    }

    public function test_raw_method_needs_in_variable()
    {
        Image::fake();

        $this->expectException(MissingParameter::class);

        Image::raw($this->image)
            ->save();
    }

    public function test_raw_in_public_root_directory_creates_image()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals(DIRECTORY_SEPARATOR . 'name.png', $image['index']);
    }

    public function test_make_method_sets_defaults_for_directories_and_sizes()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals(
            config('image.root_directory') . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $this->random(false),
            $image['imageDirectory']
        );

        $this->assertArrayNotHasKey('default_size', $image);

        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }
    }

    public function test_raw_method_wont_set_default_variables_and_sizes()
    {
        Image::fake();

        $imageResult = Image::raw($this->image)
            ->in('test')
            ->setImageName('name')
            ->save();

        $this->assertEquals('test' . DIRECTORY_SEPARATOR . 'name.' . $this->image->getClientOriginalExtension(), $imageResult['index']);
        $this->assertEquals('test', $imageResult['imageDirectory']);
        $this->assertArrayNotHasKey('default_size', $imageResult);
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

    public function test_disk_will_be_return_in_result_array()
    {
        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'public');

        $images = Image::make($this->image)
            ->disk('public')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'public');

        $images = Image::make($this->image)
            ->disk('storage')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'storage');

        config(['image.disks.storage-public' => storage_path('public')]);

        $images = Image::make($this->image)
            ->disk('storage-public')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'storage-public');

        $images = Image::raw($this->image)
            ->in('post')
            ->save();

        $this->assertEquals($images['disk'], 'public');

        $images = Image::raw($this->image)
            ->in('')
            ->disk('public')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'public');

        $images = Image::raw($this->image)
            ->in('post')
            ->disk('storage')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'storage');

        config(['image.disks.storage-public' => storage_path('public')]);

        $images = Image::raw($this->image)
            ->in('post')
            ->disk('storage-public')
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertEquals($images['disk'], 'storage-public');
    }

    /**
     * Tests for directory and size setters.
     */
    public function test_be_method_sets_both_name_and_format_for_make_method()
    {
        Image::fake();

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

    public function test_make_method_and_directory_setters_set_directories_correctly()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setRootDirectory('root')
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('archive')
            ->setSizesDirectory('size')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size' . DIRECTORY_SEPARATOR . 'name_large.png', $image['index']['large']);
        $this->assertEquals('root' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'size', $image['imageDirectory']);
    }

    public function test_raw_method_and_directory_setters_set_directories_correctly()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name.png', $image['index']);
        $this->assertEquals('post', $image['imageDirectory']);
    }

    public function test_with_make_method_and_default_sizes_sets_correct_imagePath_and_imageDirectory()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }

        $this->assertStringContainsString(DIRECTORY_SEPARATOR . $this->random(false), $image['imageDirectory']);
    }

    public function test_with_raw_method_and_default_sizes_sets_correct_imagePath_and_imageDirectory()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name.png', $image['index']);
        $this->assertEquals('post', $image['imageDirectory']);
    }

    public function test_with_make_method_without_or_one_size_returns_correct_imageDirectory_and_imagePath()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->autoResize()
            ->save();

        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch', $image['imageDirectory']);
        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . 'name.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch', $image['imageDirectory']);
        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . 'name_0.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch', $image['imageDirectory']);
        $this->assertEquals('images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . 'name_large.png', $image['index']);
    }

    public function test_with_raw_method_without_or_one_size_returns_correct_imageDirectory_and_imagePath()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name_0.png', $image['index']);

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals('post' . DIRECTORY_SEPARATOR . 'name_large.png', $image['index']);
    }

    public function test_make_method_with_more_than_one_given_size_returns_correct_path()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(50, 20)
            ->alsoResize(100, 50, 'large')
            ->save();

        $expectedPath = 'images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . $this->random(false);
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . "name_{$sizeName}.png", $path);
        }
    }

    public function test_raw_method_with_more_than_one_given_size_returns_correct_path()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(50, 20)
            ->alsoResize(100, 50, 'large')
            ->save();

        $expectedPath = 'post' . DIRECTORY_SEPARATOR . $this->random(false);
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . "name_0.png", $image['index'][0]);
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . "name_large.png", $image['index']['large']);

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR . "name_{$sizeName}.png", $path);
        }
    }

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

    public function test_returns_correct_array_structure()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save();

        $this->assertTrue(array_key_exists('index', $image));
        $this->assertTrue(array_key_exists('imageDirectory', $image));
        $this->assertTrue(!array_key_exists('default_size', $image));

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

    /**
     * Test size setters.
     */
    public function test_auto_resize_removes_sizes()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->save();

        $this->assertTrue(is_string($image['index']));
    }

    public function test_resize_removes_predefined_sizes_and_adds_given_size()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->resize(10, 10)
            ->resize(10, 10)
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(10, 10, 'large')
            ->save();

        $this->assertEquals($image['index'], 'images' . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . 'arch' . DIRECTORY_SEPARATOR . 'name_large.png');

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(10, 10)
            ->resize(10, 10)
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(10, 10, 'large')
            ->save();

        $this->assertStringContainsString($image['index'], 'post' . DIRECTORY_SEPARATOR . 'name_large.png');
    }

    public function test_also_resize_adds_size()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->alsoResize(100, '200')
            ->save();

        $this->assertFalse(is_string($image['index']));

        $image = Image::raw($this->image)
            ->in('post')
            ->resize(100, '200')
            ->alsoResize(100, '200')
            ->save();

        $this->assertFalse(is_string($image['index']));
    }

    public function test_resize_by_adds_array_of_sizes()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width' => '200',
                    'height' => 300
                ]
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
                    'width' => '200',
                    'height' => 300
                ]
            ])
            ->save();

        $this->assertTrue(is_string($image['index']));
        $this->assertStringContainsString('_large.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width' => '200',
                    'height' => 300
                ],
                'small' => [
                    'width' => 50,
                    'height' => 20
                ]
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
                    'width' => '200',
                    'height' => 300
                ],
                'small' => [
                    'width' => 50,
                    'height' => 20
                ]
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

        $image = Image::setDefaultSizeFor($image, 'small', 'paths');

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

        $image = Image::setDefaultSizeFor($image, 'small', 'paths');

        $this->assertEquals('small', $image['default_size']);
    }

    /**
     *
     */
    public function test_saves_and_removes_image_correctly()
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
            ->in('')
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
            ->in('')
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

        $image = Image::raw($this->image)
            ->in('')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->alsoResize(500, 200)
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertTrue(Image::disk('public')->rm($image));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $this->assertTrue(Image::disk('public')->rm($image, 'paths'));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return ['paths' => $image->imagePath];
            });

        $this->assertTrue(Image::disk('public')->rm($image, 'paths'));
        $this->assertTrue(Image::wasRecentlyRemoved());
    }

    // get result array manually
    public function test_result_array_can_be_get_manually()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->save(false, function ($image) {
                return [
                    'index' => $image->imagePath,
                    'imageDirectory' => $image->imageDirectory
                ];
            });

        $this->assertEquals(
            config('image.root_directory') . DIRECTORY_SEPARATOR . 'post' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $this->random(false),
            $image['imageDirectory']
        );

        foreach (config('image.' . config('image.use_size')) as $sizeName => $size) {
            $this->assertArrayHasKey($sizeName, $image['index']);
        }
    }

    // save and replace
    public function test_saves_and_replaces_image_with_replace()
    {
        $image = Image::raw($this->image)
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $image = Image::raw($this->image)
            ->in('')
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

    // save in storage
    public function test_invalid_disk_throws_exception()
    {
        $this->expectException(InvalidParameterException::class);

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage-public')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->expectExceptionMessage('Undefined disk storage-public');
    }

    public function test_disk_property_can_be_get_in_manually_way()
    {
        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('public')
            ->replace(false, function ($image) {
                return ['index' => $image->imagePath, 'disk' => $image->disk];
            });

        foreach ($images['index'] as $image) {
            $this->assertFileExists($this->disk_path($images['disk'], $image));
        }

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->replace(false, function ($image) {
                return ['index' => $image->imagePath, 'disk' => $image->disk];
            });

        foreach ($images['index'] as $image) {
            $this->assertFileExists($this->disk_path($images['disk'], $image));
        }

        config(['image.disks.storage-public' => storage_path('public')]);

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage-public')
            ->setRootDirectory('all')
            ->save(false, function ($image) {
                return ['index' => $image->imagePath, 'disk' => $image->disk];
            });

        foreach ($images['index'] as $image) {
            $this->assertFileExists($this->disk_path($images['disk'], $image));
        }


        $image = Image::raw($this->image)
            ->disk('public')
            ->in('')
            ->be('logo.png')
            ->save();

        $this->assertFileExists($this->disk_path($image['disk'], $image['index']));

        $image = Image::raw($this->image)
            ->disk('storage')
            ->in('')
            ->be('logo.png')
            ->save(false, function ($image) {
                return ['index' => $image->imagePath, 'disk' => $image->disk];
            });

        $this->assertFileExists($this->disk_path($image['disk'], $image['index']));

        $image = Image::raw($this->image)
            ->disk('storage-public')
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return ['index' => $image->imagePath, 'disk' => $image->disk];
            });

        $this->assertFileExists($this->disk_path($image['disk'], $image['index']));
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
            ->in('')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $image = Image::raw($this->image)
            ->disk('storage')
            ->in('')
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

    public function test_rm_works_with_disks()
    {
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
        $this->assertTrue(Image::wasRecentlyRemoved());

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
        $this->assertTrue(Image::wasRecentlyRemoved());

        $images = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        foreach ($images as $image) {
            $this->assertFileExists(storage_path('app/' . $image));
        }

        $this->assertTrue(Image::disk('storage')->rm(['index' => $images], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());

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

        $this->assertTrue(Image::disk('storage-public')->rm(['index' => $images], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::raw($this->image)
            ->disk('public')
            ->in('')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));

        $this->assertTrue(Image::disk('public')->rm(['index' => $image], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::raw($this->image)
            ->disk('public')
            ->in('')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(public_path($image));
        $this->assertTrue(Image::disk('public')->rm(['index' => $image], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::raw($this->image)
            ->disk('storage')
            ->in('')
            ->be('logo.png')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(storage_path('app/' . $image));
        $this->assertTrue(Image::disk('storage')->rm(['index' => $image], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());

        $image = Image::raw($this->image)
            ->disk('storage-public')
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        $this->assertFileExists(storage_path('public/' . $image));
        $this->assertTrue(Image::disk('storage-public')->rm(['index' => $image], 'index'));
        $this->assertTrue(Image::wasRecentlyRemoved());
    }

    public function test_rm_just_removes_directory_when_is_empty()
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
}
