<?php declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Factory\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentLocation;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;

/**
 * Simple factory that just creates a new instance of Component and passes the name and location to the constructor.
 *
 * Replace this factory in the DI configuration if you need different components
 */
final class ComponentFactory implements ComponentFactoryInterface
{

    public function build(ComponentName $componentName, ComponentLocation $componentLocation) : Component
    {
        return new Component($componentName, $componentLocation);
    }
}
