<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 */
abstract class BaseItem extends BaseComponent
{
    public ?Title $title;

    public function __construct(Container $container, ?Title $title = null)
    {
        parent::__construct($container);
        $this->title = $title;
    }

    abstract protected function getTemplatePath(): string;

    /**
     * @param M|null $model
     * @note do not call from parent
     */
    public function render(?Model $model, ?int $userPermission): void
    {
        $this->doRender($model, $userPermission, [
            'model' => $model,
            'title' => $this->title,
            'userPermission' => $userPermission,
        ]);
    }

    /**
     * @phpstan-param array<string,mixed> $params
     */
    final public function doRender(?Model $model, ?int $userPermission, array $params = []): void
    {
        $this->template->render(
            $this->getTemplatePath(),
            array_merge([
                'model' => $model,
                'title' => $this->title,
                'userPermission' => $userPermission,
            ], $params)
        );
    }
}
