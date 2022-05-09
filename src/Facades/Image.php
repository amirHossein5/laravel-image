<?php

namespace AmirHossein5\LaravelImage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool wasRecentlyRemoved()
 * @method static self disk(string $disk)
 * @method static self raw(\Illuminate\Http\UploadedFile|\Intervention\Image\Image $image)
 * @method static self make(\Illuminate\Http\UploadedFile|\Intervention\Image\Image $image)
 * @method static void fake()
 * @method static mixed save(bool $upsize = false, ?\Closure $closure = null)
 * @method static mixed replace(bool $upsize = false, ?\Closure $closure = null)
 * @method static self in(string $path)
 * @method static self setExclusiveDirectory(string $directory)
 * @method static self setRootDirectory(string $directory)
 * @method static self setArchiveDirectories(string $directories)
 * @method static self setSizesDirectory(string $directory)
 * @method static self setImageName(string $name)
 * @method static self setImageFormat(string $format)
 * @method static self be(string $nameWithFormat)
 * @method static bool rm(string|array $image, string|integer|null $removeIndex = null)
 * @method static array|bool setDefaultSizeFor(array $image, string $sizeName, ?string $pathKeys = 'index')
 * @method static self resize(int $width, int $height, string $as = null)
 * @method static self resizeBy(array $sizes)
 * @method static self alsoResize(int $width, int $height, string $as = null)
 * @method static self autoResize()
 * @method static void transaction(\Closure $callback, int $maxAttempts = 1)
 * @method static void rollBack()
 * @method static void commit()
 * @method static void beginTransaction()
 * @method static self quality(int $quality)
 *
 **/
class Image extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravelImage';
    }
}
