<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Event;

use Sitegeist\FluidStyleguide\Domain\Model\Component;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\View\FluidViewAdapter;

final class PreProcessComponentViewEvent
{
    public function __construct(
        public readonly Component $component,
        public readonly string $fixtureName,
        public readonly array $fixtureData,
        public readonly StandaloneView|FluidViewAdapter $view,
    ) {
    }
}
