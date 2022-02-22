<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as Intervention;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class Image
{
    use Sizeable;
    use Pathable;
    use Removeable;

    /**
     * The hidden part of path which won't appear on result.
     *
     * @var string|null
     */
    private $hiddenPath = null;

    /**
     * @var string|null
     */
    public $disk = null;

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

    public function disk(string $disk): self
    {
        $this->hiddenPath = config("image.disks.{$disk}");

        if (!$this->hiddenPath) {
            throw new InvalidParameterException('Undefined disk '.$disk);
        }

        $this->disk = $disk;

        return $this;
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
    public function save(bool $upsize = false, ?\Closure $closure = null)
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
            $image->save($this->disk_path($this->imagePath));
        } elseif (count($this->sizes) === 1) {
            foreach ($this->sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save($this->disk_path($this->imagePath));
            }
        } elseif (count($this->sizes) > 1) {
            foreach ($this->sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save($this->disk_path($this->imagePath[$key]));
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
    public function replace(bool $upsize = false, ?\Closure $closure = null)
    {
        $this->setImagePath();

        if (!$this->removeIfExists($this->imagePath)) {
            return false;
        }

        return $this->save($upsize, $closure);
    }

    private function mkdirIfNotExists(string $path): bool
    {
        if (!file_exists($this->disk_path($path))) {
            return mkdir($this->disk_path($path), 0777, true);
        }

        return true;
    }

    private function disk_path(string $path): string
    {
        return $this->hiddenPath.DIRECTORY_SEPARATOR.$path;
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
