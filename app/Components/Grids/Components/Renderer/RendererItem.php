<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Renderer;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class RendererItem extends BaseItem
{
    /** @var callable */
    protected $renderer;

    public function __construct(Container $container, callable $renderer, Title $title)
    {
        parent::__construct($container, $title);
        $this->renderer = $renderer;
    }

    public function render(?Model $model, ?int $userPermission): void
    {
        $this->template->renderer = $this->renderer;
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'renderer.latte';
    }
}
