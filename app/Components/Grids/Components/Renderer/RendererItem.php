<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Renderer;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class RendererItem extends BaseItem
{
    /**
     * @phpstan-var callable(TModel,int):(string|Html) $renderer
     */
    protected $renderer;

    /**
     * @phpstan-param callable(TModel,int):(string|Html) $renderer
     */
    public function __construct(Container $container, callable $renderer, ?Title $title = null)
    {
        parent::__construct($container, $title);
        $this->renderer = $renderer;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->renderHtml(($this->renderer)($model, $userPermission));
    }
}
