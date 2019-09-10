<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

class ComponentLocation
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $directory;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->fileName = basename($filePath);
        $this->directory = dirname($this->filePath);
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function generatePathToFile(string $fileName): string
    {
        return $this->directory . DIRECTORY_SEPARATOR
            . ltrim($fileName, DIRECTORY_SEPARATOR);
    }
}
