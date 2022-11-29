<?php

declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Event;

use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final class AfterConfigurationLoadedEvent
{
    private array $configuration;
    private SiteInterface $site;

    public function __construct(array $configuration, SiteInterface $site)
    {
        $this->configuration = $configuration;
        $this->site = $site;
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
