<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemUploadedSubmitStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Container;
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
        /** @var ServiceSubmit $serviceSubmit */
        $serviceSubmit = $this->getContext()->getByType(ServiceSubmit::class);
        /** @var ModelSubmit $submit */
        $submit = $serviceSubmit->findByPrimary($id);

        if (!$submit) {
            throw new NotFoundException('Neexistující submit.');
        }
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new  ForbiddenRequestException('Nedostatečné oprávnění.');
        }
        return $submit;
    }


    /**
     * @param int $id
     * @return array
     * @throws AbortException
     * @throws BadRequestException
     */
    public function traitHandleDownloadUploaded(int $id): array {
        $submit = $this->getSubmit($id, 'download.uploaded');
        /** @var FilesystemUploadedSubmitStorage $filesystemUploadedSubmitStorage */
        $filesystemUploadedSubmitStorage = $this->getContext()->getByType(FilesystemUploadedSubmitStorage::class);
        $filename = $filesystemUploadedSubmitStorage->retrieveFile($submit);
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
        /** @var FilesystemCorrectedSubmitStorage $filesystemCorrectedSubmitStorage */
        $filesystemCorrectedSubmitStorage = $this->getContext()->getByType(FilesystemCorrectedSubmitStorage::class);
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            return [new Message(_('Opravené riešenie nieje nahrané'), ILogger::WARNING),];
        }

        $filename = $filesystemCorrectedSubmitStorage->retrieveFile($submit);
        if (!$filename) {
            return [new Message(_('Poškozený soubor submitu'), ILogger::ERROR),];
        }

        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);
        die();
    }

    /**
     * @return Container
     */
    abstract public function getContext();

    /**
     * @param bool $need
     * @return SubmitPresenter|InboxPresenter
     */
    abstract protected function getPresenter($need = true);
}
