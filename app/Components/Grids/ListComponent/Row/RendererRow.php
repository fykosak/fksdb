<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class RendererRow extends Row
{
    /** @var callable */
    protected $renderer;

    public function __construct(Container $container, callable $renderer)
    {
        parent::__construct($container);
        $this->renderer = $renderer;
    }

    public function render(Model $model, FieldLevelPermissionValue $userPermission): void
    {
        $this->template->renderer = $this->renderer;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'renderer.latte';
    }
}
