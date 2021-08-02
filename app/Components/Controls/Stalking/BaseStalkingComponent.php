<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Badges\NoRecordsBadge;
use FKSDB\Components\Badges\PermissionDeniedBadge;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\Models\ModelPerson;

abstract class BaseStalkingComponent extends BaseComponent
{

    protected ORMFactory $tableReflectionFactory;

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(ModelPerson $person, string $headline, int $userPermissions, int $minimalPermissions): void
    {
        $this->template->gender = $person->gender;
        $this->template->headline = $headline;
        if ($userPermissions < $minimalPermissions) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.permissionDenied.latte');
        }
    }

    protected function createComponentContestBadge(): ContestBadge
    {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentPermissionDenied(): PermissionDeniedBadge
    {
        return new PermissionDeniedBadge($this->getContext());
    }

    protected function createComponentNoRecords(): NoRecordsBadge
    {
        return new NoRecordsBadge($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }
}
