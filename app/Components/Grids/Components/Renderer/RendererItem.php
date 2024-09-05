<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Renderer;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 * @phpstan-extends BaseItem<TModel>
 */
class RendererItem extends BaseItem
{
    /**
     * @phpstan-var callable(TModel,int):(string|Html) $renderer
     */
    protected $renderer;
    public Title $title;

    /**
     * @phpstan-param callable(TModel,int):(string|Html) $renderer
     */
    public function __construct(Container $container, callable $renderer, Title $title)
    {
        parent::__construct($container);
        $this->title = $title;
        $this->renderer = $renderer;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . '../html.latte',
            [
                'html' => ($this->renderer)($model, $userPermission),
            ]
        );
    }

    public function renderTitle(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . '../html.latte',
            [
                'html' => $this->title->toHtml(),
            ]
        );
    }
}
