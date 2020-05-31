<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\Controls\Badges\NoRecordsBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelPerson;

abstract class StalkingControl extends BaseComponent {

    const PERMISSION_FULL = 1024;
    const PERMISSION_RESTRICT = 128;
    const PERMISSION_BASIC = 16;
    const PERMISSION_USE_FIELD_LEVEL = 2048;

    protected TableReflectionFactory $tableReflectionFactory;

    public function injectPrimary(TableReflectionFactory $tableReflectionFactory): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(ModelPerson $person, int $userPermissions): void {
        $this->template->userPermissions = $userPermissions;
        $this->template->gender = $person->gender;
    }

    public function createComponentContestBadge(): ContestBadge {
        return new ContestBadge($this->getContext());
    }

    public function createComponentPermissionDenied(): PermissionDeniedBadge {
        return new PermissionDeniedBadge($this->getContext());
    }

    public function createComponentNoRecords(): NoRecordsBadge {
        return new NoRecordsBadge($this->getContext());
    }

    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }
}
