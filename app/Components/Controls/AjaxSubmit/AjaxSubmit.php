<?php

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\React\AjaxComponent;
use FKSDB\Model\Exceptions\ModelException;
use FKSDB\Model\Exceptions\NotFoundException;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\Messages\Message;
use FKSDB\Model\ORM\Models\ModelContestant;
use FKSDB\Model\ORM\Models\ModelSubmit;
use FKSDB\Model\ORM\Models\ModelTask;
use FKSDB\Model\ORM\Services\ServiceSubmit;
use FKSDB\Model\Submits\StorageException;
use FKSDB\Model\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use Tracy\Debugger;

/**
 * Class TaskUpload
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AjaxSubmit extends AjaxComponent {

    private ServiceSubmit $serviceSubmit;
    private ModelTask $task;
    private ModelContestant $contestant;
    private SubmitHandlerFactory $submitHandlerFactory;
    private int $academicYear;

    public function __construct(Container $container, ModelTask $task, ModelContestant $contestant, int $academicYear) {
        parent::__construct($container, 'public.ajax-submit');
        $this->task = $task;
        $this->contestant = $contestant;
        $this->academicYear = $academicYear;
    }

    final public function injectPrimary(ServiceSubmit $serviceSubmit, SubmitHandlerFactory $submitHandlerFactory): void {
        $this->serviceSubmit = $serviceSubmit;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    /**
     * @param bool $throw
     * @return ModelSubmit|null
     * @throws NotFoundException
     */
    private function getSubmit(bool $throw = false): ?ModelSubmit {
        $submit = $this->serviceSubmit->findByContestant($this->contestant->ct_id, $this->task->task_id, false);
        if ($throw && is_null($submit)) {
            throw new NotFoundException(_('Submit not found'));
        }
        return $submit;
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getActions(): array {
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
     * @return array
     * @throws NotFoundException
     */
    protected function getData(): array {
        $studyYear = $this->submitHandlerFactory->getUserStudyYear($this->contestant, $this->academicYear);
        return ServiceSubmit::serializeSubmit($this->getSubmit(), $this->task, $studyYear);
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleUpload(): void {
        $files = $this->getHttpRequest()->getFiles();
        /** @var FileUpload $fileContainer */
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->submitHandlerFactory->uploadedStorage->beginTransaction();
            if ($name !== 'submit') {
                continue;
            }

            if (!$fileContainer->isOk()) {
                $this->getLogger()->log(new Message(_('File is not Ok'), ILogger::ERROR));
                $this->sendAjaxResponse(Response::S500_INTERNAL_SERVER_ERROR);
            }
            // store submit
            $this->submitHandlerFactory->handleSave($fileContainer, $this->task, $this->contestant);
            $this->submitHandlerFactory->uploadedStorage->commit();
            $this->serviceSubmit->getConnection()->commit();
            $this->getLogger()->log(new Message(_('Upload successful'), ILogger::SUCCESS));
            $this->sendAjaxResponse();
        }
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleRevoke(): void {
        try {
            $submit = $this->getSubmit(true);
            $this->submitHandlerFactory->handleRevoke($submit);
            $this->getLogger()->log(new Message(\sprintf(_('Odevzdání úlohy %s zrušeno.'), $submit->getTask()->getFQName()), ILogger::WARNING));
        } catch (ForbiddenRequestException | NotFoundException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (StorageException | ModelException$exception) {
            Debugger::log($exception);
            $this->getLogger()->log(new Message(_('Během mazání úlohy došlo k chybě.'), Message::LVL_DANGER));
        }

        $this->sendAjaxResponse();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownload(): void {
        try {
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $this->getSubmit(true));
        } catch (ForbiddenRequestException | StorageException | NotFoundException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        }
        $this->sendAjaxResponse();
    }
}
