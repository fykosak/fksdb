<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\ListComponent\Button\ControlButton;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
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

    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->setDefaultOrder('task.series DESC, tasknr ASC');
        $this->addColumn('task', _('Task'), fn(SubmitModel $submit): string => $submit->task->getFQName());
        $this->addColumn(
            'submitted_on',
            _('Timestamp'),
            fn(SubmitModel $model): string => $model->submitted_on->format('c')
        );
        $this->addColumn('source', _('Method of handing'), fn(SubmitModel $model): string => $model->source->value);

        $this->getButtonsContainer()->addComponent(
            new ControlButton(
                $this->container,
                $this,
                new Title(null, _('Cancel')),
                fn(SubmitModel $submit): array => ['revoke!', ['id' => $submit->submit_id]],
                'btn btn-sm btn-outline-warning',
                fn(SubmitModel $submit): bool => $submit->canRevoke()
            ),
            'revoke'
        );

        $this->getButtonsContainer()->addComponent(
            new ControlButton(
                $this->container,
                $this,
                new Title(null, _('Download original')),
                fn(SubmitModel $submit): array => ['downloadUploaded!', ['id' => $submit->submit_id]],
                null,
                fn(SubmitModel $submit): bool => !$submit->isQuiz()
            ),
            'download_uploaded'
        );

        $this->getButtonsContainer()->addComponent(
            new ControlButton(
                $this->container,
                $this,
                new Title(null, _('Download corrected')),
                fn(SubmitModel $submit): array => ['downloadCorrected!', ['id' => $submit->submit_id]],
                null,
                fn(SubmitModel $submit): bool => !$submit->isQuiz() && $submit->corrected
            ),
            'download_corrected'
        );
        $this->paginate = false;
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
