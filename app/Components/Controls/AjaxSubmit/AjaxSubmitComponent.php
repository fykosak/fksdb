<?php

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\React\AjaxComponent;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use Tracy\Debugger;

class AjaxSubmitComponent extends AjaxComponent
{

    private ServiceSubmit $serviceSubmit;
    private ModelTask $task;
    private ModelContestant $contestant;
    private SubmitHandlerFactory $submitHandlerFactory;

    public function __construct(Container $container, ModelTask $task, ModelContestant $contestant)
    {
        parent::__construct($container, 'public.ajax-submit');
        $this->task = $task;
        $this->contestant = $contestant;
    }

    final public function injectPrimary(ServiceSubmit $serviceSubmit, SubmitHandlerFactory $submitHandlerFactory): void
    {
        $this->serviceSubmit = $serviceSubmit;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    /**
     * @throws NotFoundException
     */
    private function getSubmit(bool $throw = false): ?ModelSubmit
    {
        $submit = $this->serviceSubmit->findByContestant($this->contestant, $this->task, false);
        if ($throw && is_null($submit)) {
            throw new NotFoundException(_('Submit not found'));
        }
        return $submit;
    }

    /**
     * @throws InvalidLinkException
     */
    protected function getActions(): array
    {
        /* if ($this->getSubmit()) {
             return [
                 'revoke' => $this->link('revoke!'),
                 'download' => $this->link('download!'),
             ];
         } else {
             return [
                 'upload' => $this->link('upload!'),
             ];
         }*/
        return [
            'revoke' => $this->link('revoke!'),
            'download' => $this->link('download!'),
            'upload' => $this->link('upload!'),
        ];
    }

    /**
     * @throws NotFoundException
     */
    protected function getData(): array
    {
        $studyYear = $this->submitHandlerFactory->getUserStudyYear($this->contestant);
        return ServiceSubmit::serializeSubmit($this->getSubmit(), $this->task, $studyYear);
    }

    /**
     * @throws StorageException
     */
    public function handleUpload(): void
    {
        $files = $this->getHttpRequest()->getFiles();
        /** @var FileUpload $fileContainer */
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->explorer->getConnection()->beginTransaction();
            $this->submitHandlerFactory->uploadedStorage->beginTransaction();
            if ($name !== 'submit') {
                continue;
            }

            if (!$fileContainer->isOk()) {
                $this->getLogger()->log(new Message(_('File is not Ok'), Message::LVL_ERROR));
                $this->sendAjaxResponse(Response::S500_INTERNAL_SERVER_ERROR);
            }
            // store submit
            $this->submitHandlerFactory->handleSave($fileContainer, $this->task, $this->contestant);
            $this->submitHandlerFactory->uploadedStorage->commit();
            $this->serviceSubmit->explorer->getConnection()->commit();
            $this->getLogger()->log(new Message(_('Upload successful'), Message::LVL_SUCCESS));
            $this->sendAjaxResponse();
        }
    }

    public function handleRevoke(): void
    {
        try {
            $submit = $this->getSubmit(true);
            $this->submitHandlerFactory->handleRevoke($submit);
            $this->getLogger()->log(new Message(\sprintf(_('Uploading of task %s cancelled.'), $submit->getTask()->getFQName()), Message::LVL_ERROR));
        } catch (ForbiddenRequestException | NotFoundException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        } catch (StorageException | ModelException$exception) {
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
