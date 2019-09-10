<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\Package;
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

    public function __construct(
        ComponentLoader $componentLoader,
        ViewHelperResolver $viewHelperResolver
    ) {
        $this->componentLoader = $componentLoader;
        $this->viewHelperResolver = $viewHelperResolver;
    }

    public function findAll(): array
    {
        $fluidNamespaces = $this->viewHelperResolver->getNamespaces();
        $componentNamespaces = $this->componentLoader->getNamespaces();
        $packages = [];
        foreach ($fluidNamespaces as $namespaceAlias => $namespaceCandidates) {
            foreach ($namespaceCandidates as $namespaceCandidate) {
                // Ignore namespaces that are not associated with components
                if (!isset($componentNamespaces[$namespaceCandidate])) {
                    continue;
                }

                $packages[] = new Package(
                    $namespaceCandidate,
                    $namespaceAlias
                );
            }
        }

        return $packages;
    }

    public function findForComponentIdentifier(string $componentIdentifier): ?Package
    {
        $componentPackage = null;
        foreach ($this->findAll() as $package) {
            if (!$package->isResponsibleForComponent($componentIdentifier)) {
                continue;
            }

            // Prefer packages with higher namespace specificity
            if (
                isset($componentPackage) &&
                $componentPackage->getSpecificity() >= $package->getSpecificity()
            ) {
                continue;
            }

            $componentPackage = $package;
        }

        return $componentPackage;
    }
}
