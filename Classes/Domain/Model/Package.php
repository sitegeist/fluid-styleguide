<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function array_values;

class Package
{
    /**
     * Associated TYPO3 extension(s)
     * @var list<PackageInterface>|null
     */
    protected ?array $extensions = null;

    public function __construct(
        protected string $namespace, // PHP namespace for the component package
        protected string $alias, // Fluid namespace alias for the component package
        /**
         * @var list<string> Path for the component package
         */
        protected array $paths,
    ) {
        $this->namespace = trim($namespace, '\\');
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return list<PackageInterface> returns first matching extension
     */
    public function getExtensions(): array
    {
        if ($this->extensions !== null) {
            return $this->extensions;
        }

        $extensions = [];
        $activeExtensions = GeneralUtility::makeInstance(PackageManager::class)->getActivePackages();
        foreach ($activeExtensions as $extension) {
            foreach ($this->getPaths() as $path) {
                // Check if the package path starts with the extension's package path
                if (str_starts_with($path, (string) $extension->getPackagePath())) {
                    $extensions[$extension->getPackagePath()] = $extension;
                }
            }
        }

        return $this->extensions = array_values($extensions);
    }

    /**
     * Returns the specificity (= depth) of the PHP namespace
     */
    public function getSpecificity(): int
    {
        return substr_count($this->namespace, '\\');
    }

    /**
     * Checks if the specified component is part of this component package
     */
    public function isResponsibleForComponent(string $componentIdentifier): bool
    {
        $componentIdentifier = trim($componentIdentifier, '\\');
        return str_starts_with($componentIdentifier, $this->namespace);
    }

    public function extractComponentName(string $componentIdentifier): ?string
    {
        $componentIdentifier = trim($componentIdentifier, '\\');

        if (!$this->isResponsibleForComponent($componentIdentifier)) {
            return null;
        }

        return substr($componentIdentifier, strlen($this->namespace) + 1);
    }
}
