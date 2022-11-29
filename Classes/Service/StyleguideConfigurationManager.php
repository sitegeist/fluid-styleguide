<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sitegeist\FluidStyleguide\Domain\Model\Package;
use Sitegeist\FluidStyleguide\Event\AfterConfigurationLoadedEvent;
use Sitegeist\FluidStyleguide\Exception\InvalidAssetException;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class StyleguideConfigurationManager
{
    /**
     * @var YamlFileLoader
     */
    protected $yamlFileLoader;

    /**
     * @var PackageManager
     */
    protected $packageManager;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var string
     */
    protected $defaultConfigurationFile = 'EXT:fluid_styleguide/Configuration/Yaml/FluidStyleguide.yaml';

    /**
     * @var array
     */
    protected $mergedConfiguration;

    public function __construct(
        YamlFileLoader $yamlFileLoader,
        PackageManager $packageManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->yamlFileLoader = $yamlFileLoader;
        $this->packageManager = $packageManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->loadConfiguration();
    }

    public function loadConfiguration()
    {
        $this->mergedConfiguration = $this->yamlFileLoader->load($this->defaultConfigurationFile)['FluidStyleguide'];

        // Merge default configuration with custom configuration
        $activeExtensions = $this->packageManager->getActivePackages();
        foreach ($activeExtensions as $package) {
            // Skip default configuration
            if ($package->getPackageKey() === 'fluid_styleguide') {
                continue;
            }

            $packageConfiguration = $package->getPackagePath() . 'Configuration/Yaml/FluidStyleguide.yaml';
            if (file_exists($packageConfiguration)) {
                ArrayUtility::mergeRecursiveWithOverrule(
                    $this->mergedConfiguration,
                    $this->yamlFileLoader->load($packageConfiguration)['FluidStyleguide'] ?? []
                );
            }
        }

        $this->mergedConfiguration = $this->eventDispatcher
            ->dispatch(new AfterConfigurationLoadedEvent($this->mergedConfiguration, self::getCurrentSite()))
            ->getConfiguration();

        // Sanitize component assets
        $this->mergedConfiguration['ComponentAssets']['Global']['Css'] = $this->sanitizeComponentAssets(
            $this->mergedConfiguration['ComponentAssets']['Global']['Css'] ?? []
        );
        $this->mergedConfiguration['ComponentAssets']['Global']['Javascript'] = $this->sanitizeComponentAssets(
            $this->mergedConfiguration['ComponentAssets']['Global']['Javascript'] ?? []
        );
        foreach ($this->mergedConfiguration['ComponentAssets']['Packages'] as &$assets) {
            $assets['Css'] = $this->sanitizeComponentAssets($assets['Css'] ?? []);
            $assets['Javascript'] = $this->sanitizeComponentAssets($assets['Javascript'] ?? []);
        }

        $this->mergedConfiguration['ResponsiveBreakpoints'] = array_filter($this->mergedConfiguration['ResponsiveBreakpoints']);
        $this->mergedConfiguration['Languages'] = array_filter($this->mergedConfiguration['Languages']);
    }

    public function getFeatures(): array
    {
        return $this->mergedConfiguration['Features'];
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return !empty($this->mergedConfiguration['Features'][$feature]);
    }

    public function getComponentContext(): string
    {
        return $this->mergedConfiguration['ComponentContext'] ?? '|';
    }

    public function getGlobalCss(): array
    {
        return $this->mergedConfiguration['ComponentAssets']['Global']['Css'] ?? [];
    }

    public function getGlobalJavascript(): array
    {
        return $this->mergedConfiguration['ComponentAssets']['Global']['Javascript'] ?? [];
    }

    public function getCssForPackage(Package $package): array
    {
        return $this->mergedConfiguration['ComponentAssets']['Packages'][$package->getNamespace()]['Css'] ?? [];
    }

    public function getJavascriptForPackage(Package $package): array
    {
        return $this->mergedConfiguration['ComponentAssets']['Packages'][$package->getNamespace()]['Javascript'] ?? [];
    }

    public function getStyleguideCss(): Uri
    {
        return $this->generateAssetUrl('EXT:fluid_styleguide/Resources/Public/Css/Styleguide.min.css');
    }

    public function getStyleguideJavascript(): Uri
    {
        return $this->generateAssetUrl('EXT:fluid_styleguide/Resources/Public/Javascript/Styleguide.min.js');
    }

    public function getResponsiveBreakpoints(): array
    {
        return $this->mergedConfiguration['ResponsiveBreakpoints'] ?? [];
    }

    public function getLanguages(): array
    {
        return $this->mergedConfiguration['Languages'] ?? [];
    }

    public function getLanguage($languageKey): ?array
    {
        $languageMatch = array_filter(
            $this->getLanguages(),
            function ($language) use ($languageKey) {
                return $language['identifier'] === $languageKey;
            }
        );
        return reset($languageMatch) ?: null;
    }

    public function getTemplateRootPaths(): array
    {
        return $this->mergedConfiguration['Fluid']['TemplateRootPaths'] ?? [];
    }

    public function getPartialRootPaths(): array
    {
        return $this->mergedConfiguration['Fluid']['PartialRootPaths'] ?? [];
    }

    public function getLayoutRootPaths(): array
    {
        return $this->mergedConfiguration['Fluid']['LayoutRootPaths'] ?? [];
    }

    public function getBrandingHighlightColor(): string
    {
        return $this->mergedConfiguration['Branding']['HighlightColor'] ?? '';
    }

    public function getBrandingFontFamily(): string
    {
        return $this->mergedConfiguration['Branding']['FontFamily'] ?? '';
    }

    public function getBrandingIframeBackground(): string
    {
        return $this->mergedConfiguration['Branding']['IframeBackground'] ?? '';
    }

    public function getBrandingCss(): string
    {
        $variables = array_filter([
            '--styleguide-highlight-color' => $this->getBrandingHighlightColor(),
            '--styleguide-font-family' => $this->getBrandingFontFamily(),
            '--styleguide-iframe-background' => $this->getBrandingIframeBackground()
        ]);

        return ':root {' . array_reduce(
            array_keys($variables),
            function ($css, $variable) use ($variables) {
                return $css . $variable . ':' . $variables[$variable] . ';';
            },
            ''
        ) . '}';
    }

    public function getBrandingTitle(): string
    {
        return $this->mergedConfiguration['Branding']['Title'] ?? $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
    }

    public function getBrandingIntro(): string
    {
        if ($this->mergedConfiguration['Branding']['IntroFile'] ?? '') {
            $introFile = GeneralUtility::getFileAbsFileName(
                $this->mergedConfiguration['Branding']['IntroFile']
            );
            return ($introFile && file_exists($introFile)) ? (string) file_get_contents($introFile) : '';
        }

        return '';
    }

    protected function sanitizeComponentAssets($assets)
    {
        if (is_string($assets)) {
            $assets = [$assets];
        } elseif (!is_array($assets)) {
            return [];
        }

        foreach ($assets as $key => &$asset) {
            // Support both strings and arrays with additional asset information
            if (is_array($asset) && $asset['file']) {
                $file =& $asset['file'];
            } else {
                $file =& $asset;
            }

            if (!static::isRemoteUri($file)) {
                try {
                    $file = $this->generateAssetUrl($file);
                } catch (InvalidAssetException $e) {
                    unset($assets[$key]);
                }
            }
        }
        return $assets;
    }

    /**
     * Generates an asset (js/css) url without throwing away any url prefixes
     *
     * @param string $path
     * @return Uri
     */
    protected function generateAssetUrl(string $path): Uri
    {
        $path = GeneralUtility::getFileAbsFileName($path);
        if (!$path) {
            throw new InvalidAssetException(sprintf('Asset not found: %s', $path), 1608723092);
        }

        $baseUrl = static::getCurrentSite()->getBase();
        $modified = filemtime($path);
        return $baseUrl
            ->withPath(
                $baseUrl->getPath() .
                PathUtility::stripPathSitePrefix(GeneralUtility::getFileAbsFileName($path))
            )
            ->withQuery('?' . $modified)
            ->withPort(GeneralUtility::getIndpEnv('TYPO3_PORT') ?: null);
    }

    /**
     * Checks if the provided uri is a valid remote uri
     *
     * @param string $uri
     * @return boolean
     */
    protected static function isRemoteUri(string $uri): bool
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        return ($scheme && in_array(strtolower($scheme), ['http', 'https']));
    }

    /**
     * Returns the current Site object to create urls
     *
     * @return SiteInterface
     */
    protected static function getCurrentSite(): SiteInterface
    {
        return $GLOBALS['TYPO3_CURRENT_SITE'];
    }
}
