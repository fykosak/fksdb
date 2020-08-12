<?php

namespace FKSDB\Components\Controls\Navigation;


use Tracy\Debugger;

class NavigationFactory {
    private array $nodes = [];

    private array $nodeChildren = [];

    private array $structure;

    public function getNode(string $nodeId): array {
        return $this->nodes[$nodeId];
    }

    public function createNode(string $nodeId, array $arguments): void {
        $this->nodes[$nodeId] = $arguments;
    }

    public function setStructure(array $structure): void {
        $this->structure = $structure;
    }

    public function getStructure(string $id): array {
        return $this->structure[$id];
    }

    public function addParent(string $idChild, string $idParent): void {
        if (!isset($this->nodeChildren)) {
            $this->nodeChildren[$idParent] = [];
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    public function getParent(string $id): array {
        return $this->nodeChildren[$id] ?? [];
    }
}
