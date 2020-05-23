<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
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
        /** @var ModelSubmit $submit */
        $submit = $this->getServiceSubmit()->findByPrimary($id);

        if (!$submit) {
            throw new NotFoundException('Neexistující submit.');
        }
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, $privilege, $submit->getContestant()->getContest())) {
            throw new  ForbiddenRequestException('Nedostatečné oprávnění.');
        }
        return $submit;
    }


    /**
     * @param ILogger $logger
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function traitHandleDownloadUploaded(ILogger $logger, int $id) {
        $submit = $this->getSubmit($id, 'download.uploaded');
        $filename = $this->getUploadedStorage()->retrieveFile($submit);
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            $logger->log(new Message(_('Lze stahovat jen uploadovaná řešení.'), ILogger::ERROR));
            return;
        }
        if (!$filename) {
            $logger->log(new Message(_('Poškozený soubor submitu'), ILogger::ERROR));
            return;
        }
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-uploaded.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);

        die();
    }

    /**
     * @param ILogger $logger
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function traitHandleDownloadCorrected(ILogger $logger, int $id) {
        $submit = $this->getSubmit($id, 'download.corrected');
        if (!$submit->corrected) {
            $logger->log(new Message(_('Opravené riešenie nieje nahrané'), ILogger::WARNING));
            return;
        }

        $filename = $this->getCorrectedStorage()->retrieveFile($submit);
        if (!$filename) {
            $logger->log(new Message(_('Poškozený soubor submitu'), ILogger::ERROR));
            return;
        }

        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '-corrected.pdf', 'application/pdf');
        $this->getPresenter()->sendResponse($response);
        die();
    }

    /**
     * @param bool $need
     * @return SubmitPresenter|InboxPresenter
     */
    abstract protected function getPresenter($need = true);

    abstract protected function getCorrectedStorage(): CorrectedStorage;

    abstract protected function getUploadedStorage(): UploadedStorage;

    abstract protected function getServiceSubmit(): ServiceSubmit;
}
