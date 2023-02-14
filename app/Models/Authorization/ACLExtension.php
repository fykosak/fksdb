<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Expressions\Helpers;
use Nette\DI\CompilerExtension;
use Nette\Security\Permission;

class ACLExtension extends CompilerExtension
{
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
