<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Flag
 * *
 */
class Flag extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $this->template->flags = $person->getMPersonHasFlags();
        $this->template->setFile(__DIR__ . '/Flag.latte');
        $this->template->render();
    }

    protected function getHeadline(): string {
        return _('Flags');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_FULL, self::PERMISSION_RESTRICT];
    }
}
