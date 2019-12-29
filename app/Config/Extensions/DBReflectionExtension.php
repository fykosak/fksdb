<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Config\CompilerExtension;
use Tracy\Debugger;

/**
 * Class StalkingExtension
 * @package FKSDB\Config\Extensions
 */
class DBReflectionExtension extends CompilerExtension {

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        foreach ($this->config['tables'] as $tableName => $fields) {
            foreach ($fields as $fieldName => $field) {
                if (preg_match('/([A-Za-z0-9]+\\\\)*/', $field)) {
                    $builder->addDefinition($this->prefix($tableName . '.' . $fieldName))
                        ->setFactory($field);
                    continue;
                }
            }
        }
    }
}
