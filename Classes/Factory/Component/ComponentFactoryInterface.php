<?php declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Factory\Component;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentLocation;
use Sitegeist\FluidStyleguide\Domain\Model\ComponentName;

/**
 * Interface to create Components on the fly  so we can create custom components. The factory can be swapped using the DI container
 */
interface ComponentFactoryInterface
{

    /**
     * Builds the component and passes the component name and component location on.
     */
    public function build(ComponentName $componentName, ComponentLocation $componentLocation) : Component;
}
