<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

class ComponentLocation
{
    protected string $fileName = '';
    protected string $directory = '';

    public function __construct(protected string $filePath)
    {
        $this->fileName = basename($filePath);
        $this->directory = dirname($filePath);
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
