<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\Authorization\ContestAuthorizator;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Models\Submits\FileSystemStorage\UploadedStorage;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;

class SubmitHandlerFactory
{

    public CorrectedStorage $correctedStorage;
    public UploadedStorage $uploadedStorage;
    public ServiceSubmit $serviceSubmit;
    public ContestAuthorizator $contestAuthorizator;

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
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws StorageException
     */
    public function handleDownloadUploaded(Presenter $presenter, SubmitModel $submit): void
    {
        $this->checkPrivilege($submit, 'download.uploaded');
        $filename = $this->uploadedStorage->retrieveFile($submit);
        if ($submit->source !== SubmitModel::SOURCE_UPLOAD) {
            throw new StorageException(_('Only uploaded solutions can be downloaded.'));
        }
        if (!$filename) {
            throw new StorageException(_('Damaged submit file'));
        }
        $response = new FileResponse($filename, $submit->task->getFQName() . '-uploaded.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws StorageException
     */
    public function handleDownloadCorrected(Presenter $presenter, SubmitModel $submit): void
    {
        $this->checkPrivilege($submit, 'download.corrected');
        if (!$submit->corrected) {
            throw new StorageException(_('Corrected solution is not uploaded'));
        }
        $filename = $this->correctedStorage->retrieveFile($submit);
        if (!$filename) {
            throw new StorageException(_('Damaged submit file'));
        }
        $response = new FileResponse($filename, $submit->task->getFQName() . '-corrected.pdf', 'application/pdf');
        $presenter->sendResponse($response);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws StorageException
     * @throws ModelException
     */
    public function handleRevoke(SubmitModel $submit): void
    {
        $this->checkPrivilege($submit, 'revoke');
        if (!$submit->canRevoke()) {
            throw new StorageException(_('Submit cannot be revoked.'));
        }
        $this->uploadedStorage->deleteFile($submit);
        $this->serviceSubmit->dispose($submit);
    }

    public function handleSave(FileUpload $file, ModelTask $task, ModelContestant $contestant): SubmitModel
    {
        $submit = $this->storeSubmit($task, $contestant, SubmitModel::SOURCE_UPLOAD);
        // store file
        $this->uploadedStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    public function getUserStudyYear(ModelContestant $contestant): ?int
    {
        // TODO AC_year from contestant
        $personHistory = $contestant->getPersonHistory();
        return ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
    }

    public function handleQuizSubmit(ModelTask $task, ModelContestant $contestant): SubmitModel
    {
        return $this->storeSubmit($task, $contestant, SubmitModel::SOURCE_QUIZ);
    }

    private function storeSubmit(ModelTask $task, ModelContestant $contestant, string $source): SubmitModel
    {
        $submit = $this->serviceSubmit->findByContestant($contestant, $task);
        $data = [
            'submitted_on' => new DateTime(),
            'source' => $source,
            'task_id' => $task->task_id, // ugly is submit exists -- rewrite same by same value
            'ct_id' => $contestant->ct_id,// ugly is submit exists -- rewrite same by same value
        ];
        return $this->serviceSubmit->storeModel($data, $submit);
    }

    /**
     * @throws NotFoundException
     */
    public function getSubmit(int $id, bool $throw = true): ?SubmitModel
    {
        $submit = $this->serviceSubmit->findByPrimary($id);
        if ($throw && !$submit) {
            throw new NotFoundException(_('Submit does not exist.'));
        }
        return $submit;
    }

    /**
     * @throws ForbiddenRequestException
     */
    private function checkPrivilege(SubmitModel $submit, string $privilege): void
    {
        if (!$this->contestAuthorizator->isAllowed($submit, $privilege, $submit->getContestant()->contest)) {
            throw new ForbiddenRequestException(_('Access denied'));
        }
    }
}
