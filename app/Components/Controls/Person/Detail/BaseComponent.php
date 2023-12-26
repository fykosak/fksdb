<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use Nette\DI\Container;

abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected ReflectionFactory $tableReflectionFactory;
    protected PersonModel $person;
    protected int $userPermissions;

    public function __construct(Container $container, PersonModel $person, int $userPermissions)
    {
        parent::__construct($container);
        $this->person = $person;
        $this->userPermissions = $userPermissions;
    }

    final public function injectPrimary(ReflectionFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    public function beforeRender(): bool
    {
        $this->template->person = $this->person;
        if ($this->userPermissions < $this->getMinimalPermissions()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'permissionDenied.latte');
            return false;
        }
        return true;
    }

    abstract protected function getMinimalPermissions(): int;

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
}
