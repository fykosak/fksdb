<?php

namespace FKSDB\UI;

/**
 * Class PageStyleContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PageStyleContainer {
    /** @var string */
    public $styleId;
    /** @var string */
    private $navBarClassName;
    /** @var string[] */
    public $mainContainerClassNames = ['container', 'bg-white-container'];

    /**
     * PageStyleContainer constructor.
     */
    public function __construct() {
        $this->styleId = null;
    }

    /**
     * @return void
     */
    public function setWidePage() {
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

    /**
     * @param string $className
     * @return void
     */
    public function setNavBarClassName(string $className) {
        $this->navBarClassName = $className;
    }

    public function getNavBarClassName(): string {
        return $this->navBarClassName ?? 'bg-light navbar-light';
    }
}
