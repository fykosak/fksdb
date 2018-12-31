<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\FyziklaniTaskGrid;
use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use Nette\Application\UI\Form;

class TaskPresenter extends BasePresenter {

    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

    public function titleList() {
        $this->setTitle(_('Úlohy Fyziklání'));
        $this->setIcon('fa fa-tasks');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.task', 'list')));
    }

    public function titleImport() {
        $this->setTitle(_('Tasks Import of Fyziklani'));
        $this->setIcon('fa fa-upload');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedImport() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.task', 'import')));
    }

    /**
     * @return FormControl
     */
    public function createComponentTaskImportForm(): FormControl {
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
     * @throws \Nette\Application\BadRequestException
     */
    public function taskImportFormSucceeded(Form $form) {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this->getEvent(), $this->getServiceFyziklaniTask());
        $messages = [];
        $taskImportProcessor($values, $messages);
        foreach ($messages as $message) {
            $this->flashMessage($message[0], $message[1]);
        }
        $this->redirect('this');
    }

    /**
     * @return FyziklaniTaskGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentTaskGrid(): FyziklaniTaskGrid {
        return new FyziklaniTaskGrid($this->getEvent(), $this->getServiceFyziklaniTask());
    }
}
