<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\ViewHelpers\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ExampleViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument('component', Component::class, '', true);
        $this->registerArgument('fixtureName', 'string', '');
        $this->registerArgument('fixtureData', 'array', '');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        if (!isset($arguments['fixtureName']) && !isset($arguments['fixtureData'])) {
            throw new \InvalidArgumentException('TODO', 1566377563);
        }

        $fixtureData = $arguments['fixtureData'] ?? [];

        if (isset($arguments['fixtureName'])) {
            $componentFixture = $arguments['component']->getFixture($arguments['fixtureName']);
            if (!$componentFixture) {
                throw new \InvalidArgumentException('TODO', 1566377564);
            }

            $fixtureData = array_replace($componentFixture->getData(), $fixtureData);
        }


        return static::renderComponentTag(
            $arguments['component']->getName(),
            $fixtureData
        );
    }

    public static function renderComponentTag(ComponentName $componentName, array $data): string
    {
        $fluidComponent = new TagBuilder($componentName->getTagName());
        $data = array_map([static::class, 'encodeFluidVariable'], $data);

        if (isset($data['content'])) {
            $fluidComponent->setContent($data['content']);
            unset($data['content']);
        }

        $fluidComponent->addAttributes($data);

        return $fluidComponent->render();
    }

    /**
     * Encodes a fluid variable for use in component/viewhelper call
     *
     * @param $input mixed
     * @param $isRoot bool
     * @return string
     */
    public static function encodeFluidVariable($input, bool $isRoot = true): string
    {
        if (is_array($input)) {
            $fluidArray = [];
            foreach ($input as $key => $value) {
                $fluidArray[] = (string) $key . ': ' . static::encodeFluidVariable($value, false);
            }
            return '{' . implode(', ', $fluidArray) . '}';
        }

        if (is_string($input) && !$isRoot) {
            return "'" . addcslashes($input, "'") . "'";
        }

        if (is_bool($input)) {
            return ($input) ? 'TRUE' : 'FALSE';
        }

        return (string) $input;
    }
}
