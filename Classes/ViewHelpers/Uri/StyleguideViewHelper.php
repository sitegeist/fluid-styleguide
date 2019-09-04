<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Uri;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class StyleguideViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('action', 'string', 'Action name', true);
        $this->registerArgument('arguments', 'array', 'Action arguments', false, []);
    }

    /**
     * Renders markdown code in fluid templates
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): UriInterface {
        $prefix = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('fluid_styleguide', 'uriPrefix');
        $prefix = rtrim($prefix, '/') . '/';
        // TODO generate relative urls
        return $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getBase()
            ->withPath($prefix . $arguments['action'])
            ->withQuery(http_build_query($arguments['arguments']));
    }
}
