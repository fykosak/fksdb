<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use Fykosak\Utils\Logging\Message;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\MessageLevel;
use Nette\Application\AbortException;
use Nette\Database\ConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

abstract class AbstractEntityFormComponent extends FormComponent
{

    protected ?AbstractModel $model;

    public function __construct(Container $container, ?AbstractModel $model)
    {
        parent::__construct($container);
        $this->model = $model;
    }

    final public function render(): void
    {
        $this->setDefaults();
        parent::render();
    }

    final protected function isCreating(): bool
    {
        return !isset($this->model);
    }

    final protected function handleSuccess(SubmitButton $button): void
    {
        try {
            $this->handleFormSuccess($button->getForm());
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $previous = $exception->getPrevious();
            // catch NotNull|ForeignKey|Unique
            if ($previous instanceof ConstraintViolationException) {
                $this->flashMessage($previous->getMessage(), MessageLevel::ERROR->value);
            } else {
                $this->flashMessage(_('Error when storing model'), MessageLevel::ERROR->value);
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error'), MessageLevel::ERROR->value);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', $this->isCreating() ? _('Create') : _('Save'));
    }

    protected function configureForm(Form $form): void
    {
    }

    /**
     * @throws ModelException
     */
    abstract protected function handleFormSuccess(Form $form): void;

    abstract protected function setDefaults(): void;
}
