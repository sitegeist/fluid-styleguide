<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

class ComponentFixture
{
    /**
     * Absolute path to the fixture json file
     * Note that this file contains multiple fixtures
     *
     * @var string
     */
    protected $filePath;

    /**
     * Name of the individual fixture
     *
     * @var string
     */
    protected $name;

    /**
     * Fixture data
     *
     * @var array
     */
    protected $data;

    public function __construct(string $filePath, string $name, array $data)
    {
        $this->filePath = $filePath;
        $this->name = $name;
        $this->data = $data;
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
