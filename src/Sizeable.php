<?php

namespace AmirHossein5\LaravelImage;

trait Sizeable
{
    /**
     * @var array|null
     */
    private $sizes;

    /**
     * @var string|null
     */
    private $defaultSize = null;

    /**
     * @return array|bool
     */
    public function setDefaultSizeFor(array $image, string $sizeName)
    {
        if (array_key_exists($sizeName, $image['index'])) {
            $image['default_size'] = $sizeName;
            return $image;
        }

        return false;
    }

    public function resize(int $width, int $height, string $as = null): self
    {
        $array = ['width' => $width, 'height' => $height];
        $this->sizes = null;

        $as ? $this->sizes[$as] = $array
            : $this->sizes[] = $array;

        return $this;
    }

    public function resizeBy(array $sizes): self
    {
        foreach ($sizes as $key => $size) {
            $this->sizes[$key] = ['width' => (int) $size['width'], 'height' => (int) $size['height']];
        }
        return $this;
    }

    public function alsoResize(int $width, int $height, string $as = null): self
    {
        $array = ['width' => $width, 'height' => $height];

        $as ? $this->sizes[$as] = $array
            : $this->sizes[] = $array;

        return $this;
    }

    public function autoResize(): self
    {
        $this->sizes = null;
        return $this;
    }

    private function setDefaultsForImageSizes(): void
    {
        $this->resizeBy(config('image.' . config('image.use_size')));

        if (config('image.default_size') === null) {
            $this->defaultSize = null;
        } else if (!array_key_exists(config('image.default_size'), $this->sizes)) {
            throw new \ErrorException(
                'Undefined size name "' . config('image.default_size') . '", that you defined in your config file.'
            );
        }

        $this->defaultSize = config('image.default_size');
    }
}
