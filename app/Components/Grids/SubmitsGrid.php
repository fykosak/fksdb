<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * @phpstan-extends BaseGrid<SubmitModel,array{}>
 */
final class SubmitsGrid extends BaseGrid
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

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->contestant->getSubmits()->order('task.series DESC, tasknr ASC');
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = false;
        $this->filtered = false;

        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitModel $submit): string => $submit->task->getFullLabel(Language::from($this->translator->lang)),
                new Title(null, _('Task'))
            ),
            'task'
        );
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitModel $model): string => $model->submitted_on->format(_('__date_time')),
                new Title(null, _('Submitted on'))
            ),
            'submitted_on'
        );
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitModel $model): Html => $model->source->badge(),
                new Title(null, _('Source'))
            ),
            'source'
        );

        $this->addTableButton(
            new Button(
                $this->container,
                $this,
                new Title(null, _('Cancel')),
                fn(SubmitModel $submit): array => ['revoke!', ['id' => $submit->submit_id]],
                'btn btn-sm me-1 btn-outline-warning',
                fn(SubmitModel $submit): bool => $submit->canRevoke()
            ),
            'revoke'
        );

        $this->addTableButton(
            new Button(
                $this->container,
                $this,
                new Title(null, _('Download original')),
                fn(SubmitModel $submit): array => ['downloadUploaded!', ['id' => $submit->submit_id]],
                null,
                fn(SubmitModel $submit): bool => !$submit->isQuiz()
            ),
            'download_uploaded'
        );

        $this->addTableButton(
            new Button(
                $this->container,
                $this,
                new Title(null, _('Download corrected')),
                fn(SubmitModel $submit): array => ['downloadCorrected!', ['id' => $submit->submit_id]],
                null,
                fn(SubmitModel $submit): bool => !$submit->isQuiz() && $submit->corrected
            ),
            'download_corrected'
        );

        $this->addTableButton(
            new Button(
                $this->container,
                $this->getPresenter(),
                new Title(null, _('button.detail')),
                fn(SubmitModel $submit): array => [':Public:Submit:quizDetail', ['id' => $submit->submit_id]],
                null,
                fn(SubmitModel $submit): bool => $submit->isQuiz()
            ),
            'show_quiz_detail'
        );
    }

    public function handleRevoke(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleRevoke($submit);
            $this->flashMessage(
                sprintf(
                    _('Submitting of task %s cancelled.'),
                    $submit->task->getFullLabel(Language::from($this->translator->lang))
                ),
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
