<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class ListGroupContainer extends RowContainer
{
    /** @var callable */
    private $modelToIterator;

    public function __construct(Container $container, callable $callback, Title $title)
    {
        parent::__construct($container, $title);
        $this->modelToIterator = $callback;
    }

    public function render(Model $model, int $userPermission): void
    {
        $this->template->models = ($this->modelToIterator)($model);
        parent::render($model, $userPermission);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'listGroup.latte';
    }
}
