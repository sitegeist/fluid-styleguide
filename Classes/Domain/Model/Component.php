<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentLocation;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentFixture;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;

class Component
{
    /**
     * @var ComponentName
     */
    protected $name;

    /**
     * @var ComponentLocation
     */
    protected $location;

    /**
     * @var array
     */
    protected $fixtures;

    /**
     * @var string
     */
    protected $documentation;

    /**
     * @var array
     */
    protected $arguments;

    public function __construct(ComponentName $name, ComponentLocation $location)
    {
        $this->name = $name;
        $this->location = $location;
    }

    public function getName(): ComponentName
    {
        return $this->name;
    }

    public function getLocation(): ComponentLocation
    {
        return $this->location;
    }

    public function getFixtureFile(): string
    {
        if (Environment::isComposerMode()) {
            $fixtureFilesToSearch = [
                '.fixture.json',
                '.fixture.json5',
                '.fixture.yml',
                '.fixture.yaml'
            ];
        } else {
            // no composer, no json5 package
            $fixtureFilesToSearch = [
                '.fixture.json',
                '.fixture.yml',
                '.fixture.yaml'
            ];
        }

        foreach ($fixtureFilesToSearch as $fixtureFile) {
            $path = $this->location->generatePathToFile($this->name->getSimpleName() . $fixtureFile);
            if (file_exists($path)) {
                return $path;
            }
        }
        // fallback to .json
        return $this->location->generatePathToFile($this->name->getSimpleName() . '.fixture.json');
    }

    public function hasFixtures(): bool
    {
        return file_exists($this->getFixtureFile());
    }

    public function getFixtures(): array
    {
        if (isset($this->fixtures)) {
            return $this->fixtures;
        }

        $this->fixtures = [];

        if (!$this->hasFixtures()) {
            return $this->fixtures;
        }

        $fixtureFile = $this->getFixtureFile();
        $fileParts = pathinfo($fixtureFile);
        switch ($fileParts['extension']) {
            case 'json':
                $fixtures = \json_decode(file_get_contents($fixtureFile), true) ?? [];
                break;
            case 'json5':
                if (function_exists('json5_decode')) {
                    $fixtures = \json5_decode(file_get_contents($fixtureFile), true) ?? [];
                }
                break;
            case 'yaml':
            case 'yml':
                $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
                $fixtures = $loader->load($fixtureFile) ?? [];
                break;
            default:
                throw new \Exception('Fixture format unknown', 1582196195);
        }
        if (!isset($fixtures['default'])) {
            $fixtures['default'] = [];
        }
        foreach ($fixtures as $fixtureName => $fixtureData) {
            $this->fixtures[$fixtureName] = new ComponentFixture(
                $fixtureFile,
                $fixtureName,
                $fixtureData
            );
        }

        return $this->fixtures;
    }

    public function getFixture(string $name): ?ComponentFixture
    {
        return $this->getFixtures()[$name] ?? null;
    }

    public function getDocumentationFile(): string
    {
        return $this->location->generatePathToFile($this->name->getSimpleName() . '.md');
    }

    public function hasDocumentation(): bool
    {
        return file_exists($this->getDocumentationFile());
    }

    public function getDocumentation(): string
    {
        if (!isset($this->documentation)) {
            if ($this->hasDocumentation()) {
                $this->documentation = file_get_contents($this->getDocumentationFile());
            } else {
                $this->documentation = '';
            }
        }

        return $this->documentation;
    }

    public function getArguments(): array
    {
        if (!isset($this->arguments)) {
            $this->arguments = $this->getComponentRenderer()->prepareArguments();
        }

        return $this->arguments;
    }

    protected function getComponentRenderer()
    {
        $componentRenderer = GeneralUtility::makeInstance(ComponentRenderer::class);
        $componentRenderer->setComponentNamespace($this->name->getIdentifier());
        return $componentRenderer;
    }
}
