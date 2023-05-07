<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Forms\Form;

class FastEditComponent extends BaseComponent
{
    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'edit.latte');
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $form->elementPrototype->target('_blank');
        $form->addText('id', _('Team Id'))->setHtmlType('number');
        $form->addSubmit('edit', _('Edit'));

        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues('array');
            $this->getPresenter()->redirect('edit', ['id' => +$values['id']]);
        };
        return $control;
    }
}
