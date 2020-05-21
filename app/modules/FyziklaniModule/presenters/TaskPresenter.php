<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\Fyziklani\FyziklaniTaskImportProcessor;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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
        $this->setTitle(_('Tasks'), 'fa fa-tasks');
    }

    public function titleImport() {
        $this->setTitle(_('Tasks Import'), 'fa fa-upload');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.task', 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedImport() {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.task', 'import'));
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentTaskImportForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addUpload('csvfile')->setRequired();
        $form->addSelect('state', _('Select action'), [
            self::IMPORT_STATE_UPDATE_N_INSERT => _('Update tasks and add in case does not exists.'),
            self::IMPORT_STATE_REMOVE_N_INSERT => _('Delete all tasks and insert new one.'),
            self::IMPORT_STATE_INSERT => _('Only add in case does not exists.')
        ]);
        $form->addSubmit('import', _('Import'));
        $form->onSuccess[] = function (Form $form) {
            $this->taskImportFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws BadRequestException
     */
    public function taskImportFormSucceeded(Form $form) {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this->getContext(), $this->getEvent());
        $logger = new MemoryLogger();
        $taskImportProcessor($values, $logger);
        FlashMessageDump::dump($logger, $this);
        $this->redirect('this');
    }

    /**
     * @return TaskGrid
     * @throws BadRequestException
     */
    public function createComponentGrid(): TaskGrid {
        return new TaskGrid($this->getEvent(), $this->getContext());
    }
}
