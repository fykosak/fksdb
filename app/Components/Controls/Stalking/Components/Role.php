<?php

namespace FKSDB\Components\Controls\Stalking;

use Authorization\Grant;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelGrant;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Role
 * *
 */
class Role extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $template = $this->template;
        $login = $person->getLogin();
        $roles = [];
        if ($login) {
            /** @var ModelGrant $grant */
            foreach ($login->related(DbNames::TAB_GRANT, 'login_id') as $grant) {
                $roles[] = new Grant($grant->contest_id, $grant->ref(DbNames::TAB_ROLE, 'role_id')->name);
            }
        }
        $this->template->roles = $roles;
        $template->setFile(__DIR__ . '/Role.latte');
        $template->render();
    }

    /**
     * @return int[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_FULL, self::PERMISSION_RESTRICT];
    }

    protected function getHeadline(): string {
        return _('Roles');
    }
}
