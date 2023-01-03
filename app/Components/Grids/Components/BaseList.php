<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

abstract class BaseList extends BaseComponent
{
    protected FieldLevelPermissionValue $userPermission;

    public function __construct(Container $container, FieldLevelPermissionValue $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->monitor(Presenter::class, fn() => $this->configure());
    }

    abstract protected function getTemplatePath(): string;

    abstract protected function configure(): void;

    abstract protected function getModels(): Selection;

    public function render(): void
    {
        $this->template->models = $this->getModels();
        $this->template->userPermission = $this->userPermission;
        $this->template->render($this->getTemplatePath());
    }
}
