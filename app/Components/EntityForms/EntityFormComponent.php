<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Database\ConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @phpstan-template TModel of Model
 */
abstract class EntityFormComponent extends FormComponent
{
    /**
     * @phpstan-var TModel|null
     */
    protected ?Model $model;

    /**
     * @phpstan-param TModel|null $model
     */
    public function __construct(Container $container, ?Model $model)
    {
        parent::__construct($container);
        $this->model = $model;
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

    abstract protected function configureForm(Form $form): void;

    /**
     * @throws \PDOException
     */
    abstract protected function handleSuccess(Form $form): void;

    abstract protected function setDefaults(Form $form): void;
}
