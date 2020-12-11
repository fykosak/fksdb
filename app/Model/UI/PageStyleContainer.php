<?php

namespace FKSDB\Model\UI;

use Nette\SmartObject;

/**
 * Class PageStyleContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PageStyleContainer {
    use SmartObject;

    public ?string $styleId;
    private string $navBarClassName;
    public array $mainContainerClassNames = ['container', 'bg-white-container'];

    public function __construct() {
        $this->styleId = null;
    }

    public function setWidePage(): void {
        foreach ($this->mainContainerClassNames as &$className) {
            if ($className === 'container') {
                $className = 'container-fluid';
            }
        }
        $this->mainContainerClassNames[] = 'px-3';
    }

    public function getMainContainerClassName(): string {
        return join(' ', $this->mainContainerClassNames);
    }

    public function setNavBarClassName(string $className): void {
        $this->navBarClassName = $className;
    }

    public function getNavBarClassName(): string {
        return $this->navBarClassName ?? 'bg-light navbar-light';
    }
}
