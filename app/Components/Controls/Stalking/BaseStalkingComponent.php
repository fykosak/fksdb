<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

abstract class BaseStalkingComponent extends BaseComponent
{
    protected ORMFactory $tableReflectionFactory;
    protected PersonModel $person;
    protected int $userPermissions;

    public function __construct(Container $container, PersonModel $person, int $userPermissions)
    {
        parent::__construct($container);
        $this->person = $person;
        $this->userPermissions = $userPermissions;
    }

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(): bool
    {
        $this->template->person = $this->person;
        if ($this->userPermissions < $this->getMinimalPermissions()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.permissionDenied.latte');
            return false;
        }
        return true;
    }

    abstract protected function getMinimalPermissions(): int;

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
