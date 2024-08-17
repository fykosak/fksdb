<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\EntityForms\Processing\Postprocessing;
use FKSDB\Components\EntityForms\Processing\Preprocessing;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Database\Connection;
use Nette\Database\ConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TData of array
 */
abstract class ModelForm extends FormComponent
{
    /**
     * @phpstan-var TModel|null
     */
    protected ?Model $model;
    protected Connection $connection;
    /**
     * @phpstan-param TModel|null $model
     */
    public function __construct(Container $container, ?Model $model)
    {
        parent::__construct($container);
        $this->model = $model;
    }

    public function injectConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    public function render(): void
    {
        $this->setDefaults($this->getForm());
        parent::render();
    }

    protected function onException(\Throwable $exception): bool
    {
        if ($exception instanceof \PDOException) {
            Debugger::log($exception, Debugger::EXCEPTION);
            $previous = $exception->getPrevious();
            // catch NotNull|ForeignKey|Unique
            if ($previous instanceof ConstraintViolationException) {
                $this->flashMessage($previous->getMessage(), Message::LVL_ERROR);
            } else {
                $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            }
            return true;
        }
        return false;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', isset($this->model) ? _('Save') : _('Create'));
    }

    abstract protected function setDefaults(Form $form): void;

    /**
     * @throws \Throwable
     */
    final protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var TData $values */
        $values = $form->getValues('array');
        $model = $this->connection->transaction(function () use ($form, $values): Model {
            $values = array_reduce(
                $this->getPreprocessing(),
                fn(array $prevValue, callable $item): array => $item(
                    $prevValue,
                    $form,
                    $this->model
                ),
                $values
            );
            $model = $this->innerSuccess($values, $form);
            foreach ($this->getPostprocessing() as $postprocessing) {
                $postprocessing($model);
            }
            return $model;
        });
        $this->successRedirect($model);
    }

    /**
     * @phpstan-return ((callable(TData,Form,TModel|null):TData)|Preprocessing<TModel,TData>)[]
     */
    protected function getPreprocessing(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            fn(array $values, Form $form, ?Model $model): array => FormUtils::emptyStrToNull2($values),
        ];
    }

    /**
     * @phpstan-return ((callable(TModel):void)|Postprocessing<TModel>)[]
     */
    protected function getPostprocessing(): array
    {
        return [];
    }

    /**
     * @phpstan-param TData $values
     * @phpstan-return TModel
     */
    abstract protected function innerSuccess(array $values, Form $form): Model;


    /**
     * @phpstan-param TModel $model
     */
    abstract protected function successRedirect(Model $model): void;
}
