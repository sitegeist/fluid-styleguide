<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Psr\Container\ContainerInterface;
use Sitegeist\FluidStyleguide\Domain\Model\Package;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverFactoryInterface;

class PackageRepository implements \TYPO3\CMS\Core\SingletonInterface
{
    public function __construct(
        protected ComponentLoader $componentLoader,
        protected ContainerInterface $container,
    ) {
    }

    /**
     * Finds all components packages that are currently registered in this TYPO3 installation
     */
    public function findAll(): array
    {
        $fluidNamespaces = $this->getViewHelperResolver()->getNamespaces();
        $componentNamespaces = $this->componentLoader->getNamespaces();
        $packages = [];
        foreach ($componentNamespaces as $namespace => $paths) {
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
                $paths
            );
        }

        return $packages;
    }

    /**
     * Finds the component package the specified component belongs to
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

    protected function getViewHelperResolver(): ViewHelperResolver
    {
        return $this->container->get(ViewHelperResolverFactoryInterface::class)->create();
    }
}
