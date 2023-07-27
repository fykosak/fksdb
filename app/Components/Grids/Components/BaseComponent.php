<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

/**
 * @method BasePresenter getPresenter()
 */
abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected int $userPermission;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->monitor(Presenter::class, fn() => $this->configure());
    }

    abstract protected function getTemplatePath(): string;

    abstract protected function configure(): void;

    abstract protected function getModels(): Selection;

    abstract protected function addButton(BaseItem $component, string $name): void;

    public function render(): void
    {
        $this->template->render($this->getTemplatePath(), [
            'models' => $this->getModels(),
            'userPermission' => $this->userPermission,
        ]);
    }

    protected function addPresenterButton(
        string $destination,
        string $name,
        string $label,
        bool $checkACL = true,
        array $params = [],
        ?string $className = null
    ): PresenterButton {
        $paramMapCallback = function (Model $model) use ($params): array {
            $hrefParams = [];
            foreach ($params as $key => $value) {
                $hrefParams[$key] = $model->{$value};
            }
            return $hrefParams;
        };
        $button = new PresenterButton(
            $this->container,
            new Title(null, _($label)),
            fn(Model $model): array => [$destination, $paramMapCallback($model)],
            $className,
            fn(Model $model): bool => $checkACL ? $this->getPresenter()->authorized(
                $destination,
                $paramMapCallback($model)
            ) : true
        );
        $this->addButton($button, $name);
        return $button;
    }
}
