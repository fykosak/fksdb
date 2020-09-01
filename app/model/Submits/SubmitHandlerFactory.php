<?php

namespace FKSDB\Submits;

use FKSDB\Authorization\ContestAuthorizator;
use FKSDB\Exceptions\NotFoundException;
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
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
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

    public function getUploadedStorage(): UploadedStorage {
        return $this->uploadedStorage;
    }

    public function getCorrectedStorage(): CorrectedStorage {
        return $this->correctedStorage;
    }

    public function getServiceSubmit(): ServiceSubmit {
        return $this->serviceSubmit;
    }

    /**
     * @param Presenter $presenter
     * @param ModelSubmit $submit
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function handleDownloadUploaded(Presenter $presenter, ModelSubmit $submit): void {
        $this->checkPrivilege($submit, 'download.uploaded');
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
     * @param ModelSubmit $submit
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function handleDownloadCorrected(Presenter $presenter, ModelSubmit $submit): void {
        $this->checkPrivilege($submit, 'download.corrected');
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
     * @param ModelSubmit $submit
     * @return void
     * @throws ForbiddenRequestException
     */
    public function handleRevoke(ModelSubmit $submit): void {
        $this->checkPrivilege($submit, 'revoke');
        if (!$submit->canRevoke()) {
            throw new StorageException(_('Nelze zrušit submit.'));
        }
        $this->uploadedStorage->deleteFile($submit);
        $this->serviceSubmit->dispose($submit);
    }

    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant): ModelSubmit {
        $submit = $this->storeSubmit($task, $contestant, ModelSubmit::SOURCE_UPLOAD);
        // store file
        $this->uploadedStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    public function getUserStudyYear(ModelContestant $contestant, int $academicYear): ?int {
        // TODO AC_year from contestant
        $personHistory = $contestant->getPerson()->getHistory($academicYear);
        return ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
    }

    public function handleQuizSubmit(ModelTask $task, ModelContestant $contestant): ModelSubmit {
        return $this->storeSubmit($task, $contestant, ModelSubmit::SOURCE_QUIZ);
    }

    private function storeSubmit(ModelTask $task, ModelContestant $contestant, string $source): ModelSubmit {
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
     * @param bool $throw
     * @return ModelSubmit|null
     * @throws NotFoundException
     */
    public function getSubmit(int $id, bool $throw = true): ?ModelSubmit {
        $submit = $this->serviceSubmit->findByPrimary($id);
        if ($throw && !$submit) {
            throw new NotFoundException(_('Submit does not exists.'));
        }
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
