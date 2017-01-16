<?php

namespace FyziklaniModule;

use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Application\UI\Form;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;

class TaskPresenter extends BasePresenter {

    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

    public function titleTable() {
        $this->setTitle(_('Úlohy FYKOSího Fyziklání'));
    }

    public function authorizedTable() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'task')));
    }

    public function titleImport() {
        $this->setTitle(_('Import úloh FYKOSího Fyziklání'));
    }

    public function authorizedImport() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'taskImport')));
    }

    public function createComponentTaskImportForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);
        $form->addUpload('csvfile')->setRequired();
        $form->addSelect('state', _('Vyberte akci'), [
            self::IMPORT_STATE_UPDATE_N_INSERT => _('Updatovat úlohy a přidat pokud neexistuje'),
            self::IMPORT_STATE_REMOVE_N_INSERT => _('Odstranit všechny úlohy a nahrát nové'),
            self::IMPORT_STATE_INSERT => _('Přidat pokud neexistuje')
        ]);
        $form->addSubmit('import', _('Importovat'));
        $form->onSuccess[] = [$this, 'taskImportFormSucceeded'];
        return $form;
    }

    public function taskImportFormSucceeded(Form $form) {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this->eventID, $this->serviceFyziklaniTask);
        $messages = [];
        $taskImportProcessor($values, $messages);
        foreach ($messages as $message) {
            $this->flashMessage($message[0], $message[1]);
        }
        $this->redirect('this');
    }

    public function createComponentTaskGrid() {
        return new FyziklaniTaskGrid($this->eventID, $this->serviceFyziklaniTask);
    }
}
