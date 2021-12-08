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
    private $image;

    /**
     * @var bool
     */
    private $testMode = false;

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

    public function save($upsize = false)
    {
        $this->setImagePath();

        if ($this->testMode) {
            $resultArray = $this->getResultArrayStructure();
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

        $resultArray = $this->getResultArrayStructure();
        $this->reset();
        return $resultArray;
    }

    public function rm(array $image): bool
    {
        if (is_string($image['index'])) {
            return unlink(public_path($image['index']));
        } else if (is_array($image['index'])) {
            return File::deleteDirectory(public_path($image['imageDirectory']));
        }
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
            if ($var !== 'testMode') {
                $this->$var = $def_val;
            }
        }
    }
}
