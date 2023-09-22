<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Utils\Paginator as NettePaginator;

/**
 * @method BasePresenter getPresenter()
 * @phpstan-template TModel of Model
 */
abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected int $userPermission;
    public bool $paginate = true;
    public bool $counter = true;
    protected ORMFactory $tableReflectionFactory;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->monitor(Presenter::class, fn() => $this->configure());
    }

    final public function injectBase(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    abstract protected function getTemplatePath(): string;

    abstract protected function configure(): void;

    /**
     * @phpstan-return TypedSelection<TModel>|TypedGroupedSelection<TModel>
     */
    abstract protected function getModels(): Selection;

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    abstract protected function addButton(BaseItem $component, string $name): BaseItem;

    public function render(): void
    {
        $this->template->render($this->getTemplatePath(), [
            'counter' => $this->counter,
            'paginate' => $this->paginate,
            'models' => $this->getModels(),
            'userPermission' => $this->userPermission,
        ]);
    }

    protected function createComponentPaginator(): Paginator
    {
        return new Paginator($this->container);
    }

    public function getPaginator(): NettePaginator
    {
        /** @var Paginator $control */
        $control = $this->getComponent('paginator');
        return $control->paginator;
    }

    /**
     * @phpstan-return Button<TModel>
     * @phpstan-param array<string,string> $params
     */
    protected function addPresenterButton(
        string $destination,
        string $name,
        string $label,
        bool $checkACL = true,
        array $params = [],
        ?string $className = null
    ): Button {
        $paramMapCallback = function (Model $model) use ($params): array {
            $hrefParams = [];
            foreach ($params as $key => $value) {
                $hrefParams[$key] = $model->{$value};
            }
            return $hrefParams;
        };
        /** @phpstan-var Button<TModel> $button */
        $button = $this->addButton(
            new Button(
                $this->container,
                $this->getPresenter(),
                new Title(null, _($label)),
                fn(Model $model): array => [$destination, $paramMapCallback($model)],
                $className,
                fn(Model $model): bool => $checkACL ? $this->getPresenter()->authorized(
                    $destination,
                    $paramMapCallback($model)
                ) : true
            ),
            $name
        );
        return $button;
    }

    /**
     * @phpstan-return Button<TModel>
     * @throws BadTypeException
     * @deprecated
     */
    protected function addORMLink(string $linkId, bool $checkACL = false, ?string $className = null): Button
    {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        /** @phpstan-var Button<TModel> $button */
        $button = new Button(
            $this->container,
            $this->getPresenter(),
            new Title(null, $factory->getText()),
            fn(?Model $model): array => $factory->createLinkParameters($model),
            $className,
            fn(?Model $model): bool => $checkACL
                ? $this->getPresenter()->authorized(...$factory->createLinkParameters($model))
                : true
        );
        $this->addButton($button, str_replace('.', '_', $linkId));
        return $button;
    }
}
