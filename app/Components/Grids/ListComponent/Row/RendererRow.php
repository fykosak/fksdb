<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

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

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->renderer = $this->renderer;
        $this->template->model = $model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'renderer.latte');
    }
}
