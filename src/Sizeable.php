<?php

namespace AmirHossein5\LaravelImage;

trait Sizeable
{
    /**
     * @var array|null
     */
    public $sizes;

    /**
     * @var string|null
     */
    public $defaultSize = null;

    /**
     * Sets default size for given image array.
     *
     * @param array       $image
     * @param string      $sizeName
     * @param string|null $pathKeys
     *
     * @return array|bool
     */
    public function setDefaultSizeFor(array $image, string $sizeName, ?string $pathKeys = 'index')
    {
        if (array_key_exists($sizeName, $image[$pathKeys])) {
            $image['default_size'] = $sizeName;

            return $image;
        }

        return false;
    }

    /**
     * Removes previous defined sizes, and adds a size.
     *
     * @param int    $width
     * @param int    $height
     * @param string $as
     *
     * @return self
     */
    public function resize(int $width, int $height, string $as = null): self
    {
        $array = ['width' => $width, 'height' => $height];
        $this->sizes = null;

        $as ? $this->sizes[$as] = $array
            : $this->sizes[] = $array;

        return $this;
    }

    /**
     * Resize by intended array.
     *
     * @param array $sizes
     *
     * @return self
     */
    public function resizeBy(array $sizes): self
    {
        foreach ($sizes as $key => $size) {
            $this->sizes[$key] = ['width' => (int) $size['width'], 'height' => (int) $size['height']];
        }

        return $this;
    }

    /**
     * Adds a size.
     *
     * @param int    $width
     * @param int    $height
     * @param string $as
     *
     * @return self
     */
    public function alsoResize(int $width, int $height, string $as = null): self
    {
        $array = ['width' => $width, 'height' => $height];

        $as ? $this->sizes[$as] = $array
            : $this->sizes[] = $array;

        return $this;
    }

    /**
     * Removes previous defined sizes.
     *
     * @return self
     */
    public function autoResize(): self
    {
        $this->sizes = null;

        return $this;
    }

    /**
     * Sets property related to size by config.
     *
     * @return void
     */
    private function setDefaultsForImageSizes(): void
    {
        $this->resizeBy(config('image.'.config('image.use_size')));

        if (config('image.default_size') === null) {
            $this->defaultSize = null;
        } elseif (!array_key_exists(config('image.default_size'), $this->sizes)) {
            throw new \ErrorException(
                'Undefined size name "'.config('image.default_size').'", that you defined in your config file.'
            );
        }

        $this->defaultSize = config('image.default_size');
    }
}
