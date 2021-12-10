<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use Facade\FlareClient\Http\Exceptions\MissingParameter;

class ImageTest extends TestCase
{
    public function test_when_fake_method_uses_wont_create_image()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPath('post')
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

    public function test_raw_method_needs_inPath_variable()
    {
        Image::fake();

        $this->expectException(MissingParameter::class);

        Image::raw($this->image)
            ->save();
    }

    public function test_raw_inPublicPath_method_creates_in_public_directory()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPublicPath()
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
            config('image.root_directory') .DIRECTORY_SEPARATOR. 'post' . DIRECTORY_SEPARATOR . date('Y') .DIRECTORY_SEPARATOR. date('m') .DIRECTORY_SEPARATOR. date('d') .DIRECTORY_SEPARATOR. $this->random(false),
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
            ->inPath('test')
            ->setImageName('name')
            ->save();

        $this->assertEquals('test'.DIRECTORY_SEPARATOR.'name.' . $this->image->getClientOriginalExtension(), $imageResult['index']);
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

    /**
     * Tests for directory and size setters.
     */
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

        $this->assertEquals('root'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.'size'.DIRECTORY_SEPARATOR.'name_large.png', $image['index']['large']);
        $this->assertEquals('root'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.'size', $image['imageDirectory']);
    }

    public function test_raw_method_and_directory_setters_set_directories_correctly()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals('post'.DIRECTORY_SEPARATOR.'name.png', $image['index']);
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

        $this->assertStringContainsString(DIRECTORY_SEPARATOR. $this->random(false), $image['imageDirectory']);
    }

    public function test_with_raw_method_and_default_sizes_sets_correct_imagePath_and_imageDirectory()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->save();

        $this->assertEquals('post'.DIRECTORY_SEPARATOR.'name.png', $image['index']);
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

        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch', $image['imageDirectory']);
        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch'.DIRECTORY_SEPARATOR.'name.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch', $image['imageDirectory']);
        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch'.DIRECTORY_SEPARATOR.'name_0.png', $image['index']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->setArchiveDirectories('arch')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch', $image['imageDirectory']);
        $this->assertEquals('images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch'.DIRECTORY_SEPARATOR.'name_large.png', $image['index']);
    }

    public function test_with_raw_method_without_or_one_size_returns_correct_imageDirectory_and_imagePath()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20)
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals('post'.DIRECTORY_SEPARATOR.'name_0.png', $image['index']);

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize('50', 20, 'large')
            ->save();

        $this->assertEquals('post', $image['imageDirectory']);
        $this->assertEquals('post'.DIRECTORY_SEPARATOR.'name_large.png', $image['index']);
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

        $expectedPath = 'images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch'.DIRECTORY_SEPARATOR . $this->random(false);
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR. "name_{$sizeName}.png", $path);
        }
    }

    public function test_raw_method_with_more_than_one_given_size_returns_correct_path()
    {
        Image::fake();

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(50, 20)
            ->alsoResize(100, 50, 'large')
            ->save();

        $expectedPath = 'post' .DIRECTORY_SEPARATOR. $this->random(false);
        $this->assertEquals($expectedPath, $image['imageDirectory']);
        $this->assertTrue(array_key_exists(0, $image['index']));
        $this->assertTrue(array_key_exists('large', $image['index']));
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR."name_0.png", $image['index'][0]);
        $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR."name_large.png", $image['index']['large']);

        foreach ($image['index'] as $sizeName => $path) {
            $this->assertEquals($expectedPath . DIRECTORY_SEPARATOR."name_{$sizeName}.png", $path);
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
            ->inPath('post')
            ->save();

        $this->assertTrue(array_key_exists('index', $image));
        $this->assertTrue(array_key_exists('imageDirectory', $image));
        $this->assertTrue(!array_key_exists('default_size', $image));

        $image = Image::raw($this->image)
            ->inPath('post')
            ->resize(10, 10, 'small')
            ->defaultSize('small')
            ->save();

        $this->assertTrue(!array_key_exists('default_size', $image));

        $image = Image::raw($this->image)
            ->inPath('post')
            ->resize(10, 10, 'small')
            ->alsoResize(10, 10, 'small')
            ->defaultSize('small')
            ->save();

        $this->assertTrue(!array_key_exists('default_size', $image));
    }

    /**
     * Test size setters.
     */
    public function test_auto_
        
        
        
        
        
        _sizes()
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

        $this->assertEquals($image['index'], 'images'.DIRECTORY_SEPARATOR.'post'.DIRECTORY_SEPARATOR.'arch'.DIRECTORY_SEPARATOR.'name_large.png');

        $image = Image::raw($this->image)
            ->inPath('post')
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::raw($this->image)
            ->inPath('post')
            ->resize(10, 10)
            ->resize(10, 10)
            ->resize(10, 10)
            ->save();

        $this->assertTrue(is_string($image['index']));

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageName('name')
            ->setImageFormat('png')
            ->resize(10, 10, 'large')
            ->save();

        $this->assertStringContainsString($image['index'], 'post'.DIRECTORY_SEPARATOR.'name_large.png');
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
            ->inPath('post')
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
            ->inPath('post')
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
            ->inPath('post')
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

    public function test_wrong_setted_default_size_throws_exception()
    {
        Image::fake();

        $this->expectException(\ErrorException::class);

        Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->autoResize()
            ->resizeBy([
                'large' => [
                    'width' => '200',
                    'height' => 300
                ]
            ])
            ->defaultSize('small')
            ->save();
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

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->resizeBy([
                'large' => [
                    'width' => '200',
                    'height' => 300
                ]
            ])
            ->defaultSize('large')
            ->save();

        $this->assertEquals('large', $image['default_size']);

        $image = Image::raw($this->image)
            ->inPath('post')
            ->setImageFormat('png')
            ->resizeBy([
                'large' => [
                    'width' => '200',
                    'height' => 300
                ]
            ])
            ->defaultSize('large')
            ->save();

        $this->assertArrayNotHasKey('default_size', $image);

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
            ->defaultSize('large')
            ->save();

        $this->assertEquals('large', $image['default_size']);

        $image = Image::raw($this->image)
            ->inPath('post')
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
            ->defaultSize('large')
            ->save();

        $this->assertEquals('large', $image['default_size']);

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->defaultSize('large')
            ->save();

        $this->assertEquals('large', $image['default_size']);

        $image = Image::raw($this->image)
            ->inPath('post')
            ->save();

        $this->assertArrayNotHasKey('default_size', $image);
    }

    public function test_when_there_is_not_any_specified_size_can_not_use_defaultSize()
    {
        $this->expectException(\ErrorException::class);

        Image::raw($this->image)
            ->inPath('post')
            ->defaultSize('large')
            ->save();
    }

    public function test_changing_default_size_after_created_image()
    {
        Image::fake();

        $image = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->defaultSize('large')
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

        $this->assertEquals('small', $image['default_size']);

        $image = Image::raw($this->image)
            ->inPath('post')
            ->resizeBy(config('image.imageSizes'))
            ->defaultSize('large')
            ->save();

        $image = Image::setDefaultSizeFor($image, 'small');

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

        $this->assertFalse(file_exists(public_path($image['imageDirectory'])));

        foreach ($image['index'] as $path) {
            $this->assertFalse(file_exists(public_path($path)));
        }

        $image = Image::raw($this->image)
            ->inPublicPath()
            ->save();

        $this->assertFileExists(public_path($image['index']));

        Image::rm($image);

        $this->assertFalse(file_exists(public_path($image['index'])));
    }
}
