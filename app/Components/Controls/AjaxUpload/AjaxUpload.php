<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Components\React\ReactComponent;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\React\ReactResponse;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use FKSDB\Modules\PublicModule\SubmitPresenter;
use ReactMessage;

/**
 * Class AjaxUpload
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read SubmitPresenter $presenter
 */
class AjaxUpload extends ReactComponent {

    /** @var ServiceSubmit */
    private $serviceSubmit;
    /** @var UploadedStorage */
    private $uploadedStorage;
    /**
     * @var TypedTableSelection
     */
    private $availableTasks;
    /**
     * @var ModelContestant
     */
    private $contestant;
    /**
     * @var SubmitHandlerFactory
     */
    private $submitHandlerFactory;

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
     * AjaxUpload constructor.
     * @param Container $container
     * @param TypedTableSelection $availableTasks
     * @param ModelContestant $contestant
     */
    public function __construct(Container $container, TypedTableSelection $availableTasks, ModelContestant $contestant) {
        parent::__construct($container);
        $this->availableTasks = $availableTasks;
        $this->contestant = $contestant;
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
     * @throws InvalidLinkException
     */
    public function getData(): string {
        $data = [];
        /**
         * @var ModelTask $task
         */
        foreach ($this->availableTasks as $task) {
            $submit = $this->serviceSubmit->findByContestant($this->contestant->ct_id, $task->task_id);
            $data[$task->task_id] = ServiceSubmit::serializeSubmit($submit, $task, $this->getPresenter());
        }
        return json_encode($data);
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

        $files = $this->getHttpRequest()->getFiles();
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->uploadedStorage->beginTransaction();
            if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                $response->addMessage(new ReactMessage(_('Task not found'), ILogger::WARNING));
                continue;
            }
            $task = $this->isAvailableSubmit($matches[1]);
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
            }
            // store submit
            $submit = $this->submitHandlerFactory->handleSave($file, $task, $this->contestant);
            $this->uploadedStorage->commit();
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
        $data = $this->submitHandlerFactory->handleRevoke($this->getPresenter(), $logger, $submitId);
        $response = new ReactResponse();
        if ($data) {
            $response->setData($data);
        }
        $response->setMessages($logger->getMessages());
        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownload() {
        $submitId = $this->getReactRequest()->requestData['submitId'];
        $logger = new MemoryLogger();
        $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $logger, $submitId);
        die();
    }

    protected function getReactId(): string {
        return 'public.ajax-upload';
    }

    /**
     * @param $taskId
     * @return ModelTask|null
     */
    private function isAvailableSubmit($taskId) {
        /** @var ModelTask $task */
        foreach ($this->availableTasks as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            }
        }
        return null;
    }
}
