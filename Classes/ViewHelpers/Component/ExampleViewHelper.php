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
        $this->registerArgument('component', Component::class, 'Component that should be rendered', true);
        $this->registerArgument('fixtureName', 'string', 'Name of the fixture that should be used in the example');
        $this->registerArgument('fixtureData', 'array', 'Additional dynamic fixture data that should be used in the example');
    }

    /**
     * Renders fluid example code for the specified component
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        if (!isset($arguments['fixtureName']) && !isset($arguments['fixtureData'])) {
            throw new \InvalidArgumentException(sprintf(
                'A fixture name or fixture data has to be specified to render the component example of %s.',
                $arguments['component']->getName()->getIdentifier()
            ), 1566377563);
        }

        $fixtureData = $arguments['fixtureData'] ?? [];

        if (isset($arguments['fixtureName'])) {
            $componentFixture = $arguments['component']->getFixture($arguments['fixtureName']);
            if (!$componentFixture) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid fixture name "%s" specified for component %s.',
                    $arguments['fixtureName'],
                    $arguments['component']->getName()->getIdentifier()
                ), 1566377564);
            }

            // Merge static fixture data with manually edited data
            $fixtureData = array_replace($componentFixture->getData(), $fixtureData);
        }

        return static::renderComponentTag(
            $arguments['component']->getName(),
            $fixtureData
        );
    }

    /**
     * Renders fluid code of a component call
     *
     * @param ComponentName $componentName
     * @param array $data
     * @return string
     */
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
