<?php

namespace FKSDB\Components\Controls\Inbox\Corrected;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;
use Fykosak\Utils\Logging\Message;
use Nette\Application\UI\Form;

class CorrectedComponent extends SeriesTableComponent {

    private CorrectedStorage $correctedStorage;

    final public function injectCorrectedStorage(CorrectedStorage $correctedStorage): void {
        $this->correctedStorage = $correctedStorage;
    }

    final public function render(): void {
        $this->template->correctedSubmitStorage = $this->correctedStorage;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addTextArea('submits', _('Submits'))->setOption('description', _('Comma separated submitIDs'));
        $form->addSubmit('submit', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleSuccess($form);
        };
        return $control;
    }

    private function handleSuccess(Form $form): void {
        $values = $form->getValues();
        $ids = [];
        foreach (\explode(',', $values['submits']) as $value) {
            $ids[] = trim($value);
        }
        try {
            $updated = $this->getSeriesTable()->getSubmits()->where('submit_id', $ids)->update(['corrected' => 1]);
            $this->flashMessage(\sprintf(_('Updated %d submits'), $updated), Message::LVL_INFO);
        } catch (\PDOException $exception) {
            $this->flashMessage(_('Error during updating'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
