<?php

namespace AmirHossein5\LaravelImage\Tests;

use AmirHossein5\LaravelImage\ImageServiceProvider;
use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Illuminate\Support\Facades\File;

class TestCase extends TestbenchTestCase
{
    protected UploadedFile $image;

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
            $suffix = $suffix ? '_' . $suffix : '_' . rand(100, 999);
        }
        return time() . $suffix;
    }
}