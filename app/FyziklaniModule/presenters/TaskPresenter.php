<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;
use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;
use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use Nette\Application\UI\Form;

class TaskPresenter extends BasePresenter {

    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

    public function titleTable() {
        $this->setTitle(_('Úlohy FYKOSího Fyziklání'));
        $this->setIcon('fa fa-tasks');
    }

    public function authorizedTable() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'task')));
    }

    public function titleImport() {
        $this->setTitle(_('Import úloh FYKOSího Fyziklání'));
        $this->setIcon('fa fa-upload');
    }

    public function authorizedImport() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'taskImport')));
    }

    public function createComponentTaskImportForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addUpload('csvfile')->setRequired();
        $form->addSelect('state', _('Vyberte akci'), [
            self::IMPORT_STATE_UPDATE_N_INSERT => _('Updatovat úlohy a přidat pokud neexistuje'),
            self::IMPORT_STATE_REMOVE_N_INSERT => _('Odstranit všechny úlohy a nahrát nové'),
            self::IMPORT_STATE_INSERT => _('Přidat pokud neexistuje')
        ]);
        $form->addSubmit('import', _('Importovat'));
        $form->onSuccess[] = [$this, 'taskImportFormSucceeded'];
        return $control;
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function taskImportFormSucceeded(Form $form) {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this->getEventId(), $this->serviceFyziklaniTask);
        $messages = [];
        $taskImportProcessor($values, $messages);
        foreach ($messages as $message) {
            $this->flashMessage($message[0], $message[1]);
        }
        $this->redirect('this');
    }

    public function createComponentTaskGrid() {
        return new FyziklaniTaskGrid($this->getEventId(), $this->serviceFyziklaniTask);
    }
}
