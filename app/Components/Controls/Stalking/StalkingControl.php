<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Components\Controls\Badges\NoRecordsBadge;
use FKSDB\Components\Controls\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\DBReflection\LinkPrinterComponent;
use FKSDB\Components\Controls\DBReflection\ValuePrinter\ValuePrinterComponent;
use FKSDB\Model\DBReflection\DBReflectionFactory;
use FKSDB\Model\ORM\Models\ModelPerson;

/**
 * Class StalkingControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class StalkingControl extends BaseComponent {

    protected DBReflectionFactory $tableReflectionFactory;

    final public function injectPrimary(DBReflectionFactory $tableReflectionFactory): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(ModelPerson $person, string $headline, int $userPermissions, int $minimalPermissions): void {
        $this->template->gender = $person->gender;
        $this->template->headline = $headline;
        if ($userPermissions < $minimalPermissions) {
            $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.permissionDenied.latte');
            $this->template->render();
        }
    }

    protected function createComponentContestBadge(): ContestBadge {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentPermissionDenied(): PermissionDeniedBadge {
        return new PermissionDeniedBadge($this->getContext());
    }

    protected function createComponentNoRecords(): NoRecordsBadge {
        return new NoRecordsBadge($this->getContext());
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent {
        return new LinkPrinterComponent($this->getContext());
    }
}
