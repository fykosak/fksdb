<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Models\Authorization\Grant;
use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelGrant;
use FKSDB\Models\ORM\Models\ModelPerson;

class RoleComponent extends BaseStalkingComponent
{

    final public function render(ModelPerson $person, int $userPermissions): void
    {
        $this->beforeRender($person, _('Roles'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $login = $person->getLogin();
        $roles = [];
        if ($login) {
            /** @var ModelGrant $grant */
            foreach ($login->related(DbNames::TAB_GRANT, 'login_id') as $row) {
                $grant = ModelGrant::createFromActiveRow($row);
                $roles[] = new Grant($grant->role->name, $grant->getContest());
            }
        }
        $this->template->roles = $roles;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.role.latte');
    }
}
