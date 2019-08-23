<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MarkdownViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var \Parsedown
     */
    protected static $markdownParser;

    /**
     * Don't escape markdown html
     *
     * @var boolean
     */
    protected $escapeOutput = false;

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
    ): string {
        if (!isset(static::$markdownParser)) {
            static::$markdownParser = new \Parsedown();
        }

        return static::$markdownParser->text($renderChildrenClosure());
    }
}
