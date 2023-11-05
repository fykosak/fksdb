<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\Paginator as NettePaginator;

/**
 * @method BasePresenter getPresenter()
 * @phpstan-template TModel of Model
 * @phpstan-template TFilterParams of array
 */
abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent
{
    protected bool $filtered = false;
    protected int $userPermission;
    protected bool $paginate = true;
    protected bool $counter = true;
    protected ReflectionFactory $tableReflectionFactory;
    /**
     * @persistent
     * @phpstan-var TFilterParams
     */
    public array $filterParams = [];

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container);
        $this->userPermission = $userPermission;
        $this->monitor(Presenter::class, fn() => $this->configure());
    }

    final public function injectBase(ReflectionFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    abstract protected function getTemplatePath(): string;

    abstract protected function configure(): void;

    protected function configureForm(Form $form): void
    {
    }

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
            'filtered' => $this->filtered,
            'filterParams' => $this->filterParams,
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

    final protected function createComponentFilterForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $this->configureForm($form);
        $applyButton = $control->getForm()->addSubmit('apply', _('Apply filter!'));
        $resetButton = $control->getForm()->addSubmit('reset', _('Reset filter!'));
        $applyButton->onClick[] = function (SubmitButton $button): void {
            $this->filterParams = FormUtils::removeEmptyValues(
            /** @phpstan-ignore-next-line */
                FormUtils::emptyStrToNull2($button->getForm()->getValues('array'))
            );
            $this->redirect('this');
        };
        $resetButton->onClick[] = function (): void {
            $this->filterParams = []; //@phpstan-ignore-line
            $this->redirect('this');
        };
        $form->setDefaults($this->filterParams);
        return $control;
    }

    public function handleDeleteFilterParams(string $param): void
    {
        unset($this->filterParams[$param]);
        $this->redirect('this');
    }

    /**
     * @phpstan-return Button<TModel>
     * @phpstan-param array<string,string> $params
     */
    protected function addPresenterButton(
        string $destination,
        string $name,
        Title $label,
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
        /** @phpstan-ignore-next-line */
        return $this->addButton(
        /** @phpstan-ignore-next-line */
            new Button(
                $this->container,
                $this->getPresenter(),
                $label,
                fn(Model $model): array => [$destination, $paramMapCallback($model)],
                $className,
                fn(Model $model): bool => $checkACL ? $this->getPresenter()->authorized(
                    $destination,
                    $paramMapCallback($model)
                ) : true
            ),
            $name
        );
    }

    /**
     * @phpstan-return Button<TModel>
     * @throws BadTypeException
     * @deprecated
     */
    protected function addLink(string $linkId, bool $checkACL = false, ?string $className = null): Button
    {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        /** @phpstan-var Button<TModel> $button */
        $button = new Button(
            $this->container,
            $this->getPresenter(),
            $factory->title(),
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
