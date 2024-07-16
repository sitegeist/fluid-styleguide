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
     * PHP namespace for the component package
     *
     * @var string
     */
    protected $namespace;

    /**
     * Fluid namespace alias for the component package
     *
     * @var string
     */
    protected $alias;

    /**
     * Path for the component package
     *
     * @var string
     */
    protected $path;

    /**
     * Associated TYPO3 extension
     *
     * @var \TYPO3\CMS\Core\Package\PackageInterface
     */
    protected $extension;

    public function __construct(string $namespace, string $alias, string $path)
    {
        $this->namespace = trim($namespace, '\\');
        $this->alias = $alias;
        $this->path = $path;
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
                if (strpos($this->getPath(), $extension->getPackagePath()) === 0) {
                    $this->extension = $extension;
                return $this->extension;
            }
        }

        return null;
    }

    /**
     * Returns the specificity (= depth) of the PHP namespace
     *
     * @var int
     */
    public function getSpecificity(): int
    {
        return substr_count($this->namespace, '\\');
    }

    /**
     * Checks if the specified component is part of this component package
     *
     * @param string $componentIdentifier
     * @return boolean
     */
    public function isResponsibleForComponent(string $componentIdentifier): bool
    {
        $componentIdentifier = trim($componentIdentifier, '\\');
        return strpos($componentIdentifier, $this->namespace) === 0;
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
