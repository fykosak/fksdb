<?php

namespace FKSDB\Submits;

use Authorization\ContestAuthorizator;
use FKSDB\Exceptions\ModelException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class SubmitHandlerFactory {
    /** @var CorrectedStorage */
    private $correctedStorage;
    /** @var UploadedStorage */
    private $uploadedStorage;
    /** @var ServiceSubmit */
    private $serviceSubmit;
    /** @var ContestAuthorizator */
    private $contestAuthorizator;

    /**
     * SubmitDownloadFactory constructor.
     * @param CorrectedStorage $correctedStorage
     * @param UploadedStorage $uploadedStorage
     * @param ServiceSubmit $serviceSubmit
     * @param ContestAuthorizator $contestAuthorizator
     */
    public function __construct(
        CorrectedStorage $correctedStorage,
        UploadedStorage $uploadedStorage,
        ServiceSubmit $serviceSubmit,
        ContestAuthorizator $contestAuthorizator
    ) {
        $this->correctedStorage = $correctedStorage;
        $this->uploadedStorage = $uploadedStorage;
        $this->serviceSubmit = $serviceSubmit;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * @param ModelSubmit $submit
     * @return bool
     * @internal
     */
    public function canRevoke(ModelSubmit $submit): bool {
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            return false;
        }

        $now = time();
        $start = $submit->getTask()->submit_start ? $submit->getTask()->submit_start->getTimestamp() : 0;
        $deadline = $submit->getTask()->submit_deadline ? $submit->getTask()->submit_deadline->getTimestamp() : ($now + 1);

        return ($now <= $deadline) && ($now >= $start);
    }

    /**
     * @param Presenter $presenter
     * @param ILogger $logger
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(Presenter $presenter, ILogger $logger, int $id) {
        $submit = $this->getSubmit($id, 'download.uploaded');
        $filename = $this->uploadedStorage->retrieveFile($submit);
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            $logger->log(new Message(_('Lze stahovat jen uploadovaná řešení.'), Message::LVL_DANGER));
            return;
        }
        if (!$filename) {
            $logger->log(new Message(_('Poškozený soubor submitu'), Message::LVL_DANGER));
            return;
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-uploaded.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }

    /**
     * @param Presenter $presenter
     * @param ILogger $logger
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(Presenter $presenter, ILogger $logger, int $id) {
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            $logger->log(new Message(_('Opravené riešenie nieje nahrané'), Message::LVL_WARNING));
            return;
        }
        $filename = $this->correctedStorage->retrieveFile($submit);
        if (!$filename) {
            $logger->log(new Message(_('Poškozený soubor submitu'), Message::LVL_DANGER));
            return;
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }


    /**
     * @param Presenter $presenter
     * @param ILogger $logger
     * @param int $submitId
     * @return array|null
     * @throws InvalidLinkException
     */
    public function handleRevoke(Presenter $presenter, ILogger $logger, int $submitId) {
        /** @var ModelSubmit $submit */
        $submit = $this->serviceSubmit->findByPrimary($submitId);
        if (!$submit) {
            $logger->log(new Message(_('Neexistující submit.'), Message::LVL_DANGER));
            return null;
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->contestAuthorizator->isAllowed($submit, 'revoke', $contest)) {
            $logger->log(new Message(_('Nedostatečné oprávnění.'), Message::LVL_DANGER));
            return null;
        }
        if (!$this->canRevoke($submit)) {
            $logger->log(new Message(_('Nelze zrušit submit.'), Message::LVL_DANGER));
            return null;
        }
        try {
            $this->uploadedStorage->deleteFile($submit);
            $this->serviceSubmit->dispose($submit);
            $data = ServiceSubmit::serializeSubmit(null, $submit->getTask(), $presenter);
            $logger->log(new Message(\sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), ILogger::WARNING));
            return $data;

        } catch (StorageException $exception) {
            Debugger::log($exception);
            $logger->log(new Message(_('Během mazání úlohy %s došlo k chybě.'), Message::LVL_DANGER));
            return null;
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $logger->log(new Message(_('Během mazání úlohy %s došlo k chybě.'), Message::LVL_DANGER));
            return null;
        }
    }

    /**
     * @param FileUpload $file
     * @param ModelTask $task
     * @param ModelContestant $contestant
     * @return AbstractModelSingle|ModelSubmit
     * @throws \Exception
     */
    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant) {
        $submit = $this->serviceSubmit->findByContestant($contestant->ct_id, $task->task_id);
        if (is_null($submit)) {
            $submit = $this->serviceSubmit->createNewModel([
                'task_id' => $task->task_id,
                'ct_id' => $contestant->ct_id,
                'submitted_on' => new DateTime(),
                'source' => ModelSubmit::SOURCE_UPLOAD,
            ]);
        } else {
            $this->serviceSubmit->updateModel2($submit, [
                'submitted_on' => new DateTime(),
                'source' => ModelSubmit::SOURCE_UPLOAD,
            ]);
        }
        // store file
        $this->uploadedStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    /**
     * @param int $id
     * @param string $privilege
     * @return ModelSubmit
     * @throws BadRequestException
     */
    private function getSubmit(int $id, string $privilege): ModelSubmit {
        /** @var ModelSubmit $submit */
        $submit = $this->serviceSubmit->findByPrimary($id);

        if (!$submit) {
            throw new NotFoundException('Neexistující submit.');
        }
        if (!$this->contestAuthorizator->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new  ForbiddenRequestException('Nedostatečné oprávnění.');
        }
        return $submit;
    }
}
