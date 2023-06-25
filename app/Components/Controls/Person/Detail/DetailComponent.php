<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\ListComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

abstract class DetailComponent extends BaseList
{
    protected PersonModel $person;
    protected bool $isOrg;

    public function __construct(
        Container $container,
        PersonModel $person,
        FieldLevelPermissionValue $userPermission,
        bool $isOrg
    ) {
        parent::__construct($container, $userPermission);
        $this->isOrg = $isOrg;
        $this->person = $person;
    }

    final public function render(): void
    {
        $this->template->headline = $this->getHeadline();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        if ($this->userPermission < $this->getMinimalPermission()) {
            return __DIR__ . DIRECTORY_SEPARATOR . 'permissionDenied.latte';
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    abstract protected function getHeadline(): Title;
}
