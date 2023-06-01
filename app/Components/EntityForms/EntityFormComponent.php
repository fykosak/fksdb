<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Database\ConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

abstract class EntityFormComponent extends FormComponent
{
    public function __construct(Container $container, protected readonly ?Model $model)
    {
        parent::__construct($container);
    }

    /**
     * @throws BadTypeException
     */
    public function render(): void
    {
        $this->setDefaults($this->getForm());
        parent::render();
    }

    final protected function handleSuccess(Form $form): void
    {
        try {
            $this->handleFormSuccess($form);
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $previous = $exception->getPrevious();
            // catch NotNull|ForeignKey|Unique
            if ($previous instanceof ConstraintViolationException) {
                $this->flashMessage($previous->getMessage(), Message::LVL_ERROR);
            } else {
                $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Error in the form') . ': ' . $exception->getMessage(), Message::LVL_ERROR);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', isset($this->model) ? _('Save') : _('Create'));
    }

    protected function configureForm(Form $form): void
    {
    }

    /**
     * @throws ModelException
     */
    abstract protected function handleFormSuccess(Form $form): void;

    abstract protected function setDefaults(Form $form): void;
}
