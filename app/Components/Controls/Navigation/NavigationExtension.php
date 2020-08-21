<?php

namespace FKSDB\Components\Controls\Navigation;

use Nette\DI\CompilerExtension;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class NavigationExtension extends CompilerExtension {

    public function loadConfiguration(): void {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();

        $navbar = $builder->addDefinition('navbar')
            ->setType(NavigationFactory::class);
        $navbar->setAutowired(true);

        $navbar->addSetup('setStructure', [$this->createFromStructure($config['structure'])]);
    }

    private function createNode(string $nodeId, array $arguments = []): array {
        return $this->parseIdAsLink($nodeId, $arguments);
    }

    private function createFromStructure(iterable $structure): array {
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

    private function parseIdAsLink(string $nodeId, ?array $arguments): array {
        $data = $arguments;
        $fullQualityAction = str_replace('.', ':', $nodeId);
        $a = strrpos($fullQualityAction, ':');
        $presenterName = substr($fullQualityAction, 0, $a);
        $action = substr($fullQualityAction, $a + 1);
        $data['linkPresenter'] = $presenterName;
        $data['linkAction'] = $action;
        $data['linkParams'] = isset($arguments['params']) ? $arguments['params'] : null;
        unset($data['params']);
        return $data;
    }

}
