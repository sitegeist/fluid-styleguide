<?php
namespace Sitegeist\FluidStyleguide\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class AssetViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('fileName', 'string', 'path to an asset file', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $fileName = $arguments['fileName'];

        return $fileName . '?' . filemtime($fileName);
    }
}
