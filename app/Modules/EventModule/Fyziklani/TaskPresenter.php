<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\TaskGrid;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Fyziklani\FyziklaniTaskImportProcessor;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Form;
use Nette\DI\MissingServiceException;

class TaskPresenter extends BasePresenter
{

    public const IMPORT_STATE_UPDATE_N_INSERT = 1;
    public const IMPORT_STATE_REMOVE_N_INSERT = 2;
    public const IMPORT_STATE_INSERT = 3;

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Tasks'), 'fas fa-tasks');
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(_('Tasks Import'), 'fas fa-download');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void
    {
        $this->setAuthorized($this->isEventOrContestOrgAuthorized('fyziklani.task', 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedImport(): void
    {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.task', 'import'));
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentTaskImportForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $form->addUpload('csvfile')->setRequired();
        $form->addSelect(
            'state',
            _('Select action'),
            [
                self::IMPORT_STATE_UPDATE_N_INSERT => _('Update tasks and add in case does not exist.'),
                self::IMPORT_STATE_REMOVE_N_INSERT => _('Delete all tasks and insert new one.'),
                self::IMPORT_STATE_INSERT => _('Only add in case does not exists.'),
            ]
        );
        $form->addSubmit('import', _('Import'));
        $form->onSuccess[] = function (Form $form) {
            $this->taskImportFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @throws EventNotFoundException
     * @throws MissingServiceException
     */
    private function taskImportFormSucceeded(Form $form): void
    {
        $values = $form->getValues();
        $taskImportProcessor = new FyziklaniTaskImportProcessor(
            $this->getContext()->getByType(ServiceFyziklaniTask::class),
            $this->getEvent(),
        );
        $logger = new MemoryLogger();
        $taskImportProcessor->process($values, $logger);
        FlashMessageDump::dump($logger, $this);
        $this->redirect('this');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): TaskGrid
    {
        return new TaskGrid($this->getEvent(), $this->getContext());
    }
}
