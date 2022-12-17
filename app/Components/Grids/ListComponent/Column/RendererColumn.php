<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Column;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

class RendererColumn extends Column
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
        $this->beforeRender($model, $userPermission);
        $this->template->renderer = $this->renderer;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'renderer.latte');
    }
}
