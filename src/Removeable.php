<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Support\Facades\File;

trait Removeable
{
    /**
     * @var string|array $image
     * @var string|integer|null $removeIndex
     */
    public function rm($image, $removeIndex = null): bool
    {
        $this->setDisk();
        
        if (!$removeIndex) {
            if (is_string($image)) {
                return $this->wasRecentlyRemoved = unlink($this->disk_path($image));
            } else if (is_string($image['index'])) {
                return $this->wasRecentlyRemoved = unlink($this->disk_path($image['index']));
            } else if (is_array($image['index'])) {
                return $this->wasRecentlyRemoved = File::deleteDirectory($this->disk_path($image['imageDirectory']));
            }
        }

        if (is_string($image[$removeIndex])) {
            return $this->wasRecentlyRemoved = unlink($this->disk_path($image[$removeIndex]));
        } else if (is_array($image[$removeIndex])) {

            if (isset($image['imageDirectory'])) {
                return $this->wasRecentlyRemoved = File::deleteDirectory($this->disk_path($image['imageDirectory']));
            }

            $paths = array_values($image[$removeIndex]);

            for ($i = 0; $i < count($paths); $i++) {
                if ($i === 0) {
                    $this->wasRecentlyRemoved = unlink($this->disk_path($paths[$i]));
                } else {
                    $this->wasRecentlyRemoved =
                        unlink($this->disk_path($paths[$i])) and $this->wasRecentlyRemoved;
                }
            }

            return $this->wasRecentlyRemoved;
        }

        return $this->wasRecentlyRemoved = false;
    }

    /**
     * rm method returns false if not exists but this if exists removes.
     * 
     * @var string|array $path
     */
    private function removeIfExists($path): bool
    {
        if (is_string($path)) {
            if (file_exists($this->disk_path($path))) {
                $this->rm($path);

                if (!$this->wasRecentlyRemoved) {
                    return false;
                }
            }
        } else if (is_array($path)) {
            foreach ($path as $item) {
                if (file_exists($this->disk_path($item))) {
                    $this->rm($item);

                    if (!$this->wasRecentlyRemoved) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
    
    private function setDisk(): void
    {
        if (! $this->disk) {
            $this->disk('public');
        }
    }
}
