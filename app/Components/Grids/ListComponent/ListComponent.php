<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use FKSDB\Components\Grids\ListComponent\Button\ButtonGroup;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;

abstract class ListComponent extends BaseComponent implements IContainer
{
    protected ORMFactory $reflectionFactory;
    protected \Nette\ComponentModel\Container $buttons;
    /** @var callable */
    protected $classNameCallback = null;
    protected int $userPermission;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->buttons = new ButtonGroup($this->container);
        $this->addComponent($this->buttons, 'buttons');
        $this->monitor(Presenter::class, function (): void {
            $this->configure();
        });
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    public function render(): void
    {
        $this->template->models = $this->getModels();
        $this->template->userPermission = $this->userPermission;
        $this->template->classNameCallback = $this->classNameCallback;
        $this->template->title = $this->getComponent('title', false);
        $this->template->render($this->getTemplatePath());
    }

    abstract protected function getModels(): iterable;

    abstract protected function configure(): void;

    protected function setTitle(Control $title): void
    {
        $this->addComponent($title, 'title');
    }

    protected function addButton(ItemComponent $button, string $name): void
    {
        $this->buttons->addComponent($button, $name);
    }
}
