<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\model\Fyziklani\FyziklaniTaskImportProcessor;
use Nette\Application\UI\Form;

/**
 * Class TaskPresenter
 * @package FyziklaniModule
 */
class TaskPresenter extends BasePresenter {

    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

    public function titleList() {
        $this->setTitle(_('Tasks'));
        $this->setIcon('fa fa-tasks');
    }

    public function titleImport() {
        $this->setTitle(_('Tasks Import'));
        $this->setIcon('fa fa-upload');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.task', 'list')));
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
        $form->onSuccess[] = function (Form $form) {
            $this->taskImportFormSucceeded($form);
        };
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
     * @return TaskGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentGrid(): TaskGrid {
        return $this->fyziklaniComponentsFactory->createTasksGrid($this->getEvent());
    }
}
