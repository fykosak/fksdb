<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Class AbstractEntityFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractEntityFormControl extends BaseComponent {

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
     * @throws BadRequestException
     */
    final public function createComponentFormControl(): FormControl {
        $control = $this->createFormControl();
        $this->configureForm($control->getForm());
        return $control;
    }

    /**
     * @return void
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . '@layout.latte');
        $this->template->render();
    }

    /**
     * @param Form $form
     * @return void
     */
    abstract protected function configureForm(Form $form);
}
