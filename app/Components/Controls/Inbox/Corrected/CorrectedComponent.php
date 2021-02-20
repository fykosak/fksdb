<?php

namespace FKSDB\Components\Controls\Inbox\Corrected;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Logging\Logger;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;

/**
 * Class CorrectedControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CorrectedComponent extends SeriesTableComponent {

    private CorrectedStorage $correctedStorage;

    final public function injectCorrectedStorage(CorrectedStorage $correctedStorage): void {
        $this->correctedStorage = $correctedStorage;
    }

    public function render(): void {
        $this->template->correctedSubmitStorage = $this->correctedStorage;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @return FormControl
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

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleSuccess(Form $form): void {
        $values = $form->getValues();
        $ids = [];
        foreach (\explode(',', $values['submits']) as $value) {
            $ids[] = trim($value);
        }
        try {
            $updated = $this->getSeriesTable()->getSubmits()->where('submit_id', $ids)->update(['corrected' => 1]);
            $this->flashMessage(\sprintf(_('Updated %d submits'), $updated), Logger::INFO);
        } catch (\PDOException $exception) {
            $this->flashMessage(_('Error during updating'), Logger::ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
