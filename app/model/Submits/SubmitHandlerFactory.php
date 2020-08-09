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
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use Nette\Security\IUserStorage;
use Nette\Utils\DateTime;

/**
 * Class SubmitHandlerFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitHandlerFactory {

    private CorrectedStorage $correctedStorage;

    private UploadedStorage $uploadedStorage;

    private ServiceSubmit $serviceSubmit;

    private ContestAuthorizator $contestAuthorizator;

    private IUserStorage $userStorage;

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

    public function getUploadedStorage(): UploadedStorage {
        return $this->uploadedStorage;
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
    public function handleDownloadUploaded(Presenter $presenter, int $id): void {
        $submit = $this->getSubmit($id, 'download.uploaded');
        $this->downloadUploadedSubmit($presenter, $submit);
    }

    /**
     * @param Presenter $presenter
     * @param ModelSubmit $submit
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function handleDownloadUploadedSubmit(Presenter $presenter, ModelSubmit $submit): void {
        $this->checkPrivilege($submit, 'download.uploaded');
        $this->downloadUploadedSubmit($presenter, $submit);
    }

    /**
     * @param Presenter $presenter
     * @param ModelSubmit $submit
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function downloadUploadedSubmit(Presenter $presenter, ModelSubmit $submit): void {
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
    public function handleDownloadCorrected(Presenter $presenter, int $id): void {
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
     * @param ILogger $logger
     * @param int $submitId
     * @param int $academicYear
     * @return array
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws StorageException
     */
    public function handleRevoke(ILogger $logger, int $submitId, int $academicYear): array {
        $submit = $this->getSubmit($submitId, 'revoke');
        return $this->revoke($logger, $submit, $academicYear);
    }

    /**
     * @param ILogger $logger
     * @param ModelSubmit $submit
     * @param int $academicYear
     * @return array
     * @throws ForbiddenRequestException
     */
    public function handleRevokeSubmit(ILogger $logger, ModelSubmit $submit, int $academicYear): array {
        $this->checkPrivilege($submit, 'revoke');
        return $this->revoke($logger, $submit, $academicYear);
    }

    private function revoke(ILogger $logger, ModelSubmit $submit, int $academicYear): array {
        if (!$submit->canRevoke()) {
            throw new StorageException(_('Nelze zrušit submit.'));
        }
        $this->uploadedStorage->deleteFile($submit);
        $this->serviceSubmit->dispose($submit);
        $data = [$submit->getTask()->task_id => ServiceSubmit::serializeSubmit(null, $submit->getTask(), $this->getUserStudyYear($academicYear))];
        $logger->log(new Message(\sprintf(_('Odevzdání úlohy %s zrušeno.'), $submit->getTask()->getFQName()), ILogger::WARNING));
        return $data;
    }

    /**
     * @param FileUpload $file
     * @param ModelTask $task
     * @param ModelContestant $contestant
     * @return AbstractModelSingle|IModel|ModelSubmit
     */
    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant): ModelSubmit {
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

    public function getUserStudyYear(int $academicYear): ?int {
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
        $this->checkPrivilege($submit, $privilege);
        return $submit;
    }

    /**
     * @param ModelSubmit $submit
     * @param string $privilege
     * @return void
     * @throws ForbiddenRequestException
     */
    private function checkPrivilege(ModelSubmit $submit, string $privilege): void {
        if (!$this->contestAuthorizator->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new ForbiddenRequestException(_('Access denied'));
        }
    }
}
