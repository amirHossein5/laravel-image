<?php

namespace AmirHossein5\LaravelImage\Tests;

use AmirHossein5\LaravelImage\ImageServiceProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Intervention\Image\Facades\Image as Intervention;

class TestCase extends TestbenchTestCase
{
    protected UploadedFile $image;

    protected function disk_path(string $disk, string $path): string
    {
        return config('image.disks.'.$disk).DIRECTORY_SEPARATOR.$path;
    }

    protected function getPackageProviders($app)
    {
        return [
            ImageServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->image = UploadedFile::fake()->image('test.png');
    }

    protected function tearDown(): void
    {
        if (file_exists(public_path('images'))) {
            File::deleteDirectory(public_path('images'));
            $this->assertFalse(file_exists(public_path('images')));
        }

        parent::tearDown();
    }

    protected function random(bool $hasSuffix = true, string $suffix = null): string
    {
        if ($hasSuffix) {
            $suffix = $suffix ? '_'.$suffix : '_'.rand(100, 999);
        }

        return time().$suffix;
    }

    public function interventionCircle(): \Intervention\Image\Image
    {
        // create empty canvas with background color
        $img = Intervention::canvas(300, 200, '#ddd');

        // draw a filled blue circle
        $img->circle(100, 50, 50, function ($draw) {
            $draw->background('#0000ff');
        });

        // draw a filled blue circle with red border
        $img->circle(10, 100, 100, function ($draw) {
            $draw->background('#0000ff');
            $draw->border(1, '#f00');
        });

        // draw an empty circle with 5px border
        $img->circle(70, 150, 100, function ($draw) {
            $draw->border(5, '000000');
        });

        return $img;
    }

    /**
     * Re modifies string by directory separator.
     * 
     * @param string $string
     * 
     * @return string
     */
    public function directorySeparator(string $string): string
    {
        $string = str_replace('/', DIRECTORY_SEPARATOR, $string);

        return str_replace('\\', DIRECTORY_SEPARATOR, $string);
    }
}
