<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\FormComponent;
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
abstract class AbstractEntityFormComponent extends FormComponent {

    protected bool $create;

    public function __construct(Container $container, bool $create) {
        parent::__construct($container);
        $this->create = $create;
    }

    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     */
    final protected function handleSuccess(SubmitButton $button): void {
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

    protected function configureForm(Form $form): void {
    }
}
