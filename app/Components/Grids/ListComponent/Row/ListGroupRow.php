<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class ListGroupRow extends ColumnsRow
{
    /** @var callable */
    private $modelToIterator;
    protected ?Title $title;

    public function __construct(Container $container, callable $callback, ?Title $title = null)
    {
        parent::__construct($container);
        $this->modelToIterator = $callback;
        $this->title = $title;
    }

    public function render(Model $model, int $userPermission): void
    {
        $this->template->models = ($this->modelToIterator)($model);
        $this->template->title = $this->title;
        parent::render($model, $userPermission);
    }

    public function getContainerClassName(): ?string
    {
        return 'list-group list-group-flush col';
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'listGroup.latte';
    }
}
