<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Components\React\ReactComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\React\ReactResponse;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use PublicModule\SubmitPresenter;
use ReactMessage;

/**
 * Class AjaxUpload
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read SubmitPresenter $presenter
 */
class AjaxUpload extends ReactComponent {
    use SubmitRevokeTrait;
    use SubmitSaveTrait;
    use SubmitDownloadTrait;

    /** @var ServiceSubmit */
    private $serviceSubmit;
    /** @var CorrectedStorage */
    private $correctedStorage;
    /** @var UploadedStorage */
    private $uploadedStorage;

    /**
     * @param ServiceSubmit $serviceSubmit
     * @param CorrectedStorage $correctedStorage
     * @param UploadedStorage $uploadedStorage
     * @return void
     */
    public function injectPrimary(ServiceSubmit $serviceSubmit, CorrectedStorage $correctedStorage, UploadedStorage $uploadedStorage) {
        $this->serviceSubmit = $serviceSubmit;
        $this->correctedStorage = $correctedStorage;
        $this->uploadedStorage = $uploadedStorage;
    }

    /**
     * @return void
     * @throws InvalidLinkException
     */
    protected function configure() {
        $this->addAction('revoke', $this->link('revoke!'));
        $this->addAction('upload', $this->link('upload!'));
        $this->addAction('download', $this->link('download!'));
        parent::configure();
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function getData(): string {
        $data = [];
        /**
         * @var ModelTask $task
         */
        foreach ($this->getPresenter()->getAvailableTasks() as $task) {
            $submit = $this->serviceSubmit->findByContestant($this->getPresenter()->getContestant()->ct_id, $task->task_id);
            $data[$task->task_id] = $this->serviceSubmit->serializeSubmit($submit, $task, $this->getPresenter());
        }
        return json_encode($data);
    }

    /**
     * @param bool $need
     * @return SubmitPresenter
     * @throws BadRequestException
     */
    public function getPresenter($need = TRUE) {
        $presenter = parent::getPresenter();
        if (!$presenter instanceof SubmitPresenter) {
            throw new BadTypeException(SubmitPresenter::class, $presenter);
        }
        return $presenter;
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     * @throws InvalidLinkException
     */
    public function handleUpload() {
        $response = new ReactResponse();

        $contestant = $this->getPresenter()->getContestant();
        $files = $this->getHttpRequest()->getFiles();
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->getUploadedStorage()->beginTransaction();
            if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                $response->addMessage(new ReactMessage(_('Task not found'), ILogger::WARNING));
                continue;
            }
            /** @var ModelTask $task */
            $task = $this->getPresenter()->getAvailableTasks()->where('task_id', $matches[1])->fetch();
            if (!$task) {

                $response->setCode(Response::S403_FORBIDDEN);
                $response->addMessage(new ReactMessage(_('Upload not allowed'), ILogger::ERROR));
                $this->getPresenter()->sendResponse($response);
            }
            /** @var FileUpload $file */
            $file = $fileContainer;
            if (!$file->isOk()) {
                $response->setCode(Response::S500_INTERNAL_SERVER_ERROR);
                $response->addMessage(new ReactMessage(_('File is not Ok'), ILogger::ERROR));
                $this->getPresenter()->sendResponse($response);
                return;
            }
            // store submit
            $submit = $this->traitSaveSubmit($file, $task, $contestant);
            $this->getUploadedStorage()->commit();

            $this->serviceSubmit->getConnection()->commit();
            $response->addMessage(new ReactMessage(_('Upload successful'), ILogger::SUCCESS));
            $response->setAct('upload');
            $response->setData($this->serviceSubmit->serializeSubmit($submit, $task, $this->getPresenter()));
            $this->getPresenter()->sendResponse($response);
        }
    }

    /**
     * @return void
     * @throws InvalidLinkException
     * @throws BadRequestException
     * @throws AbortException
     */
    public function handleRevoke() {
        $submitId = $this->getReactRequest()->requestData['submitId'];
        $logger = new MemoryLogger();
        $data = $this->traitHandleRevoke($logger, $submitId);
        $response = new ReactResponse();
        if ($data) {
            $response->setData($data);
        }
        $response->setMessages($logger->getMessages());
        $this->getPresenter()->sendResponse($response);
        die();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownload() {
        $submitId = $this->getReactRequest()->requestData['submitId'];
        $logger = new MemoryLogger();
        $this->traitHandleDownloadUploaded($logger, $submitId);
        die();
    }

    protected function getReactId(): string {
        return 'public.ajax-upload';
    }

    protected function getCorrectedStorage(): CorrectedStorage {
        return $this->correctedStorage;
    }

    protected function getUploadedStorage(): UploadedStorage {
        return $this->uploadedStorage;
    }

    protected function getServiceSubmit(): ServiceSubmit {
        return $this->serviceSubmit;
    }
}
