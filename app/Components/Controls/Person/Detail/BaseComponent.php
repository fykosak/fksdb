<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use Nette\DI\Container;

abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected ReflectionFactory $tableReflectionFactory;

    public function __construct(
        Container $container,
        protected readonly PersonModel $person,
        protected readonly FieldLevelPermissionValue $userPermission,
        protected readonly bool $isOrg,
    ) {
        parent::__construct($container);
    }

    final public function injectPrimary(ReflectionFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(): bool
    {
        $this->template->person = $this->person;
        $this->template->isOrg = $this->isOrg;
        $this->template->userPermission = $this->userPermission;
        if ($this->userPermission->value < $this->getMinimalPermission()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'permissionDenied.latte');

            return false;
        }
        return true;
    }

    abstract protected function getMinimalPermission(): FieldLevelPermissionValue;

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
