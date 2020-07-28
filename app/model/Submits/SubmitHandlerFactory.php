<?php

namespace FKSDB\Submits;

use FKSDB\Authorization\ContestAuthorizator;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelLogin;
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
use Nette\Security\IUserStorage;
use Nette\Utils\DateTime;

/**
 * Class SubmitHandlerFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitHandlerFactory {

    /** @var CorrectedStorage */
    private $correctedStorage;

    /** @var UploadedStorage */
    private $uploadedStorage;

    /** @var ServiceSubmit */
    private $serviceSubmit;

    /** @var ContestAuthorizator */
    private $contestAuthorizator;

    /** @var IUserStorage */
    private $userStorage;

    /**
     * SubmitDownloadFactory constructor.
     * @param CorrectedStorage $correctedStorage
     * @param UploadedStorage $uploadedStorage
     * @param ServiceSubmit $serviceSubmit
     * @param ContestAuthorizator $contestAuthorizator
     * @param IUserStorage $userStorage
     */
    public function __construct(
        CorrectedStorage $correctedStorage,
        UploadedStorage $uploadedStorage,
        ServiceSubmit $serviceSubmit,
        ContestAuthorizator $contestAuthorizator,
        IUserStorage $userStorage
    ) {
        $this->correctedStorage = $correctedStorage;
        $this->uploadedStorage = $uploadedStorage;
        $this->serviceSubmit = $serviceSubmit;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->userStorage = $userStorage;
    }

    /**
     * @param Presenter $presenter
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleDownloadUploaded(Presenter $presenter, int $id) {
        $submit = $this->getSubmit($id, 'download.uploaded');

        $filename = $this->uploadedStorage->retrieveFile($submit);
        if ($submit->source !== ModelSubmit::SOURCE_UPLOAD) {
            throw new StorageException(_('Lze stahovat jen uploadovaná řešení.'));
        }
        if (!$filename) {
            throw new StorageException(_('Poškozený soubor submitu'));
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-uploaded.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }

    /**
     * @param Presenter $presenter
     * @param int $id
     * @return void
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws AbortException
     * @throws BadRequestException
     * @throws StorageException
     */
    public function handleDownloadCorrected(Presenter $presenter, int $id) {
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            throw new StorageException(_('Opravené riešenie nieje nahrané'));
        }
        $filename = $this->correctedStorage->retrieveFile($submit);
        if (!$filename) {
            throw new StorageException(_('Poškozený soubor submitu'));
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }

    /**
     * @param Presenter $presenter
     * @param ILogger $logger
     * @param int $submitId
     * @param int $academicYear
     * @return array
     * @throws ForbiddenRequestException
     * @throws InvalidLinkException
     * @throws NotFoundException
     * @throws StorageException
     */
    public function handleRevoke(Presenter $presenter, ILogger $logger, int $submitId, int $academicYear): array {
        $submit = $this->getSubmit($submitId, 'revoke');

        if (!$submit->canRevoke()) {
            throw new StorageException(_('Nelze zrušit submit.'));
        }
        $this->uploadedStorage->deleteFile($submit);
        $this->serviceSubmit->dispose($submit);
        $data = ServiceSubmit::serializeSubmit(null, $submit->getTask(), $this->getUserStudyYear($academicYear));
        $logger->log(new Message(\sprintf(_('Odevzdání úlohy %s zrušeno.'), $submit->getTask()->getFQName()), ILogger::WARNING));
        return $data;
    }

    /**
     * @param FileUpload $file
     * @param ModelTask $task
     * @param ModelContestant $contestant
     * @return AbstractModelSingle|IModel|ModelSubmit
     */
    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant) {
        $submit = $this->serviceSubmit->findByContestant($contestant->ct_id, $task->task_id);
        $submit = $this->serviceSubmit->store($submit, [
            'task_id' => $task->task_id,
            'ct_id' => $contestant->ct_id,
            'submitted_on' => new DateTime(),
            'source' => ModelSubmit::SOURCE_UPLOAD,
        ]);
        // store file
        $this->uploadedStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    /**
     * @param int $academicYear
     * @return int|null
     */
    public function getUserStudyYear(int $academicYear) {
        /** @var ModelLogin $login */
        $login = $this->userStorage->getIdentity();
        $personHistory = $login->getPerson()->getHistory($academicYear);
        return ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
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
            throw new NotFoundException(_('Submit does not exists.'));
        }
        if (!$this->contestAuthorizator->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new ForbiddenRequestException(_('Access denied'));
        }
        return $submit;
    }
}
