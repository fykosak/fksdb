<?php

namespace FKSDB\UI;

use Tracy\Debugger;

/**
 * Class PageStyleContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PageStyleContainer {
    /** @var string */
    public $styleId;
    /** @var string */
    public $navBarClassName;
    /** @var string */
    public $mainContainerClassName;

    /**
     * PageStyleContainer constructor.
     * @param string|null $styleId
     * @param string $navBarClassName
     * @param string $mainContainerClassName
     */
    public function __construct(string $styleId = null, string $navBarClassName = 'bg-light navbar-light', string $mainContainerClassName = 'container bg-white-container') {
        Debugger::barDump('C');
        $this->styleId = $styleId;
        $this->navBarClassName = $navBarClassName;
        $this->mainContainerClassName = $mainContainerClassName;
    }
}
