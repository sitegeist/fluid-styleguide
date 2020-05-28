<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\Package;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use SMS\FluidComponents\Utility\ComponentLoader;

class PackageRepository implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var ViewHelperResolver
     */
    protected $viewHelperResolver;

    /**
     * @var ComponentLoader
     */
    protected $componentLoader;

    public function __construct()
    {
        $this->componentLoader = GeneralUtility::makeInstance(ComponentLoader::class);
        $this->viewHelperResolver = GeneralUtility::makeInstance(ViewHelperResolver::class);
    }

    /**
     * Finds all components packages that are currently registered in this TYPO3 installation
     *
     * @return array
     */
    public function findAll(): array
    {
        $fluidNamespaces = $this->viewHelperResolver->getNamespaces();
        $componentNamespaces = $this->componentLoader->getNamespaces();
        $packages = [];
        foreach ($componentNamespaces as $namespace => $path) {
            $matchingNamespaceAlias = '???';
            foreach ($fluidNamespaces as $namespaceAlias => $namespaceCandidates) {
                if (in_array($namespace, $namespaceCandidates)) {
                    $matchingNamespaceAlias = $namespaceAlias;
                    break;
                }
            }

            $packages[] = new Package(
                $namespace,
                $matchingNamespaceAlias
            );
        }

        return $packages;
    }

    /**
     * Finds the component package the specified component belongs to
     *
     * @param string $componentIdentifier
     * @return Package|null
     */
    public function findForComponentIdentifier(string $componentIdentifier): ?Package
    {
        $componentPackage = null;
        foreach ($this->findAll() as $package) {
            if (!$package->isResponsibleForComponent($componentIdentifier)) {
                continue;
            }

            // Prefer packages with higher namespace specificity
            if (isset($componentPackage) &&
                $componentPackage->getSpecificity() >= $package->getSpecificity()
            ) {
                continue;
            }

            $componentPackage = $package;
        }

        return $componentPackage;
    }
}
