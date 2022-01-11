<?php

namespace FKSDB\Components\Controls\Navigation;

use Nette\DI\CompilerExtension;

class NavigationExtension extends CompilerExtension {

    public function loadConfiguration(): void {
        parent::loadConfiguration();

        $config = $this->getConfig();
        $navbar = $this->getContainerBuilder()->addDefinition('navbar')
            ->setType(NavigationFactory::class);

        $navbar->addSetup('setStructure', [$this->createFromStructure($config['structure'])]);
    }

    private function createNode(string $nodeId, array $arguments): array {
        return $this->parseIdAsLink($nodeId, $arguments);
    }

    private function createFromStructure(array $structure): array {
        $structureData = [];
        foreach ($structure as $nodeId => $children) {
            $structureData[$nodeId] = $this->createNode($nodeId, []);
            $structureData[$nodeId]['parents'] = [];
            foreach ($children as $key => $arguments) {
                $structureData[$nodeId]['parents'][$key] = $this->createNode($key, $arguments);
            }
        }
        return $structureData;
    }

    private function parseIdAsLink(string $nodeId, array $arguments): array {
        $data = $arguments;
        $fullQualityAction = str_replace('.', ':', $nodeId);
        $a = strrpos($fullQualityAction, ':');
        $presenterName = substr($fullQualityAction, 0, $a);
        $action = substr($fullQualityAction, $a + 1);
        $data['linkPresenter'] = $presenterName;
        $data['linkAction'] = $action;
        $data['linkParams'] = $arguments['params'] ?? null;
        unset($data['params']);
        return $data;
    }

}
