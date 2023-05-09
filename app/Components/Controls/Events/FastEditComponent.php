<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
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
        $form->addText('code', _('Team Id'));
        $form->addCheckbox('bypass', _('Bypass checksum'));
        $form->addSubmit('edit', _('Edit'));

        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues('array');
            try {
                if ($values['bypass']) {
                    $id = +$values['code'];
                } else {
                    $id = AttendanceCode::checkCode($this->container, $values['code']);
                }
            } catch (ForbiddenRequestException$exception) {
                $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
                $this->getPresenter()->redirect('this');
            }
            $this->getPresenter()->redirect('edit', ['id' => $id]);
        };
        return $control;
    }
}
