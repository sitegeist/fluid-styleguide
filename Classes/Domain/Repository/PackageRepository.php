<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\Package;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactory;

class PackageRepository implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var ViewHelperResolverFactory
     */
    protected $viewHelperResolverFactory;

    /**
     * @var ComponentLoader
     */
    protected $componentLoader;

    public function __construct(ComponentLoader $componentLoader, ViewHelperResolverFactory $viewHelperResolverFactory)
    {
        $this->componentLoader = $componentLoader;
        $this->viewHelperResolverFactory = $viewHelperResolverFactory;
    }

    /**
     * Finds all components packages that are currently registered in this TYPO3 installation
     *
     * @return array
     */
    public function findAll(): array
    {
        $fluidNamespaces = $this->viewHelperResolverFactory->create()->getNamespaces();
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
                $matchingNamespaceAlias,
                $path
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
