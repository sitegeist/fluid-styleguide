<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Repository;

use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use Sitegeist\FluidStyleguide\Domain\Repository\PackageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ComponentNameRepository implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var PackageRepository
     */
    protected $packageRepository;

    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    public function findByComponentIdentifier(string $componentIdentifier): ?ComponentName
    {
        $package = $this->packageRepository->findForComponentIdentifier($componentIdentifier);
        if (!$package) {
            return null;
        }

        return new ComponentName(
            $package->extractComponentName($componentIdentifier),
            $package
        );
    }
}
