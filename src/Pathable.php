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
    public $inPath;

    /**
     * Is creating image in normal mode or raw mode.
     *
     * @var bool
     */
    private $raw;

    public function inPath(string $path): self
    {
        $this->inPath = $path;
        return $this;
    }

    public function inPublicPath(): self
    {
        $this->inPath = '';
        return $this;
    }

    public function setExclusiveDirectory(string $directory): self
    {
        $this->exclusiveDirectory = $directory;
        return $this;
    }

    public function setRootDirectory(string $directory): self
    {
        $this->rootDirectory = $directory;
        return $this;
    }

    public function setArchiveDirectories(string $directories): self
    {
        $this->archiveDirectories = $directories;
        return $this;
    }

    public function setSizesDirectory(string $directory): self
    {
        $this->sizesDirectory = $directory;
        return $this;
    }

    public function setImageName(string $name): self
    {
        $this->imageName = $name;
        return $this;
    }

    public function setImageFormat(string $format): self
    {
        $this->imageFormat = $format;
        return $this;
    }
    
    public function be(string $nameWithFormat): self
    {
        $this->imageFormat = preg_replace('/[\w]+\./i', '', $nameWithFormat);
        
        preg_match_all('/[\w]+\./i', $nameWithFormat, $name);

        $this->imageName = trim(implode('', $name[0]), '.');
        
        return $this;
    }

    private function setDefaultsForImagePath(): void
    {
        $this->rootDirectory = config('image.root_directory');
        $this->archiveDirectories = $this->convertByDirectorySeparator(date('Y') . '/' . date('m') . '/' . date('d'));
        $this->sizesDirectory = $this->random(false);
        $this->imageFormat = $this->image->getClientOriginalExtension();
        $this->imageName = $this->random();
    }

    private function setRawDefaults(): void
    {
        $this->sizesDirectory = $this->random(false);
        $this->imageFormat = $this->image->getClientOriginalExtension();
        $this->imageName = $this->random();
    }

    private function setImagePath(): void
    {
        if ($this->raw) {
            if ($this->inPath === null) {
                throw new MissingParameter(
                    'When you use "raw" method pass "$inPath" variable with "->inPath(place/of/created/image)".'
                );
            }
            $this->prepareVariables();
            $this->setImagePathAndDirectoryBySizes($this->inPath);

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

    private function setImagePathAndDirectoryBySizes(string $template = ''): void
    {
        if (!$this->sizes) {
            $imageDirectory = $template;
            $resultPath = $imageDirectory . "/{$this->imageName}.{$this->imageFormat}";
        } else if (count($this->sizes) === 1) {
            $sizeName = array_keys($this->sizes)[0];
            $imageDirectory = $template;
            $resultPath = $imageDirectory . "/{$this->imageName}_{$sizeName}.{$this->imageFormat}";
        } else if (count($this->sizes) > 1) {
            foreach ($this->sizes as $sizeName => $size) {
                $imageDirectory = $template . "/{$this->sizesDirectory}";
                $resultPath[$sizeName] =
                    $imageDirectory . "/{$this->imageName}_{$sizeName}.{$this->imageFormat}";
            }
        }

        $this->imagePath = $this->convertByDirectorySeparator($resultPath);
        $this->imageDirectory = $this->convertByDirectorySeparator($imageDirectory);
    }

    private function getArrayStructure(): array
    {
        $resultArrayStructure = ['index', 'imageDirectory', 'default_size'];
        return array_flip($resultArrayStructure);
    }

    private function getResultArrayStructure(): array
    {
        $arrayStructure = $this->getArrayStructure();
        $arrayStructure['index'] = $this->imagePath;
        $arrayStructure['imageDirectory'] = $this->imageDirectory;

        if (count($this->sizes ?? []) > 1 and $this->defaultSize) {
            $arrayStructure['default_size'] = $this->defaultSize;
        } else {
            unset($arrayStructure['default_size']);
        }

        return $arrayStructure;
    }

    private function getDirectoriesTemplate(): string
    {
        return "{$this->rootDirectory}/{$this->exclusiveDirectory}/{$this->archiveDirectories}";
    }

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

    private function random(bool $hasSuffix = true, string $suffix = null): string
    {
        if ($hasSuffix) {
            $suffix = $suffix ? '_' . $suffix : '_' . rand(100, 999);
        }
        return time() . $suffix;
    }

    private function prepareVariables(): void
    {
        if ($this->raw) {
            $this->sizesDirectory = trim($this->sizesDirectory, '/\\');
            $this->inPath = trim($this->inPath, '/\\');
            return;
        }
        $this->rootDirectory = trim($this->rootDirectory, '/\\');
        $this->exclusiveDirectory = trim($this->exclusiveDirectory, '/\\');
        $this->archiveDirectories = trim($this->archiveDirectories, '/\\');
        $this->sizesDirectory = trim($this->sizesDirectory, '/\\');
    }
}
