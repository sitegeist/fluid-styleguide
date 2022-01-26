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
 * Interface to create Components on the fly  so we can create custom components. The factory can be swapped using the DI container
 */
interface ComponentFactoryInterface
{

    /**
     * Builds the component and passes the component name and component location on.
     *
     * @param ComponentName $componentName
     * @param ComponentLocation $componentLocation
     * @return Component
     */
    public function build(ComponentName $componentName, ComponentLocation $componentLocation) : Component;
}
