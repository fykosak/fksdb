<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use Nette\Application\AbortException;
use Nette\Database\ConstraintViolationException;
use Nette\Forms\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Tracy\Debugger;

/**
 * Class AbstractEntityFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractEntityFormComponent extends BaseComponent {

    protected bool $create;


    /**
     * AbstractEntityFormControl constructor.
     * @param Container $container
     * @param bool $create
     */
    public function __construct(Container $container, bool $create) {
        parent::__construct($container);
        $this->create = $create;
    }

    protected function createFormControl(): FormControl {
        return new FormControl();
    }


    /**
     * @return Form
     * @throws BadTypeException
     */
    protected function getForm(): Form {
        $control = $this->getComponent('formControl');
        if (!$control instanceof FormControl) {
            throw new BadTypeException(FormControl::class, $control);
        }
        return $control->getForm();
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    final protected function createComponentFormControl(): FormControl {
        $control = $this->createFormControl();
        $this->configureForm($control->getForm());
        $this->appendSubmitButton($control->getForm())
            ->onClick[] = function (SubmitButton $button) {
            $this->handleSuccess($button);
        };
        return $control;
    }

    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     */
    private function handleSuccess(SubmitButton $button): void {
        try {
            $this->handleFormSuccess($button->getForm());
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $previous = $exception->getPrevious();
            // catch NotNull|ForeignKey|Unique
            if ($previous && $previous instanceof ConstraintViolationException) {
                $this->flashMessage($previous->getMessage(), Message::LVL_DANGER);
            } else {
                $this->flashMessage(_('Error when storing model'), Message::LVL_DANGER);
            }
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton {
        return $form->addSubmit('submit', $this->create ? _('Create') : _('Save'));
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws ModelException
     */
    abstract protected function handleFormSuccess(Form $form): void;

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . '@layout.latte');
        $this->template->render();
    }

    protected function configureForm(Form $form): void {
    }
}
