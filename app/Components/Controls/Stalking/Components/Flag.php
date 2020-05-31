<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Flag
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Flag extends AbstractStalkingComponent {

    public function render(ModelPerson $person, int $userPermissions): void {
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
