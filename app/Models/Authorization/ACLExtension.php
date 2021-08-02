<?php

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization\Assertions\QIDAssertion;
use FKSDB\Models\Authorization\Assertions\StoredQueryTagAssertion;
use FKSDB\Models\Expressions\Helpers;
use Nette\DI\CompilerExtension;
use Nette\Security\Permission;

class ACLExtension extends CompilerExtension
{
    /** @var string[] */
    public static array $semanticMap = [
        'qid' => QIDAssertion::class,
        'queryTag' => StoredQueryTagAssertion::class,
    ];

    public function __construct()
    {
        Helpers::registerSemantic(self::$semanticMap);
    }

    public function loadConfiguration(): void
    {
        parent::loadConfiguration();

        $builder = $this->getContainerBuilder();
        $definition = $builder->addDefinition('authorization')
            ->setFactory(Permission::class);

        $config = $this->getConfig();

        foreach ($config as $setup) {
            $stmt = Helpers::statementFromExpression($setup);
            $definition->addSetup($stmt->entity, $stmt->arguments);
        }
    }
}
