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
    /**
     * @var PackageRepository
     */
    protected $packageRepository;

    /**
     * @var ComponentNameRepository
     */
    protected $componentNameRepository;

    /**
     * @var ComponentLoader
     */
    protected $componentLoader;

    /**
     * @var ComponentFactoryInterface
     */
    protected $componentFactory;

    public function __construct(PackageRepository $packageRepository, ComponentNameRepository $componentNameRepository, ComponentLoader $componentLoader, ComponentFactoryInterface $componentFactory)
    {
        $this->packageRepository = $packageRepository;
        $this->componentNameRepository = $componentNameRepository;
        $this->componentLoader = $componentLoader;
        $this->componentFactory = $componentFactory;
    }

    /**
     * Returns a list of all components in the current TYPO3 installation that have
     * a fixture file and thus can be displayed in the styleguide
     *
     * @return array
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
     *
     * @param string $identifier
     * @return Component|null
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
