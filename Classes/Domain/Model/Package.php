<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Package
{
    /**
     * Associated TYPO3 extension
     */
    protected PackageInterface $extension;

    public function __construct(
        protected string $namespace, // PHP namespace for the component package
        protected string $alias, // Fluid namespace alias for the component package
        protected string $path, // Path for the component package
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExtension(): ?PackageInterface
    {
        if ($this->extension) {
            return $this->extension;
        }

        $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
        $activeExtensions = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService)->getActivePackages();
        foreach ($activeExtensions as $extension) {
            if (str_starts_with($this->getPath(), (string) $extension->getPackagePath())) {
                $this->extension = $extension;
                return $this->extension;
            }
        }

        return null;
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
