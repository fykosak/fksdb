<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use Tracy\Debugger;

class SubmitsGrid extends BaseGrid
{

    private ContestantModel $contestant;
    private SubmitHandlerFactory $submitHandlerFactory;

    public function __construct(Container $container, ContestantModel $contestant)
    {
        parent::__construct($container);
        $this->contestant = $contestant;
    }

    final public function injectPrimary(SubmitHandlerFactory $submitHandlerFactory): void
    {
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    protected function getData(): IDataSource
    {
        return new NDataSource($this->contestant->getSubmits());
    }

    /**
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->setDefaultOrder('series DESC, tasknr ASC');
        $this->addColumn('task', _('Task'))
            ->setRenderer(fn(SubmitModel $submit): string => $submit->task->getFQName());
        $this->addColumn('submitted_on', _('Timestamp'));
        $this->addColumn('source', _('Method of handing'))
            ->setRenderer(fn(SubmitModel $model): string => $model->source->value);

        $this->addButton('revoke', _('Cancel'))
            ->setClass('btn btn-sm btn-outline-warning')
            ->setText(_('Cancel'))
            ->setShow(fn(SubmitModel $submit): bool => $submit->canRevoke())
            ->setLink(fn(SubmitModel $submit): string => $this->link('revoke!', $submit->submit_id))
            ->setConfirmationDialog(fn(SubmitModel $submit): string => sprintf(
                _('Do you really want to take the solution of task %s back?'),
                $submit->task->getFQName()
            ));
        $this->addButton('download_uploaded')
            ->setText(_('Download original'))->setLink(
                fn(SubmitModel $submit): string => $this->link('downloadUploaded!', $submit->submit_id)
            )
            ->setShow(fn(SubmitModel $submit): bool => !$submit->isQuiz());
        $this->addButton('download_corrected')
            ->setText(_('Download corrected'))->setLink(
                fn(SubmitModel $submit): string => $this->link('downloadCorrected!', $submit->submit_id)
            )->setShow(fn(SubmitModel $submit): bool => !$submit->isQuiz() && $submit->corrected);

        $this->paginate = false;
        $this->enableSorting = false;
    }

    public function handleRevoke(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleRevoke($submit);
            $this->flashMessage(
                sprintf(_('Submitting of task %s cancelled.'), $submit->task->getFQName()),
                Message::LVL_WARNING
            );
        } catch (ForbiddenRequestException | NotFoundException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (StorageException | ModelException$exception) {
            Debugger::log($exception);
            $this->flashMessage(_('There was an error during the deletion of task %s.'), Message::LVL_ERROR);
        }
    }

    /**
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    /**
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadCorrected($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }
}
