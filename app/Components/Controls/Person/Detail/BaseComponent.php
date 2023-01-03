<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ORMFactory;
use Nette\DI\Container;

abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected ORMFactory $tableReflectionFactory;

    public function __construct(
        Container $container,
        protected readonly PersonModel $person,
        protected readonly FieldLevelPermissionValue $userPermissions,
        protected readonly bool $isOrg,
    ) {
        parent::__construct($container);
    }

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(): bool
    {
        $this->template->person = $this->person;
        $this->template->isOrg = $this->isOrg;
        if ($this->userPermissions < $this->getMinimalPermissions()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.permissionDenied.latte');
            return false;
        }
        return true;
    }

    abstract protected function getMinimalPermissions(): FieldLevelPermissionValue;

    protected function createComponentContestBadge(): ContestBadge
    {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }
}
