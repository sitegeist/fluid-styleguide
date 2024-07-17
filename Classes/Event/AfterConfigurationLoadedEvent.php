<?php

declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Event;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final class AfterConfigurationLoadedEvent
{
    public function __construct(
        private array $configuration,
        private readonly SiteInterface $site
    ) {
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getSite(): SiteInterface
    {
        return $this->site;
    }
}
