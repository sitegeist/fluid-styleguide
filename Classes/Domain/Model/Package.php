<?php
declare(strict_types=1);

namespace Sitegeist\FluidStyleguide\Domain\Model;

class Package
{
    /**
     * PHP namespace for the component package
     *
     * @var string
     */
    protected $namespace;

    /**
     * Fluid namespace alias for the component package
     *
     * @var string
     */
    protected $alias;

    public function __construct(string $namespace, string $alias)
    {
        $this->namespace = trim($namespace, '\\');
        $this->alias = $alias;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Returns the specificity (= depth) of the PHP namespace
     *
     * @var int
     */
    public function getSpecificity(): int
    {
        return substr_count($this->namespace, '\\');
    }

    /**
     * Checks if the specified component is part of this component package
     *
     * @param string $componentIdentifier
     * @return boolean
     */
    public function isResponsibleForComponent(string $componentIdentifier): bool
    {
        $componentIdentifier = trim($componentIdentifier, '\\');
        return strpos($componentIdentifier, $this->namespace) === 0;
    }

    public function extractComponentName(string $componentIdentifier): ?string
    {
        $componentIdentifier = trim($componentIdentifier, '\\');

        if (!$this->isResponsibleForComponent($componentIdentifier)) {
            return null;
        }

        return substr($componentIdentifier, strlen($this->namespace) + 1);
    }
}
