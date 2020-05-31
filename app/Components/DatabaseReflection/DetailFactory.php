<?php

namespace FKSDB\Components\DatabaseReflection;

/**
 * Class DetailFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DetailFactory {

    private array $nodes = [];

    public function setNodes(array $nodes): void {
        $this->nodes = $nodes;
    }

    public function getSection(string $section): array {
        return $this->nodes[$section];
    }
}
