<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentLocation;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Factory\Component\ComponentFactoryInterface;
use SMS\FluidComponents\Utility\ComponentLoader;

class ComponentRepository implements \TYPO3\CMS\Core\SingletonInterface
{
    public function __construct(
        protected PackageRepository $packageRepository,
        protected ComponentNameRepository $componentNameRepository,
        protected ComponentLoader $componentLoader,
        protected ComponentFactoryInterface $componentFactory,
    ) {
    }

    /**
     * Returns a list of all components in the current TYPO3 installation that have
     * a fixture file and thus can be displayed in the styleguide
     */
    public function findWithFixtures(): array
    {
        $packages = $this->packageRepository->findAll();

        $components = [];
        foreach ($packages as $package) {
            $detectedComponents = $this->componentLoader->findComponentsInNamespace(
                $package->getNamespace()
            );

            foreach ($detectedComponents as $componentIdentifier => $componentFilePath) {
                $component = $this->componentFactory->build(
                    new ComponentName($package->extractComponentName($componentIdentifier), $package),
                    new ComponentLocation($componentFilePath)
                );

                if ($component->hasFixtures()) {
                    $components[] = $component;
                }
            }
        }

        return $components;
    }

    /**
     * Returns the component record for the specified component identifier
     */
    public function findWithFixturesByIdentifier(string $identifier): ?Component
    {
        $identifier = trim($identifier, '\\');
        $componentName = $this->componentNameRepository->findByComponentIdentifier($identifier);
        if (!$componentName) {
            return null;
        }

        $componentFile = $this->componentLoader->findComponent($identifier);
        if (!$componentFile) {
            return null;
        }

        $component = $this->componentFactory->build($componentName, new ComponentLocation($componentFile));
        if (!$component->hasFixtures()) {
            return null;
        }

        return $component;
    }
}
