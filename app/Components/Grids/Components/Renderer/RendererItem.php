<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Renderer;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<M>
 */
class RendererItem extends BaseItem
{
    /**
     * @phpstan-var callable(M):(string|Html) $renderer
     */
    protected $renderer;

    /**
     * @phpstan-param callable(M):(string|Html) $renderer
     */
    public function __construct(Container $container, callable $renderer, Title $title)
    {
        parent::__construct($container, $title);
        $this->renderer = $renderer;
    }

    /**
     * @param M|null $model
     */
    public function render(?Model $model, ?int $userPermission, array $params = []): void
    {
        parent::render($model, $userPermission, ['renderer' => $this->renderer]);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'renderer.latte';
    }
}
