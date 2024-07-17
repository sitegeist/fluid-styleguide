<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

class ComponentFixture
{
    public function __construct(
        protected string $filePath, // Absolute path to the fixture file. Note that this file contains multiple fixtures
        protected string $name,
        protected array $data,
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
