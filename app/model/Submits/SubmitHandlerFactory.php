<?php

namespace FKSDB\Submits;

use FKSDB\Authorization\ContestAuthorizator;
use FKSDB\Components\Control\AjaxUpload\AjaxUpload;
use FKSDB\Exceptions\ModelException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
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

    private CorrectedStorage $correctedStorage;

    private UploadedStorage $uploadedStorage;

    private ServiceSubmit $serviceSubmit;

    private ContestAuthorizator $contestAuthorizator;

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
     * @param Presenter $presenter
     * @param ILogger $logger
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleDownloadUploaded(Presenter $presenter, ILogger $logger, int $id): void {
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
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleDownloadCorrected(Presenter $presenter, ILogger $logger, int $id): void {
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
     * @throws ForbiddenRequestException
     * @throws InvalidLinkException
     * @throws NotFoundException
     */
    public function handleRevoke(Presenter $presenter, ILogger $logger, int $submitId): ?array {
        $submit = $this->getSubmit($submitId, 'revoke');
        if (!$submit) {
            $logger->log(new Message(_('Neexistující submit.'), Message::LVL_DANGER));
            return null;
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->contestAuthorizator->isAllowed($submit, 'revoke', $contest)) {
            $logger->log(new Message(_('Nedostatečné oprávnění.'), Message::LVL_DANGER));
            return null;
        }
        if (!$submit->canRevoke()) {
            $logger->log(new Message(_('Nelze zrušit submit.'), Message::LVL_DANGER));
            return null;
        }
        try {
            $this->uploadedStorage->deleteFile($submit);
            $this->serviceSubmit->dispose($submit);
            $data = AjaxUpload::serializeSubmit(null, $submit->getTask(), $presenter);
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

    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant): ModelSubmit {
        $submit = $this->handleStoreSubmit($task, $contestant, ModelSubmit::SOURCE_UPLOAD);
        // store file
        $this->uploadedStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    public function handleQuizSubmit(ModelTask $task, ModelContestant $contestant): ModelSubmit {
        return $this->handleStoreSubmit($task, $contestant, ModelSubmit::SOURCE_QUIZ);
    }

    private function handleStoreSubmit(ModelTask $task, ModelContestant $contestant, string $source): ModelSubmit {
        $submit = $this->serviceSubmit->findByContestant($contestant->ct_id, $task->task_id);
        $data = [
            'submitted_on' => new DateTime(),
            'source' => $source,
            'task_id' => $task->task_id, // ugly is submit exists -- rewrite same by same value
            'ct_id' => $contestant->ct_id,// ugly is submit exists -- rewrite same by same value
        ];
        return $this->serviceSubmit->store($submit, $data);
    }

    /**
     * @param int $id
     * @param string $privilege
     * @return ModelSubmit
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    private function getSubmit(int $id, string $privilege): ModelSubmit {
        $submit = $this->serviceSubmit->findByPrimary($id);

        if (!$submit) {
            throw new NotFoundException('Neexistující submit.');
        }
        if (!$this->contestAuthorizator->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new ForbiddenRequestException('Nedostatečné oprávnění.');
        }
        return $submit;
    }
}
