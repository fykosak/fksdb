<?php

namespace Authorization;

use Authorization\Assertions\EventOrgAssertion;
use Authorization\Assertions\EventOrgByIdAssertion;
use Authorization\Assertions\EventOrgByYearAssertion;
use Authorization\Assertions\QIDAssertion;
use Authorization\Assertions\StoredQueryTagAssertion;
use FKSDB\Config\Expressions\Helpers;
use Nette\Security\Permission;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ACLExtension extends \Nette\DI\CompilerExtension {

    public static $semanticMap = [
        'qid' => QIDAssertion::class,
        'queryTag' => StoredQueryTagAssertion::class,
        'isEventOrg' => EventOrgAssertion::class,
        'isEventOrgById' => EventOrgByIdAssertion::class,
        'isEventOrgByYear' => EventOrgByYearAssertion::class,
    ];

    public function __construct() {
        Helpers::registerSemantic(self::$semanticMap);
    }

    public function loadConfiguration() {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $definition = $builder->addDefinition('authorization')
            ->setFactory(Permission::class);

        foreach ($this->getConfig() as $setup) {
            $definition->addSetup(Helpers::statementFromExpression($setup));
        }
    }

}
