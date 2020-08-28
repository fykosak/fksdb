<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Components\React\ReactComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\React\ReactResponse;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Http\Response;
use FKSDB\Modules\PublicModule\SubmitPresenter;

/**
 * Class AjaxUpload
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read SubmitPresenter $presenter
 */
class AjaxUpload extends ReactComponent {

    private ServiceSubmit $serviceSubmit;

    private UploadedStorage $uploadedStorage;

    /** @var TypedTableSelection */
    private $availableTasks;

    /**@var ModelContestant */
    private $contestant;

    private SubmitHandlerFactory $submitHandlerFactory;

    public function injectPrimary(ServiceSubmit $serviceSubmit, UploadedStorage $uploadedStorage, SubmitHandlerFactory $submitHandlerFactory): void {
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
        parent::__construct($container, 'public.ajax-upload');
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
     * @param mixed ...$args
     * @return string
     * @throws InvalidLinkException
     */
    public function getData(...$args): string {
        $data = [];
        /** @var ModelTask $task */
        foreach ($this->availableTasks as $task) {
            $submit = $this->serviceSubmit->findByContestant($this->contestant->ct_id, $task->task_id);
            $data[$task->task_id] = self::serializeSubmit($submit, $task, $this->getPresenter());
        }
        return json_encode($data);
    }

    /**
     * @param ModelSubmit|null $submit
     * @param ModelTask $task
     * @param Presenter $presenter
     * @return array
     * @throws InvalidLinkException
     */
    public static function serializeSubmit($submit, ModelTask $task, Presenter $presenter): array {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'href' => $submit ? $presenter->link('download', ['id' => $submit->submit_id]) : null,
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Termín %s'), $task->submit_deadline),
        ];
    }

    /**
     * @return void
     * @throws AbortException
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    public function handleUpload(): void {
        $response = new ReactResponse();

        $files = $this->getHttpRequest()->getFiles();
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->uploadedStorage->beginTransaction();
            if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                $response->addMessage(new Message(_('Task not found'), ILogger::WARNING));
                continue;
            }
            $task = $this->isAvailableSubmit($matches[1]);
            if (!$task) {

                $response->setCode(Response::S403_FORBIDDEN);
                $response->addMessage(new Message(_('Upload not allowed'), ILogger::ERROR));
                $this->getPresenter()->sendResponse($response);
            }
            /** @var FileUpload $file */
            $file = $fileContainer;
            if (!$file->isOk()) {
                $response->setCode(Response::S500_INTERNAL_SERVER_ERROR);
                $response->addMessage(new Message(_('File is not Ok'), ILogger::ERROR));
                $this->getPresenter()->sendResponse($response);
            }
            // store submit
            $submit = $this->submitHandlerFactory->handleSave($file, $task, $this->contestant);
            $this->uploadedStorage->commit();
            $this->serviceSubmit->getConnection()->commit();
            $response->addMessage(new Message(_('Upload successful'), ILogger::SUCCESS));
            $response->setAct('upload');
            $response->setData(self::serializeSubmit($submit, $task, $this->getPresenter()));
            $this->getPresenter()->sendResponse($response);
        }
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws InvalidLinkException
     * @throws NotFoundException
     */
    public function handleRevoke(): void {
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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleDownload(): void {
        $submitId = $this->getReactRequest()->requestData['submitId'];
        $logger = new MemoryLogger();
        $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $logger, $submitId);
        die();
    }

    /**
     * @param int $taskId
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
