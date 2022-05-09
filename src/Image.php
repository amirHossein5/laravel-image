<?php

namespace AmirHossein5\LaravelImage;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as Intervention;
use Intervention\Image\Image as InterventionImage;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class Image
{
    use Sizeable;
    use Pathable;
    use Removeable;
    use Transaction;

    /**
     * Image quality.
     *
     * @var int
     */
    public int $quality = 90;

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
     * @var Intervention\Image\Image
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
     * On Transactioning it shows the image is going to be replave.
     *
     * @var bool
     */
    private bool $willBeReplace = false;

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
     * Determines way of saving. $image can be UploadedFile laravel object or intervention.
     *
     * @param \Illuminate\Http\UploadedFile|Intervention\Image\Image $image
     *
     * @return self
     */
    public function raw($image): self
    {
        $this->raw = true;
        $this->image = Intervention::make($image);
        $this->setRawDefaults();

        return $this;
    }

    /**
     * Determines way of saving. $image can be UploadedFile laravel object or intervention.
     *
     * @param \Illuminate\Http\UploadedFile|Intervention\Image\Image $image
     *
     * @return self
     */
    public function make($image): self
    {
        $this->image = Intervention::make($image);
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
     * Sets image quality.
     *
     * @param int $quality
     *
     * @return self
     */
    public function quality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
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

        // saving image
        if ($this->transactioning) {
            $this->transactionBag[] = [
                'image'          => $this->image,
                'sizes'          => $this->sizes,
                'imagePath'      => $this->imagePath,
                'imageDirectory' => $this->imageDirectory,
                'upsize'         => $upsize,
                'disk'           => $this->disk,
                'willBeReplace'  => $this->willBeReplace,
                'quality'        => $this->quality,
            ];
        } else {
            if (!$this->mkdirIfNotExists($this->imageDirectory)) {
                return false;
            }

            $this->store(
                $this->image,
                $this->sizes,
                $this->imagePath,
                $upsize,
                $this->quality
            );
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

        if ($this->transactioning) {
            $this->willBeReplace = true;
        } else {
            if (!$this->removeIfExists($this->imagePath)) {
                return false;
            }
        }

        return $this->save($upsize, $closure);
    }

    /**
     * Stores image(s).
     *
     * @param Intervention\Image\Image $image
     * @param null|array               $sizes
     * @param array|string             $imagePath
     * @param bool                     $upsize
     * @param int                      $quality
     *
     * @return void
     */
    private function store(InterventionImage $image, ?array $sizes, $imagePath, bool $upsize, int $quality): void
    {
        if (!$sizes) {
            $image->save($this->disk_path($imagePath), $quality);
        } elseif (count($sizes) === 1) {
            foreach ($sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save($this->disk_path($imagePath), $quality);
            }
        } elseif (count($sizes) > 1) {
            foreach ($sizes as $key => $size) {
                $image->fit($size['width'], $size['height'], function ($constraint) use ($upsize) {
                    !$upsize ?: $constraint->upsize();
                });
                $image->save($this->disk_path($imagePath[$key]), $quality);
            }
        }
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
        $whitelist = [
            'testMode',
            'wasRecentlyRemoved',
            'transactioning',
            'transactionBag',
        ];

        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if (!in_array($var, $whitelist)) {
                $this->$var = $def_val;
            }
        }
    }
}
