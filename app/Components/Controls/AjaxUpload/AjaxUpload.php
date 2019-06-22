<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Components\React\ReactComponent;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\ISubmitStorage;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use PublicModule\SubmitPresenter;
use ReactResponse;

/**
 * Class AjaxUpload
 * @package FKSDB\Components\Control\AjaxUpload
 * @property-read SubmitPresenter $presenter
 */
class AjaxUpload extends ReactComponent {
    use SubmitRevokeTrait;
    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;
    /**
     * @var ISubmitStorage
     */
    private $submitStorage;

    /**
     * AjaxUpload constructor.
     * @param Container $context
     * @param ServiceSubmit $serviceSubmit
     * @param ISubmitStorage $submitStorage
     */
    public function __construct(Container $context, ServiceSubmit $serviceSubmit, ISubmitStorage $submitStorage) {
        parent::__construct($context);
        $this->serviceSubmit = $serviceSubmit;
        $this->submitStorage = $submitStorage;
    }

    /**
     * @return ServiceSubmit
     */
    protected function getServiceSubmit(): ServiceSubmit {
        return $this->serviceSubmit;
    }

    /**
     * @return ISubmitStorage
     */
    protected function getSubmitStorage(): ISubmitStorage {
        return $this->submitStorage;
    }

    /**
     * @return string
     */
    public function getModuleName(): string {
        return 'public';
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return '';
    }

    /**
     * @return array
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function getActions(): array {
        $actions = parent::getActions();
        $actions['revoke'] = $this->link('revoke!');
        $actions['upload'] = $this->link('upload!');
        return $actions;
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function getData(): string {
        $data = [];
        /**
         * @var ModelTask $task
         */
        foreach ($this->getPresenter()->getAvailableTasks() as $task) {
            $submit = $this->serviceSubmit->findByContestant($this->getPresenter()->getContestant()->ct_id, $task->task_id);
            $data[$task->task_id] = $this->serviceSubmit->serializeSubmit($submit, $task, $this->getPresenter());
        };
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
            throw new BadRequestException();
        }
        return $presenter;
    }


    /**
     * @param ModelSubmit $submit
     * @param ModelTask $task
     * @return array
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws BadRequestException
     * @deprecated
     */
    private function serializeData($submit, ModelTask $task) {
        return $this->serviceSubmit->serializeSubmit($submit, $task, $this->getPresenter());
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'ajax-upload';
    }

    /**
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function handleUpload() {
        $response = new ReactResponse();
        $contestant = $this->getPresenter()->getContestant();
        $files = $this->getHttpRequest()->getFiles();
        foreach ($files as $name => $fileContainer) {
            $this->serviceSubmit->getConnection()->beginTransaction();
            $this->submitStorage->beginTransaction();
            if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                $response->addMessage(new \ReactMessage('task not found', 'warning'));
                continue;
            }
            $task = $this->getPresenter()->isAvailableSubmit($matches[1]);
            if (!$task) {
                $this->getPresenter()->getHttpResponse()->setCode('403');
                $response->addMessage(new \ReactMessage('upload not allowed', 'danger'));
                $this->getPresenter()->sendResponse($response);
            };
            /**
             * @var FileUpload $file
             */
            $file = $fileContainer;
            if (!$file->isOk()) {
                $this->getPresenter()->getHttpResponse()->setCode('500');
                $response->addMessage(new \ReactMessage('file is not Ok', 'danger'));
                $this->getPresenter()->sendResponse($response);
                return;
            }
            // store submit
            $submit = $this->saveSubmit($file, $task, $contestant);
            $this->submitStorage->commit();
            $this->serviceSubmit->getConnection()->commit();
            $response->addMessage(new \ReactMessage('Upload úspešný', 'success'));
            $response->setAct('upload');
            $response->setData($this->serializeData($submit, $task));
            $this->getPresenter()->sendResponse($response);
        }

        die();
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws BadRequestException
     */
    public function handleRevoke() {
        $submitId = $this->getHttpRequest()->getPost('data')['submitId'];
        /**
         * @var Message $message
         */
        list($message, $data) = $this->traitHandleRevoke($submitId);
        $response = new ReactResponse();
        if ($data) {
            $response->setData($data);
        }
        $response->addMessage(new \ReactMessage($message->getMessage(), $message->getLevel()));
        $this->getPresenter()->sendResponse($response);
        die();
    }

    /**
     * @param FileUpload $file
     * @param ModelContestant $contestant
     * @param ModelTask $task
     * @return AbstractModelSingle|ModelSubmit
     */
    private function saveSubmit(FileUpload $file, ModelTask $task, ModelContestant $contestant) {
        $submit = $this->serviceSubmit->findByContestant($contestant->ct_id, $task->task_id);
        if (!$submit) {
            $submit = $this->serviceSubmit->createNew([
                'task_id' => $task->task_id,
                'ct_id' => $contestant->ct_id,
            ]);
        }
        //TODO handle cases when user modifies already graded submit (i.e. with bad timings)
        $submit->submitted_on = new DateTime();
        $submit->source = ModelSubmit::SOURCE_UPLOAD;
        $submit->ct_id; // stupid... touch the field in order to have it loaded via ActiveRow
        $this->serviceSubmit->save($submit);
        // store file
        $this->submitStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }


}
