<?php

namespace Authorization;

use FKS\Config\Expressions\Helpers;
use Nette\Config\CompilerExtension;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ACLExtension extends CompilerExtension {

    public static $semanticMap = array(
        'qid' => 'Authorization\Assertions\QIDAssertion',
        'isEventOrg' => 'Authorization\Assertions\EventOrgAssertion',
        'isEventOrgById' => 'Authorization\Assertions\EventOrgByIdAssertion',
        'isEventOrgByYear' => 'Authorization\Assertions\EventOrgByYearAssertion',
    );

    public function __construct() {
        Helpers::registerSemantic(self::$semanticMap);

    }

    public function loadConfiguration() {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $definition = $builder->addDefinition('authorization')
                ->setClass('Nette\Security\Permission');

        $config = $this->getConfig();

        foreach ($config as $setup) {
            $stmt = Helpers::statementFromExpression($setup);
            $definition->setup[] = $stmt;
        }
    }

}
