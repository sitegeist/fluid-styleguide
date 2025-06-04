<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sitegeist\FluidComponentsLinter\Service\CodeQualityService;
use Sitegeist\FluidComponentsLinter\Service\ConfigurationService;
use Sitegeist\FluidStyleguide\Domain\Repository\ComponentRepository;
use Sitegeist\FluidStyleguide\Event\PostProcessComponentViewEvent;
use Sitegeist\FluidStyleguide\Event\PreProcessComponentViewEvent;
use Sitegeist\FluidStyleguide\Service\ComponentDownloadService;
use Sitegeist\FluidStyleguide\Service\StyleguideConfigurationManager;
use SMS\FluidComponents\Utility\ComponentLoader;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\View\FluidViewAdapter;
use TYPO3\CMS\Fluid\View\StandaloneView;

class StyleguideController
{
    protected StandaloneView|FluidViewAdapter $view;
    protected ServerRequestInterface $request;

    public function __construct(
        protected ComponentRepository $componentRepository,
        protected ComponentDownloadService $componentDownloadService,
        protected StyleguideConfigurationManager $styleguideConfigurationManager,
        protected ContainerInterface $container,
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $allComponents = $this->componentRepository->findWithFixtures();
        $componentPackages = $this->groupComponentsByPackage($allComponents);

        $this->view->assignMultiple([
            'navigation' => $allComponents,
            'packages' => $componentPackages
        ]);

        return new HtmlResponse($this->view->render('Styleguide/List'));
    }

    public function showAction(array $arguments = []): ResponseInterface
    {
        $component = $arguments['component'] ?? '';
        $fixture = $arguments['fixture'] ?? 'default';

        // Sanitize user input
        $component = $this->sanitizeComponentIdentifier($component);
        $fixture = $this->sanitizeFixtureName($fixture);

        // Check if component exists
        $component = $this->componentRepository->findWithFixturesByIdentifier($component);
        if (!$component) {
            return new Response('Component not found', 404);
        }

        if ($this->styleguideConfigurationManager->isFeatureEnabled('CodeQuality') && class_exists(CodeQualityService::class)) {
            $showQualityIssues = true;

            // Initialize code quality service
            $configurationService = new ConfigurationService;
            $configuration = $configurationService->getFinalConfiguration(false, $component->getCodeQualityConfiguration() ?? false);
            $registeredChecks = $configurationService->getRegisteredChecks();
            $codeQualityService = new CodeQualityService($configuration, $registeredChecks);

            // Get code quality issues for component
            $qualityIssues = $codeQualityService->validateComponent(
                $component->getLocation()->getFilePath()
            );
        } else {
            $showQualityIssues = false;
            $qualityIssues = [];
        }

        $this->view->assignMultiple([
            'navigation' => $this->componentRepository->findWithFixtures(),
            'activeComponent' => $component,
            'activeFixture' => $fixture,
            'showQualityIssues' => $showQualityIssues,
            'qualityIssues' => $qualityIssues
        ]);

        return new HtmlResponse($this->view->render('Styleguide/Show'));
    }

    /**
     * Shows a rendered example of a component. This will be shown inside of the iframe
     *
     * @return void
     */
    public function componentAction(array $arguments = [])
    {
        $component = $arguments['component'] ?? '';
        $fixture = $arguments['fixture'] ?? 'default';
        $formData = $arguments['formData'] ?? [];

        // Sanitize user input
        $component = $this->sanitizeComponentIdentifier($component);
        $fixture = $this->sanitizeFixtureName($fixture);
        if (!$this->styleguideConfigurationManager->isFeatureEnabled('Editor')) {
            $formData = [];
        } else {
            $formData = $this->sanitizeFormData($formData);
        }

        // Check if component exists
        $component = $this->componentRepository->findWithFixturesByIdentifier($component);
        if (!$component) {
            return new Response('Component not found', 404);
        }

        $package = $component->getName()->getPackage();

        $this->view->assignMultiple([
            'component' => $component,
            'componentCss' => $this->styleguideConfigurationManager->getCssForPackage($package),
            'componentJavascript' => $this->styleguideConfigurationManager->getJavascriptForPackage($package),
            'fixtureName' => $fixture,
            'fixtureData' => $formData
        ]);

        $eventDispatcher = $this->container->get(EventDispatcher::class);

        $eventDispatcher->dispatch(new PreProcessComponentViewEvent($component, $fixture, $formData, $this->view));

        $renderedView = $this->view->render('Styleguide/Component');

        $event = new PostProcessComponentViewEvent($component, $fixture, $formData, $renderedView);
        $event = $eventDispatcher->dispatch($event);

        $renderedView = $event->getRenderedView();
        $renderedView = str_replace('<!-- ###ADDITIONAL_HEADER_DATA### -->', implode('', $event->getHeaderData()), $renderedView);
        $renderedView = str_replace('<!-- ###ADDITIONAL_FOOTER_DATA### -->', implode('', $event->getFooterData()), $renderedView);

        return $renderedView;
    }

    /**
     * Provides a zip download of a component folder
     */
    public function downloadComponentZipAction(array $arguments = [])
    {
        $component = $arguments['component'] ?? '';

        // Sanitize user input
        if (!$this->styleguideConfigurationManager->isFeatureEnabled('ZipDownload')) {
            return new Response('Zip download is not available', 403);
        }
        $component = $this->sanitizeComponentIdentifier($component);

        // Check if component exists
        $component = $this->componentRepository->findWithFixturesByIdentifier($component);
        if (!$component) {
            return new Response('Component not found', 404);
        }

        return $this->componentDownloadService->downloadZip($component);
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

    public function initializeView(StandaloneView|FluidViewAdapter $view): void
    {
        $this->view = $view;

        $this->view->assignMultiple([
            'styleguideConfiguration' => $this->styleguideConfigurationManager,
            'styleguideLanguage' => $this->request->getAttribute('language'),
            'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ?? '',
            'baseUri' => $this->request->getAttribute('site')->getBase()
        ]);

        $this->registerDemoComponents();
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Makes sure that no malicious user input will be passed to a component
     */
    protected function sanitizeFormData(array $formData): array
    {
        foreach ($formData as $key => &$value) {
            // Throw away any input other than string
            if (!is_string($value)) {
                unset($formData[$key]);
                continue;
            }

            // Convert to integer
            if (MathUtility::canBeInterpretedAsInteger($value)) {
                $value = (int)$value;
                // Convert to float
            } elseif (MathUtility::canBeInterpretedAsFloat($value)) {
                $value = (float)$value;
                // Convert to boolean
            } elseif (mb_strtoupper($value) === 'TRUE' || mb_strtoupper($value) === 'FALSE') {
                $value = (mb_strtoupper($value) === 'TRUE');
                // Escape string if necessary
            } elseif ($this->styleguideConfigurationManager->isFeatureEnabled('EscapeInputFromEditor')) {
                $value = htmlspecialchars($value);
            }
        }

        return $formData;
    }

    /**
     * Make sure that the component identifier doesn't include any malicious characters
     */
    protected function sanitizeComponentIdentifier(string $componentIdentifier): string
    {
        return trim((string) preg_replace('#[^a-z0-9_\\\\]#i', '', $componentIdentifier), '\\');
    }

    /**
     * Make sure that the fixture name doesn't include any malicious characters
     */
    protected function sanitizeFixtureName(string $fixtureName): string
    {
        return preg_replace('#[^a-z0-9_]#i', '', $fixtureName);
    }

    protected function registerDemoComponents(): void
    {
        $componentLoader = $this->container->get(ComponentLoader::class);
        if (count($componentLoader->getNamespaces()) === 1 ||
            $this->styleguideConfigurationManager->isFeatureEnabled('DemoComponents')
        ) {
            $demoNamespace = 'Sitegeist\\FluidStyleguide\\DemoComponents';
            $componentLoader->addNamespace(
                $demoNamespace,
                ExtensionManagementUtility::extPath(
                    'fluid_styleguide',
                    'Resources/Private/DemoComponents'
                )
            );
            $this->view->getRenderingContext()->getViewHelperResolver()->addNamespace(
                'demo',
                $demoNamespace
            );
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['demo'] = [$demoNamespace];
        }
    }
}
