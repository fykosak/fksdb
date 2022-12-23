<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\ListComponent\ListComponent;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

abstract class BaseListComponent extends ListComponent
{
    protected ORMFactory $tableReflectionFactory;
    protected PersonModel $person;
    protected bool $isOrg;

    public function __construct(
        Container $container,
        PersonModel $person,
        int $userPermissions,
        bool $isOrg
    ) {
        parent::__construct($container, $userPermissions);
        $this->isOrg = $isOrg;
        $this->person = $person;
    }

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    final public function render(): void
    {
        $this->template->title = $this->getTitle();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        if ($this->userPermission < $this->getMinimalPermissions()) {
            return __DIR__ . DIRECTORY_SEPARATOR . 'permissionDenied.latte';
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    abstract protected function getTitle(): Title;
}
