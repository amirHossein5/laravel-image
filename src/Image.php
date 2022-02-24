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

    /**
     * @var bool
     */
    private bool $wasRecentlyRemoved = false;

    /**
     * Determines that image wasRecentlyRemoved.
     *
     * @return bool
     */
    public function wasRecentlyRemoved(): bool
    {
        return $this->wasRecentlyRemoved;
    }

    /**
     * Determines the disk.
     *
     * @param string $disk
     *
     * @return self
     */
    public function disk(string $disk): self
    {
        $this->hiddenPath = config("image.disks.{$disk}");

        if (!$this->hiddenPath) {
            throw new InvalidParameterException('Undefined disk '.$disk);
        }

        $this->disk = $disk;

        return $this;
    }

    /**
     * Determines way of saving.
     *
     * @param \Illuminate\Http\UploadedFile $image
     *
     * @return self
     */
    public function raw(UploadedFile $image): self
    {
        $this->raw = true;
        $this->image = $image;
        $this->setRawDefaults();

        return $this;
    }

    /**
     * Determines way of saving.
     *
     * @param \Illuminate\Http\UploadedFile $image
     *
     * @return self
     */
    public function make(UploadedFile $image): self
    {
        $this->image = $image;
        $this->setDefaultsForImagePath();
        $this->setDefaultsForImageSizes();

        return $this;
    }

    /**
     * Determines is on testing env.
     *
     * @return void
     */
    public function fake(): void
    {
        $this->testMode = true;
    }

    /**
     * Saves image.
     *
     * @param bool          $upsize
     * @param \Closure|null $closure
     *
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
     * Save and replaces image if exists with same name.
     *
     * @param bool          $upsize
     * @param \Closure|null $closure
     *
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

    /**
     * Makes directory if not exists.
     *
     * @param string $path
     *
     * @return bool
     */
    private function mkdirIfNotExists(string $path): bool
    {
        if (!file_exists($this->disk_path($path))) {
            return mkdir($this->disk_path($path), 0777, true);
        }

        return true;
    }

    /**
     * Sets disk path of image.
     *
     * @param string $path
     *
     * @return string
     */
    private function disk_path(string $path): string
    {
        return $this->hiddenPath.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Resets properties of class.
     *
     * @return void
     */
    private function reset(): void
    {
        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if ($var !== 'testMode' and $var !== 'wasRecentlyRemoved') {
                $this->$var = $def_val;
            }
        }
    }
}
