<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Control\AjaxUpload\SubmitDownloadTrait;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class SubmitsTableControl
 * @package FKSDB\Components\Controls\Upload
 */
class SubmitsPreviewControl extends SeriesTableComponent {
    use SubmitDownloadTrait;

    /** @var UploadedStorage */
    private $uploadedStorage;
    /** @var CorrectedStorage */
    private $correctedStorage;
    /** @var ServiceSubmit $serviceSubmit */
    private $serviceSubmit;

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @param UploadedStorage $uploadedStorage
     * @param CorrectedStorage $correctedStorage
     * @param ServiceSubmit $serviceSubmit
     * @return void
     */
    public function injectPrimary(UploadedStorage $uploadedStorage, CorrectedStorage $correctedStorage, ServiceSubmit $serviceSubmit) {
        $this->uploadedStorage = $uploadedStorage;
        $this->correctedStorage = $correctedStorage;
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        $logger = new MemoryLogger();
        $this->traitHandleDownloadUploaded($logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        $logger = new MemoryLogger();
        $this->traitHandleDownloadCorrected($logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    protected function getCorrectedStorage(): CorrectedStorage {
        return $this->correctedStorage;
    }

    protected function getUploadedStorage(): UploadedStorage {
        return $this->uploadedStorage;
    }

    protected function getServiceSubmit(): ServiceSubmit {
        return $this->serviceSubmit;
    }
}
