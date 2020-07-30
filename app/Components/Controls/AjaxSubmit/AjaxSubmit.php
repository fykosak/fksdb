<?php

namespace FKSDB\Components\Control\AjaxSubmit;

use FKSDB\Components\React\AjaxComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\StorageException;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use FKSDB\Modules\PublicModule\SubmitPresenter;
use Tracy\Debugger;

/**
 * Class TaskUpload
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read SubmitPresenter $presenter
 */
class AjaxSubmit extends AjaxComponent {

    /** @var ServiceSubmit */
    private $serviceSubmit;

    /** @var UploadedStorage */
    private $uploadedStorage;

    /** @var TypedTableSelection */
    private $task;

    /**@var ModelContestant */
    private $contestant;

    /**@var SubmitHandlerFactory */
    private $submitHandlerFactory;

    /** @var int */
    private $academicYear;

    /**
     * TaskUpload constructor.
     * @param Container $container
     * @param ModelTask $task
     * @param ModelContestant $contestant
     * @param int $academicYear
     */
    public function __construct(Container $container, ModelTask $task, ModelContestant $contestant, int $academicYear) {
        parent::__construct($container, 'public.ajax-submit');
        $this->task = $task;
        $this->contestant = $contestant;
        $this->academicYear = $academicYear;
    }

    /**
     * @param ServiceSubmit $serviceSubmit
     * @param UploadedStorage $uploadedStorage
     * @param SubmitHandlerFactory $submitHandlerFactory
     * @return void
     */
    public function injectPrimary(ServiceSubmit $serviceSubmit, UploadedStorage $uploadedStorage, SubmitHandlerFactory $submitHandlerFactory) {
        $this->serviceSubmit = $serviceSubmit;
        $this->uploadedStorage = $uploadedStorage;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    /**
     * @param bool $throw
     * @return ModelSubmit|null
     * @throws NotFoundException
     */
    private function getSubmit(bool $throw = false) {
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
     * @return mixed
     * @throws NotFoundException
     */
    protected function getData() {
        $studyYear = $this->submitHandlerFactory->getUserStudyYear($this->academicYear);
        return ServiceSubmit::serializeSubmit($this->getSubmit(), $this->task, $studyYear);
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     */
    public function handleUpload() {
        $files = $this->getHttpRequest()->getFiles();
        /** @var FileUpload $fileContainer */
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->uploadedStorage->beginTransaction();
            if ($name !== 'submit') {
                continue;
            }

            if (!$fileContainer->isOk()) {
                $this->getLogger()->log(new Message(_('File is not Ok'), ILogger::ERROR));
                $this->sendAjaxResponse(Response::S500_INTERNAL_SERVER_ERROR);
            }
            // store submit
            $this->submitHandlerFactory->handleSave($fileContainer, $this->task, $this->contestant);
            $this->uploadedStorage->commit();
            $this->serviceSubmit->getConnection()->commit();
            $this->getLogger()->log(new Message(_('Upload successful'), ILogger::SUCCESS));
            $this->sendAjaxResponse();
        }
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleRevoke() {
        try {
            $this->submitHandlerFactory->handleRevokeSubmit($this->getLogger(), $this->getSubmit(true), $this->academicYear);
        } catch (ForbiddenRequestException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (NotFoundException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (StorageException$exception) {
            Debugger::log($exception);
            $this->getLogger()->log(new Message(_('Během mazání úlohy došlo k chybě.'), Message::LVL_DANGER));
        } catch (ModelException $exception) {
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
    public function handleDownload() {
        try {
            $this->submitHandlerFactory->handleDownloadUploadedSubmit($this->getPresenter(), $this->getSubmit(true));
        } catch (ForbiddenRequestException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (NotFoundException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (StorageException$exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_DANGER));
        }
        $this->sendAjaxResponse();
    }
}
