<?php

namespace FKSDB\Components\DatabaseReflection;

use Tracy\Debugger;

/**
 * Class DetailFactory
 * @package FKSDB\Components\DatabaseReflection
 */
class DetailFactory {
    /**
     * @var array
     */
    private $nodes = [];

    /**
     * @param array $nodes
     */
    public function setNodes(array $nodes) {
        Debugger::barDump($nodes);
        $this->nodes = $nodes;
    }

    /**
     * @param string $section
     * @return array
     */
    public function getSection(string $section): array {
        return $this->nodes[$section];
    }
}
