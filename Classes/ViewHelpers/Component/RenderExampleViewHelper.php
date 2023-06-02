<?php

namespace Sitegeist\FluidStyleguide\ViewHelpers\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderExampleViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /*
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $closure = $viewHelperVariableContainer->get(self::class, 'closure');

        if (isset($closure)) {
            return $closure($renderingContext);
        }
    }
}
