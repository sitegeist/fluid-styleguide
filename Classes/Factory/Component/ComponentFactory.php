<?php declare(strict_types=1);
/**
 * LICENSE
 *
 * This software and its source code is protected by copyright law (Sec. 69a ff. UrhG).
 * It is not allowed to make any kinds of modifications, nor must it be copied,
 * or published without explicit permission. Misuse will lead to persecution.
 *
 * @copyright  2022 infomax websolutions GmbH
 * @link       http://www.infomax-it.de
 */

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
