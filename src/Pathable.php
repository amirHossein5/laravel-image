<?php

namespace AmirHossein5\LaravelImage;

use Facade\FlareClient\Http\Exceptions\MissingParameter;

trait Pathable
{
    /**
     * Path(s) of images.
     *
     * @var array|string
     */
    public $imagePath;

    /**
     * @var string
     */
    public $imageDirectory;

    /**
     * @var string
     */
    public $rootDirectory;

    /**
     * @var string
     */
    public $exclusiveDirectory;

    /**
     * Like .../2021/12/2/...
     *
     * @var string
     */
    public $archiveDirectories;

    /**
     * Directory that if there be more than one size, will locate all of those images in this directory.
     *
     * @var string
     */
    public $sizesDirectory;

    /**
     * @var string
     */
    public $imageName;

    /**
     * @var string
     */
    public $imageFormat;

    /**
     * Path to save images in, when using "raw" method.
     *
     * @var string
     */
    public $in;

    /**
     * Is creating image in normal mode or raw mode.
     *
     * @var bool
     */
    private $raw;

    /**
     * Sets the path that image is going to be saved. 
     * 
     * @param string $path
     * @return self
     */
    public function in(string $path): self
    {
        $this->in = $path;

        return $this;
    }

    /**
     * Sets exclusive directory.
     * 
     * @param string $directory
     * @return self
     */
    public function setExclusiveDirectory(string $directory): self
    {
        $this->exclusiveDirectory = $directory;

        return $this;
    }

    /**
     * Sets RootDirectory.
     * 
     * @param string $directory
     * @return self
     */
    public function setRootDirectory(string $directory): self
    {
        $this->rootDirectory = $directory;

        return $this;
    }

    /**
     * Sets ArchiveDirectories.
     * 
     * @param string $directory
     * @return self
     */
    public function setArchiveDirectories(string $directories): self
    {
        $this->archiveDirectories = $directories;

        return $this;
    }

    /**
     * Sets SizesDirectory.
     * 
     * @param string $directory
     * @return self
     */
    public function setSizesDirectory(string $directory): self
    {
        $this->sizesDirectory = $directory;

        return $this;
    }

    /**
     * Sets ImageName.
     * 
     * @param string $name
     * @return self
     */
    public function setImageName(string $name): self
    {
        $this->imageName = $name;

        return $this;
    }

    /**
     * Sets ImageFormat.
     * 
     * @param string $format
     * @return self
     */
    public function setImageFormat(string $format): self
    {
        $this->imageFormat = $format;

        return $this;
    }

    /**
     * Sets ImageName and ImageFormat.
     * 
     * @param string $nameWithFormat
     * @return self
     */
    public function be(string $nameWithFormat): self
    {
        $this->imageFormat = preg_replace('/[\w]+\./i', '', $nameWithFormat);

        preg_match_all('/[\w]+\./i', $nameWithFormat, $name);

        $this->imageName = trim(implode('', $name[0]), '.');

        return $this;
    }

    /**
     * Sets property defaults on make mode.
     * 
     * @return void
     */
    private function setDefaultsForImagePath(): void
    {
        $this->rootDirectory = config('image.root_directory');
        $this->archiveDirectories = $this->convertByDirectorySeparator(date('Y').'/'.date('m').'/'.date('d'));
        $this->sizesDirectory = $this->random(false);
        $this->imageFormat = $this->image->getClientOriginalExtension();
        $this->imageName = $this->random();
        $this->hiddenPath = config('image.disks.public');
        $this->disk = 'public';
    }

    /**
     * Sets property defaults on raw mode.
     * 
     * @return void
     */
    private function setRawDefaults(): void
    {
        $this->sizesDirectory = $this->random(false);
        $this->imageFormat = $this->image->getClientOriginalExtension();
        $this->imageName = $this->random();
        $this->hiddenPath = config('image.disks.public');
        $this->disk = 'public';
    }

    /**
     * Sets image path property.
     * 
     * @return void
     */
    private function setImagePath(): void
    {
        if ($this->raw) {
            if ($this->in === null) {
                throw new MissingParameter(
                    'When you use "raw" method pass "$in" variable with "->in(place/of/created/image)".'
                );
            }
            $this->prepareVariables();
            $this->setImagePathAndDirectoryBySizes($this->in);

            return;
        }

        if ($this->exclusiveDirectory === null) {
            throw new MissingParameter(
                'When you use "make" method pass "$setExclusiveDirectory" variable with "->setExclusiveDirectory(directory-name)".'
            );
        }

        $this->prepareVariables();
        $this->setImagePathAndDirectoryBySizes($this->getDirectoriesTemplate());
    }

    /**
     * Sets imagePath  and imageDirectory property by sizes.
     * 
     * @param string $template
     * @return void
     */
    private function setImagePathAndDirectoryBySizes(string $template = ''): void
    {
        if (!$this->sizes) {
            $imageDirectory = $template;
            $resultPath = $imageDirectory."/{$this->imageName}.{$this->imageFormat}";
        } elseif (count($this->sizes) === 1) {
            $sizeName = array_keys($this->sizes)[0];
            $imageDirectory = $template;
            $resultPath = $imageDirectory."/{$this->imageName}_{$sizeName}.{$this->imageFormat}";
        } elseif (count($this->sizes) > 1) {
            foreach ($this->sizes as $sizeName => $size) {
                $imageDirectory = $template."/{$this->sizesDirectory}";
                $resultPath[$sizeName] =
                    $imageDirectory."/{$this->imageName}_{$sizeName}.{$this->imageFormat}";
            }
        }

        $this->imagePath = $this->convertByDirectorySeparator($resultPath);
        $this->imageDirectory = $this->convertByDirectorySeparator($imageDirectory);
    }

    /**
     * Gets result array structure.
     * 
     * @return array
     */
    private function getArrayStructure(): array
    {
        $resultArrayStructure = ['index', 'imageDirectory', 'default_size', 'disk'];

        return array_flip($resultArrayStructure);
    }

    /**
     * Gets result array.
     * 
     * @return array
     */
    private function getResultArrayStructure(): array
    {
        $arrayStructure = $this->getArrayStructure();
        $arrayStructure['index'] = $this->imagePath;
        $arrayStructure['imageDirectory'] = $this->imageDirectory;
        $arrayStructure['disk'] = $this->disk;

        if (count($this->sizes ?? []) > 1 and $this->defaultSize) {
            $arrayStructure['default_size'] = $this->defaultSize;
        } else {
            unset($arrayStructure['default_size']);
        }

        return $arrayStructure;
    }

    /**
     * Gets directories template.
     * 
     * @return string
     */
    private function getDirectoriesTemplate(): string
    {
        return "{$this->rootDirectory}/{$this->exclusiveDirectory}/{$this->archiveDirectories}";
    }

    /**
     * fixed directory separators.
     * 
     * @return string|array
     */
    private function convertByDirectorySeparator($path)
    {
        if (is_string($path)) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

            return str_replace('\\', DIRECTORY_SEPARATOR, $path);
        }
        foreach ($path as $key => $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $resultPath[$key] = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        }

        return $resultPath;
    }

    /**
     * makes random number.
     * 
     * @param bool $hasSuffix
     * @param string $suffix
     * @return string
     */
    private function random(bool $hasSuffix = true, string $suffix = null): string
    {
        if ($hasSuffix) {
            $suffix = $suffix ? '_'.$suffix : '_'.rand(100, 999);
        }

        return time().$suffix;
    }

    /**
     * cuts directory separators from related path properties.
     * 
     * @return void
     */
    private function prepareVariables(): void
    {
        if ($this->raw) {
            $this->sizesDirectory = trim($this->sizesDirectory, '/\\');
            $this->in = trim($this->in, '/\\');

            return;
        }
        $this->rootDirectory = trim($this->rootDirectory, '/\\');
        $this->exclusiveDirectory = trim($this->exclusiveDirectory, '/\\');
        $this->archiveDirectories = trim($this->archiveDirectories, '/\\');
        $this->sizesDirectory = trim($this->sizesDirectory, '/\\');
    }
}
