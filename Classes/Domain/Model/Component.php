<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

use JsonSerializable;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

class Component implements JsonSerializable
{
    /** @var array<string, ComponentFixture>|null  */
    protected ?array $fixtures = null;
    protected ?string $documentation;
    /** @var array<string, ArgumentDefinition>|null  */
    protected ?array $arguments = null;

    public function __construct(
        protected ComponentName $name,
        protected ComponentLocation $location,
    ) {
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
        $fixtureFilesToSearch = [
            '.fixture.php',
            '.fixture.json',
            '.fixture.yml',
            '.fixture.yaml'
        ];
        if (Environment::isComposerMode()) {
            $fixtureFilesToSearch[] = '.fixture.json5';
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

        if (!$this->hasFixtures()) {
            return $this->fixtures;
        }

        $fixtureFile = $this->getFixtureFile();
        $fileParts = pathinfo((string) $fixtureFile);
        switch ($fileParts['extension']) {
            case 'json':
                $fixtures = \json_decode(file_get_contents($fixtureFile), true) ?? [];
                break;
            case 'json5':
                if (function_exists('json5_decode')) {
                    $fixtures = \json5_decode(file_get_contents($fixtureFile), true) ?? [];
                } else {
                    $fixtures = [];
                }
                break;
            case 'yaml':
            case 'yml':
                $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
                $fixtures = $loader->load($fixtureFile) ?? [];
                break;
            case 'php':
                $fixtures = require $fixtureFile;
                break;
            default:
                throw new \Exception('Fixture format unknown', 1582196195);
        }
        if (!is_array($fixtures)) {
            throw new \InvalidArgumentException('Fixtures must be of type array', 1738326135);
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

    public function getDefaultValues(): array
    {
        return array_reduce($this->getArguments(), function ($defaults, $argument) {
            if (!$argument->isRequired()) {
                $defaults[$argument->getName()] = $argument->getDefaultValue();
            }
            return $defaults;
        }, []);
    }

    public function getCodeQualityConfiguration(): ?string
    {
        $configurationFileLocations = [
            $this->getLocation()->getDirectory(),
            $this->getName()->getPackage()->getPath(),
            rtrim($this->getName()->getPackage()->getExtension()->getPackagePath(), DIRECTORY_SEPARATOR)
        ];
        foreach ($configurationFileLocations as $configurationFileLocation) {
            $configurationFile = $configurationFileLocation . DIRECTORY_SEPARATOR . '.fclint.json';
            if (file_exists($configurationFile)) {
                return $configurationFile;
            }
        }
        return null;
    }

    protected function getComponentRenderer(): ComponentRenderer
    {
        $componentRenderer = GeneralUtility::makeInstance(ComponentRenderer::class);
        $componentRenderer->setComponentNamespace($this->name->getIdentifier());
        return $componentRenderer;
    }

    function jsonSerialize(): array
    {
        $arguments = [];
        foreach ($this->getArguments() as $key => $argument) {
            $arguments[$key] = [
                'name' => $argument->getName(),
                'type' => $argument->getType(),
                'description' => $argument->getDescription(),
                'required' => $argument->isRequired(),
                'defaultValue' => $argument->getDefaultValue(),
                'escape' => $argument->getEscape(),
            ];
        }
        return [
            'identifier' => $this->name->getIdentifier(),
            'location' => $this->location->getFilePath(),
            'fixtures' => $this->getFixtures(),
            'documentation' => $this->getDocumentation(),
            'arguments' => $arguments,
            'defaultValues' => $this->getDefaultValues(),
            'codeQualityConfiguration' => $this->getCodeQualityConfiguration()
        ];
    }
}
