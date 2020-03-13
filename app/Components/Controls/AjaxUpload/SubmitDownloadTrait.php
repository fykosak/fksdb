<?php


namespace FKSDB\Components\Control\AjaxUpload;


use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemSubmitUploadedStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
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
     * @return SubmitPresenter
     */
    abstract protected function getPresenter($need = true);

    /**
     * @return FilesystemSubmitUploadedStorage
     */
    abstract protected function getSubmitUploadedStorage(): FilesystemSubmitUploadedStorage;

    /**
     * @return FilesystemCorrectedSubmitStorage
     */
    abstract protected function getSubmitCorrectedStorage(): FilesystemCorrectedSubmitStorage;

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws AbortException
     */
    public function handleDownloadUploaded(int $id) {
        $submit = $this->getSubmit($id, 'download.uploaded');
        $filename = $this->getSubmitUploadedStorage()->retrieveFile($submit);
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            throw new BadRequestException('Lze stahovat jen uploadovaná řešení.', 501);
        }
        if (!$filename) {
            throw new BadRequestException('Poškozený soubor submitu', 500);
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-uploaded.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws AbortException
     */
    public function handleDownloadCorrected(int $id) {
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            throw new BadRequestException('Opravené riešenie nieje nahrané', 404);
        }

        $filename = $this->getSubmitCorrectedStorage()->retrieveFile($submit);
        if (!$filename) {
            throw new BadRequestException('Poškozený soubor submitu', 500);
        }

        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);
    }
}
