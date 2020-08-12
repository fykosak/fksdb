<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\FyziklaniTaskImportProcessor;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;

/**
 * Class TaskPresenter
 * *
 */
class TaskPresenter extends BasePresenter {

    const IMPORT_STATE_UPDATE_N_INSERT = 1;
    const IMPORT_STATE_REMOVE_N_INSERT = 2;
    const IMPORT_STATE_INSERT = 3;

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Tasks'), 'fa fa-tasks'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleImport(): void {
        $this->setPageTitle(new PageTitle(_('Tasks Import'), 'fa fa-upload'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.task', 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedImport(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.task', 'import'));
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentTaskImportForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addUpload('csvfile')->setRequired();
        $form->addSelect('state', _('Select action'), [
            self::IMPORT_STATE_UPDATE_N_INSERT => _('Update tasks and add in case does not exists.'),
            self::IMPORT_STATE_REMOVE_N_INSERT => _('Delete all tasks and insert new one.'),
            self::IMPORT_STATE_INSERT => _('Only add in case does not exists.'),
        ]);
        $form->addSubmit('import', _('Import'));
        $form->onSuccess[] = function (Form $form) {
            $this->taskImportFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws EventNotFoundException
     */
    private function taskImportFormSucceeded(Form $form): void {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor($this->getContext()->getByType(ServiceFyziklaniTask::class), $this->getEvent());
        $logger = new MemoryLogger();
        $taskImportProcessor->process($values, $logger);
        FlashMessageDump::dump($logger, $this);
        $this->redirect('this');
    }

    /**
     * @return TaskGrid
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TaskGrid {
        return new TaskGrid($this->getEvent(), $this->getContext());
    }
}
