<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Upload\AjaxSubmit;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\IResponse;
use Tracy\Debugger;

/**
 * @property GettextTranslator $translator
 */
class AjaxSubmitComponent extends AjaxComponent
{
    private TaskModel $task;
    private ContestantModel $contestant;
    private SubmitHandlerFactory $submitHandlerFactory;

    public function __construct(Container $container, TaskModel $task, ContestantModel $contestant)
    {
        parent::__construct($container, 'public.ajax-submit');
        $this->task = $task;
        $this->contestant = $contestant;
    }

    final public function injectPrimary(SubmitHandlerFactory $submitHandlerFactory): void
    {
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    /**
     * @throws NotFoundException
     */
    private function getSubmit(bool $throw = false): ?SubmitModel
    {
        /** @var SubmitModel|null $submit */
        $submit = $this->contestant->getSubmits()->where('task_id', $this->task)->fetch();
        if ($throw && is_null($submit)) {
            throw new NotFoundException(_('Submit not found'));
        }
        return $submit;
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure(): void
    {
        $this->addAction('revoke', 'revoke!');
        $this->addAction('download', 'download!');
        $this->addAction('upload', 'upload!');
        $this->addPresenterLink('quiz', ':Quiz', ['id' => $this->task->task_id]);
    }

    /**
     * @phpstan-return array{
     *     submitId:int|null,
     *     name:array<string,string>,
     *     deadline:string|null,
     *     taskId:int,
     *     isQuiz:bool,
     *     disabled:bool,
     * }
     * @throws NotFoundException
     */
    protected function getData(): array
    {
        $submit = $this->getSubmit();
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $this->task->name->__serialize(),
            'deadline' => $this->task->submit_deadline ? sprintf(
                _('Deadline %s'),
                $this->task->submit_deadline->format(_('__date_time'))
            ) : null,
            'taskId' => $this->task->task_id,
            'isQuiz' => count($this->task->getQuestions()) > 0,
            'disabled' => !$this->task->isForCategory($this->contestant->contest_category),
        ];
    }

    /**
     * @throws StorageException
     */
    public function handleUpload(): void
    {
        $files = $this->getHttpRequest()->getFiles();
        /** @var FileUpload $fileContainer */
        foreach ($files as $name => $fileContainer) {
            $this->submitHandlerFactory->submitService->explorer->getConnection()->beginTransaction();
            $this->submitHandlerFactory->uploadedStorage->beginTransaction();
            if ($name !== 'submit') {
                continue;
            }

            if (!$fileContainer->isOk()) {
                $this->getLogger()->log(new Message(_('File is not Ok'), Message::LVL_ERROR));
                $this->sendAjaxResponse(IResponse::S500_INTERNAL_SERVER_ERROR);
            }
            // store submit
            $this->submitHandlerFactory->handleSave($fileContainer, $this->task, $this->contestant);
            $this->submitHandlerFactory->uploadedStorage->commit();
            $this->submitHandlerFactory->submitService->explorer->getConnection()->commit();
            $this->getLogger()->log(new Message(_('Upload successful'), Message::LVL_SUCCESS));
            $this->sendAjaxResponse();
        }
    }

    public function handleRevoke(): void
    {
        try {
            $submit = $this->getSubmit(true);
            $this->submitHandlerFactory->handleRevoke($submit);
            $this->getLogger()->log(
                new Message(
                    \sprintf(
                        _('Uploading of task %s cancelled.'),
                        $submit->task->getFullLabel(Language::from($this->translator->lang))
                    ),
                    Message::LVL_ERROR
                )
            );
        } catch (ForbiddenRequestException | NotFoundException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        } catch (StorageException | \PDOException $exception) {
            Debugger::log($exception);
            $this->getLogger()->log(new Message(_('There was an error during the task deletion.'), Message::LVL_ERROR));
        }

        $this->sendAjaxResponse();
    }

    /**
     * @throws BadRequestException
     */
    public function handleDownload(): void
    {
        try {
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $this->getSubmit(true));
        } catch (ForbiddenRequestException | StorageException | NotFoundException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        }
        $this->sendAjaxResponse();
    }
}
