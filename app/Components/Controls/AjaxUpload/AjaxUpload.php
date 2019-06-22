<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\StorageException;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use PublicModule\SubmitPresenter;
use Tracy\Debugger;

/**
 * Class AjaxUpload
 * @package FKSDB\Components\Control\AjaxUpload
 * @property-read SubmitPresenter $presenter
 */
class AjaxUpload extends ReactComponent {
    private $serviceSubmit;

    /**
     * AjaxUpload constructor.
     * @param Container $context
     */
    public function __construct(Container $context, ServiceSubmit $serviceSubmit) {
        parent::__construct($context);
        $this->serviceSubmit = $serviceSubmit;
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
        $actions['revoke'] = $this->link('!revoke');
        $actions['upload'] = $this->link('!upload');
        return $actions;
    }

    /**
     * @return string
     * @throws BadRequestException
     */
    public function getData(): string {
        $data = [];
        $presenter = $this->getPresenter();
        if (!$presenter instanceof SubmitPresenter) {
            throw new BadRequestException();
        }
        /**
         * @var ModelTask $task
         */
        foreach ($presenter->getAvailableTasks() as $task) {
            $submit = $this->serviceSubmit->findByContestant($this->getContestant()->ct_id, $task->task_id);
            $data[$task->task_id] = $this->serializeData($submit, $task);
        };
        return json_encode($data);
    }


    /**
     * @param ModelSubmit $submit
     * @param ModelTask $task
     * @return array
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    private function serializeData($submit, ModelTask $task) {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'href' => $submit ? $this->link('download', ['id' => $submit->submit_id]) : null,
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Termín %s'), $task->submit_deadline),
        ];
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'ajax-upload';
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleUpload(\ReactResponse $response) {
        $contestant = $this->getContestant();
        $files = $this->getHttpRequest()->getFiles();
        foreach ($files as $name => $fileContainer) {
            $this->submitService->getConnection()->beginTransaction();
            $this->submitStorage->beginTransaction();
            if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                $response->addMessage(new \ReactMessage('task not found', 'warning'));
                continue;
            }
            $task = $this->isAvailableSubmit($matches[1]);
            if (!$task) {
                $this->getHttpResponse()->setCode('403');
                $response->addMessage(new \ReactMessage('upload not allowed', 'danger'));
                $this->sendResponse($response);
            };
            /**
             * @var $file FileUpload
             */
            $file = $fileContainer;
            if (!$file->isOk()) {
                $this->getHttpResponse()->setCode('500');
                $response->addMessage(new \ReactMessage('file is not Ok', 'danger'));
                $this->sendResponse($response);
                return;
            }
            // store submit
            $submit = $this->saveSubmit($file, $task, $contestant);
            $this->submitStorage->commit();
            $this->submitService->getConnection()->commit();
            $response->addMessage(new \ReactMessage('Upload úspešný', 'success'));
            $response->setAct('upload');
            $response->setData($this->serializeData($submit, $task));
            $this->sendResponse($response);
        }
        die();
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function handleRevoke(\ReactResponse $response) {
        $submitId = $this->getHttpRequest()->getPost('data')['submitId'];
        /**
         * @var ModelSubmit $submit
         */
        $submit = $this->submitService->findByPrimary($submitId);
        if (!$submit) {
            $response->addMessage(new \ReactMessage(_('Neexistující submit.'), 'danger'));
            $this->sendResponse($response);
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->getContestAuthorizator()->isAllowed($submit, 'revoke', $contest)) {
            $response->addMessage(new \ReactMessage(_('Nedostatečné oprávnění.'), 'danger'));
            $this->sendResponse($response);
        }
        if (!$this->canRevokeSubmit($submit)) {
            $response->addMessage(new \ReactMessage(_('Nelze zrušit submit.'), 'danger'));
            $this->sendResponse($response);
        }
        try {
            $this->submitStorage->deleteFile($submit);
            $this->submitService->dispose($submit);
            $data = $this->serializeData(null, $submit->getTask());
            $response->setData($data);
            $response->addMessage(new \ReactMessage(sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), 'danger'));
            $this->sendResponse($response);
        } catch (StorageException $e) {
            $response->addMessage(new \ReactMessage(_('Během mazání úlohy %s došlo k chybě.'), 'danger'));
            $this->sendResponse($response);
            Debugger::log($e);
        } catch (ModelException $e) {
            $response->addMessage(new \ReactMessage(_('Během mazání úlohy %s došlo k chybě.'), 'danger'));
            $this->sendResponse($response);
            Debugger::log($e);
        }
        die();
    }

    /**
     * @internal
     * @param ModelSubmit $submit
     * @return boolean
     */
    private function canRevokeSubmit(ModelSubmit $submit) {
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            return false;
        }
        $now = time();
        $start = $submit->getTask()->submit_start ? $submit->getTask()->submit_start->getTimestamp() : 0;
        $deadline = $submit->getTask()->submit_deadline ? $submit->getTask()->submit_deadline->getTimestamp() : ($now + 1);
        return ($now <= $deadline) && ($now >= $start);
    }


}
