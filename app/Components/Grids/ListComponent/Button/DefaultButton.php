<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Button;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Components\Grids\ListComponent\ItemComponent;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class DefaultButton extends ItemComponent
{
    /** @var callable */
    private $linkCallback;
    private string $title;

    public string $className = 'btn btn-outline-secondary btn-sm float-end';

    public function __construct(Container $container, string $title, callable $linkCallback)
    {
        parent::__construct($container);
        $this->title = $title;
        $this->linkCallback = $linkCallback;
    }

    public function render(Model $model, FieldLevelPermissionValue $userPermission): void
    {
        $this->template->params = ($this->linkCallback)($model);
        $this->template->title = $this->title;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'default.latte';
    }
}
