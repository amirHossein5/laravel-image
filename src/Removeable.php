<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Filesystem\Filesystem;

trait Removeable
{
    /**
     * Removes the image.
     *
     * @param string|array    $image
     * @param string|int|null $removeIndex
     *
     * @return bool
     */
    public function rm($image, $removeIndex = null): bool
    {
        $this->setDisk();

        if (!$removeIndex) {
            if (is_string($image)) {
                $this->unlinkImagePath($image);
                return $this->wasRecentlyRemoved;
            } elseif (is_string($image['index'])) {
                $this->unlinkImagePath($image['index']);
                return $this->wasRecentlyRemoved;
            } elseif (is_array($image['index'])) {
                $this->unsetImagePaths(array_values($image['index']));

                $this->removeDirectoryIfEmpty($this->disk_path($image['imageDirectory']));

                return $this->wasRecentlyRemoved;
            }
        }

        if (is_string($image[$removeIndex])) {
            $this->unlinkImagePath($image[$removeIndex]);
            return $this->wasRecentlyRemoved;
        } elseif (is_array($image[$removeIndex])) {
            $this->unsetImagePaths(array_values($image[$removeIndex]));

            if (isset($image['imageDirectory'])) {
                $this->removeDirectoryIfEmpty($this->disk_path($image['imageDirectory']));
            }

            return $this->wasRecentlyRemoved;
        }

        return $this->wasRecentlyRemoved = false;
    }

    /**
     * remove the given image paths.
     *
     * @param array $paths
     * @return void
     */
    private function unsetImagePaths(array $paths): void
    {
        try {
            for ($i = 0; $i < count($paths); $i++) {
                if ($i === 0) {
                    $this->wasRecentlyRemoved = unlink($this->disk_path($paths[$i]));
                } else {
                    $this->wasRecentlyRemoved =
                        unlink($this->disk_path($paths[$i])) and $this->wasRecentlyRemoved;
                }
            }
        } catch (\Exception $th) {
            $this->wasRecentlyRemoved = false;
        }
    }

    /**
     * remove the given image path.
     *
     * @param string $path
     * @return void
     */
    private function unlinkImagePath(string $path): void
    {
        try {
            $this->wasRecentlyRemoved = unlink($this->disk_path($path));
        } catch (\Exception $th) {
            $this->wasRecentlyRemoved = false;
        }
    }

    /**
     * removes given directory if it's empty.
     *
     * @param string $dir
     *
     * @return void
     */
    private function removeDirectoryIfEmpty(string $dir): void
    {
        $FileSystem = new Filesystem();

        // Check if the directory exists.
        if ($FileSystem->exists($dir)) {

            // Get all files in this directory.
            $files = $FileSystem->files($dir);

            // Check if directory is empty.
            if (empty($files)) {
                $FileSystem->deleteDirectory($dir);
            }
        }
    }

    /**
     * rm method returns false if not exists but this if exists removes.
     *
     * @param string|array $path
     *
     * @return bool
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
        } elseif (is_array($path)) {
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

    /**
     * Sets disk for removing image.
     *
     * @return void
     */
    private function setDisk(): void
    {
        if (!$this->disk) {
            $this->disk('public');
        }
    }
}
