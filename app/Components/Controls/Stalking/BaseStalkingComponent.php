<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Badges\ContestBadge;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\Models\PersonModel;

abstract class BaseStalkingComponent extends BaseComponent
{
    protected ORMFactory $tableReflectionFactory;

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(
        PersonModel $person,
        string $headline,
        int $userPermissions,
        int $minimalPermissions
    ): void {
        $this->getTemplate()->gender = $person->gender;
        $this->getTemplate()->headline = $headline;
        if ($userPermissions < $minimalPermissions) {
            $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.permissionDenied.latte');
        }
    }

    protected function createComponentContestBadge(): ContestBadge
    {
        return new ContestBadge($this->getContext());
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
