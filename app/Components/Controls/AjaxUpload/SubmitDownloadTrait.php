<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemUploadedSubmitStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use OrgModule\InboxPresenter;
use PublicModule\SubmitPresenter;

/**
 * Class SubmitDownloadTrait
 * @package FKSDB\Components\Control\AjaxUpload
 */
trait SubmitDownloadTrait {
    /**
     * @param int $id
     * @param string $privilege
     * @return ModelSubmit
     * @throws BadRequestException
     */
    private function getSubmit(int $id, string $privilege): ModelSubmit {
        /**
         * @var ModelSubmit $submit
         */
        $submit = $this->getServiceSubmit()->findByPrimary($id);

        if (!$submit) {
            throw new BadRequestException('Neexistující submit.', 404);
        }
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new  BadRequestException('Nedostatečné oprávnění.', 403);
        }
        return $submit;
    }

    /**
     * @return ServiceSubmit
     */
    abstract protected function getServiceSubmit(): ServiceSubmit;

    /**
     * @param bool $need
     * @return SubmitPresenter|InboxPresenter
     */
    abstract protected function getPresenter($need = true);

    /**
     * @return FilesystemUploadedSubmitStorage
     */
    abstract protected function getSubmitUploadedStorage(): FilesystemUploadedSubmitStorage;

    /**
     * @return FilesystemCorrectedSubmitStorage
     */
    abstract protected function getSubmitCorrectedStorage(): FilesystemCorrectedSubmitStorage;

    /**
     * @param int $id
     * @return array
     * @throws AbortException
     * @throws BadRequestException
     */
    public function traitHandleDownloadUploaded(int $id): array {
        $submit = $this->getSubmit($id, 'download.uploaded');
        $filename = $this->getSubmitUploadedStorage()->retrieveFile($submit);
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            return [new Message(_('Lze stahovat jen uploadovaná řešení.'), ILogger::ERROR),];
        }
        if (!$filename) {
            return [new Message(_('Poškozený soubor submitu'), ILogger::ERROR),];
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-uploaded.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);

        die();
    }

    /**
     * @param int $id
     * @return array
     * @throws AbortException
     * @throws BadRequestException
     */
    public function traitHandleDownloadCorrected(int $id): array {
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            return [new Message(_('Opravené riešenie nieje nahrané'), ILogger::WARNING),];
        }

        $filename = $this->getSubmitCorrectedStorage()->retrieveFile($submit);
        if (!$filename) {
            return [new Message(_('Poškozený soubor submitu'), ILogger::ERROR),];
        }

        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);
        die();
    }
}
