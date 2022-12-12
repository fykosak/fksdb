<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Button;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class DefaultButton extends BaseComponent
{
    /** @var callable */
    private $linkCallback;
    private string $title;

    public string $className = 'btn btn-outline-secondary float-end';

    public function __construct(Container $container, string $title, callable $linkCallback)
    {
        parent::__construct($container);
        $this->title = $title;
        $this->linkCallback = $linkCallback;
    }

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->params = ($this->linkCallback)($model);
        $this->template->title = $this->title;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'default.latte');
    }
}
