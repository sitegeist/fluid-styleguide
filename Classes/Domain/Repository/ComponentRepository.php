<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentLocation;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Domain\Repository\ComponentNameRepository;
use Sitegeist\FluidStyleguide\Domain\Repository\PackageRepository;
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

    public function __construct(
        PackageRepository $packageRepository,
        ComponentNameRepository $componentNameRepository,
        ComponentLoader $componentLoader
    ) {
        $this->packageRepository = $packageRepository;
        $this->componentNameRepository = $componentNameRepository;
        $this->componentLoader = $componentLoader;
    }

    public function findAllWithFixtures(): array
    {
        $packages = $this->packageRepository->findAll();

        $components = [];
        foreach ($packages as $package) {
            $detectedComponents = $this->componentLoader->findComponentsInNamespace(
                $package->getNamespace()
            );

            foreach ($detectedComponents as $componentIdentifier => $componentFilePath) {
                $component = new Component(
                    new ComponentName(
                        $package->extractComponentName($componentIdentifier),
                        $package
                    ),
                    new ComponentLocation($componentFilePath)
                );

                if ($component->hasFixtures()) {
                    $components[] = $component;
                }
            }
        }

        return $components;
    }

    public function findByIdentifier(string $identifier): ?Component
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

        return new Component(
            $componentName,
            new ComponentLocation($componentFile)
        );
    }
}
