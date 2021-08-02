<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

class PageStyleContainer
{

    public ?string $styleId;
    public array $mainContainerClassNames = ['container', 'bg-white-container'];
    private string $navBarClassName;

    public function __construct()
    {
        $this->styleId = null;
    }

    public function setWidePage(): void
    {
        foreach ($this->mainContainerClassNames as &$className) {
            if ($className === 'container') {
                $className = 'container-fluid';
            }
        }
        $this->mainContainerClassNames[] = 'px-3';
    }

    public function getMainContainerClassName(): string
    {
        return join(' ', $this->mainContainerClassNames);
    }

    public function getNavBarClassName(): string
    {
        return $this->navBarClassName ?? 'bg-light navbar-light';
    }

    public function setNavBarClassName(string $className): void
    {
        $this->navBarClassName = $className;
    }
}
