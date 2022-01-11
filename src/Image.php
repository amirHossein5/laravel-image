<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image as Intervention;

class Image
{
    use Sizeable, Pathable;

    /**
     * @var Illuminate\Http\UploadedFile
     */
    public $image;

    /**
     * @var bool
     */
    private $testMode = false;

    private bool $wasRecentlyRemoved = false;

    public function wasRecentlyRemoved(): bool
    {
        return $this->wasRecentlyRemoved;
    }

    public function raw(UploadedFile $image): self
    {
        $this->raw = true;
        $this->image = $image;
        $this->setRawDefaults();

        return $this;
    }

    public function make(UploadedFile $image): self
    {
        $this->image = $image;
        $this->setDefaultsForImagePath();
        $this->setDefaultsForImageSizes();

        return $this;
    }

    public function fake(): void
    {
        $this->testMode = true;
    }

    /**
     * @return mixed
     */
    public function save($upsize = false, ?\Closure $closure = null)
    {
        $this->setImagePath();

        if ($this->testMode) {
            if ($closure instanceof \Closure) {
                $resultArray = $closure($this);
            } else {
                $resultArray = $this->getResultArrayStructure();
            }
            $this->reset();
            return $resultArray;
        }

        if (!$this->mkdirIfNotExists($this->imageDirectory)) {
            return false;
        }

        $image = Intervention::make($this->image);

        if (!$this->sizes) {
            $image->save(public_path($this->imagePath));
        } else if (count($this->sizes) === 1) {
            foreach ($this->sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save(public_path($this->imagePath));
            }
        } else if (count($this->sizes) > 1) {
            foreach ($this->sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save(public_path($this->imagePath[$key]));
            }
        }

        if ($closure instanceof \Closure) {
            $resultArray = $closure($this);
        } else {
            $resultArray = $this->getResultArrayStructure();
        }
        $this->reset();
        return $resultArray;
    }

    /**
     * @return mixed
     */
    public function replace($upsize = false, ?\Closure $closure = null)
    {
        $this->setImagePath();

        if (!$this->removeIfExists($this->imagePath)) {
            return false;
        }

        return $this->save($upsize, $closure);
    }

    /**
     * @var string|array $image
     * @var string|integer|null $removeIndex
     */
    public function rm($image, $removeIndex = null): bool
    {
        if (!$removeIndex) {
            if (is_string($image)) {
                return $this->wasRecentlyRemoved = unlink(public_path($image));
            } else if (is_string($image['index'])) {
                return $this->wasRecentlyRemoved = unlink(public_path($image['index']));
            } else if (is_array($image['index'])) {
                return $this->wasRecentlyRemoved = File::deleteDirectory(public_path($image['imageDirectory']));
            }
        }

        if (is_string($image[$removeIndex])) {
            return $this->wasRecentlyRemoved = unlink(public_path($image[$removeIndex]));
        } else if (is_array($image[$removeIndex])) {

            if (isset($image['imageDirectory'])) {
                return $this->wasRecentlyRemoved = File::deleteDirectory(public_path($image['imageDirectory']));
            }

            $paths = array_values($image[$removeIndex]);

            for ($i = 0; $i < count($paths); $i++) {
                if ($i === 0) {
                    $this->wasRecentlyRemoved = unlink(public_path($paths[$i]));
                } else {
                    $this->wasRecentlyRemoved =
                        unlink(public_path($paths[$i])) and $this->wasRecentlyRemoved;
                }
            }

            return $this->wasRecentlyRemoved;
        }

        return $this->wasRecentlyRemoved = false;
    }

    /**
     * rm method returns false if not exists but this if exists removes.
     * @var string|array $path
     */
    private function removeIfExists($path): bool
    {
        if (is_string($path)) {
            if (file_exists(public_path($path))) {
                $this->rm($path);

                if (!$this->wasRecentlyRemoved) {
                    return false;
                }
            }
        } else if (is_array($path)) {
            foreach ($path as $item) {
                if (file_exists(public_path($item))) {
                    $this->rm($item);

                    if (!$this->wasRecentlyRemoved) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function mkdirIfNotExists(string $path): bool
    {
        if (!file_exists(public_path($path))) {
            return mkdir(public_path($path), 0777, true);
        }

        return true;
    }

    private function reset(): void
    {
        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if ($var !== 'testMode' and $var !== 'wasRecentlyRemoved') {
                $this->$var = $def_val;
            }
        }
    }
}
