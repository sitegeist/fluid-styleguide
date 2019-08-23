<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Controller;

use Sitegeist\FluidStyleguide\Service\ComponentDownloadService;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentMetadata;
use Sitegeist\FluidStyleguide\Domain\Repository\ComponentRepository;

class StyleguideController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var ComponentRepository
     */
    protected $componentRepository;

    /**
     * @var ComponentDownloadService
     */
    protected $componentDownloadService;

    /**
     * Shows a list of all components
     *
     * @return void
     */
    public function listAction()
    {
        $allComponents = $this->componentRepository->findAllWithFixtures();
        $componentPackages = $this->groupComponentsByPackage($allComponents);

        $this->view->assignMultiple([
            'navigation' => $allComponents,
            'packages' => $componentPackages
        ]);
    }

    /**
     * Shows a component detail page
     *
     * @return void
     */
    public function showAction(string $component, string $fixture = 'default')
    {
        $this->view->assignMultiple([
            'navigation' => $this->componentRepository->findAllWithFixtures(),
            'activeComponent' => $this->componentRepository->findByIdentifier($component),
            'activeFixture' => $fixture
        ]);
    }

    /**
     * Shows a rendered example of a component. This will be shown inside of the iframe
     *
     * @return void
     */
    public function componentAction(string $component, string $fixture = 'default', array $formData = [])
    {
        $this->view->assignMultiple([
            'component' => $this->componentRepository->findByIdentifier($component),
            'fixtureName' => $fixture,
            'fixtureData' => $formData
        ]);
    }

    /**
     * Provides a zip download of a component folder
     *
     * @return void
     */
    public function downloadComponentZipAction(string $component)
    {
        $component = $this->componentRepository->findByIdentifier($component);
        $this->componentDownloadService->downloadZip($component);
    }

    protected function groupComponentsByPackage(array $components): array
    {
        $componentPackages = [];
        foreach ($components as $component) {
            $packageNamespace = $component->getName()->getPackage()->getNamespace();
            if (!isset($componentPackages[$packageNamespace])) {
                $componentPackages[$packageNamespace] = [];
            }

            $componentPackages[$packageNamespace][] = $component;
        }
        return $componentPackages;
    }

    /**
     * @param \Sitegeist\FluidStyleguide\Domain\Repository\ComponentRepository $componentRepository
     */
    public function injectComponentRepository(ComponentRepository $componentRepository)
    {
        $this->componentRepository = $componentRepository;
    }

    /**
     * Undocumented function
     *
     * @param \Sitegeist\FluidStyleguide\Service\ComponentDownloadService $componentDownloadService
     * @return void
     */
    public function injectComponentDownloadService(ComponentDownloadService $componentDownloadService)
    {
        $this->componentDownloadService = $componentDownloadService;
    }
}
