<?php

namespace FKSDB\Components\Controls\Upload;

use FKSDB\Components\Control\AjaxUpload\SubmitDownloadTrait;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class SubmitsTableControl
 * @package FKSDB\Components\Controls\Upload
 */
class SubmitsTableControl extends SeriesTableControl {
    use SubmitDownloadTrait;

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SubmitsTableControl.latte');
        $this->template->render();
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        list($message) = $this->traitHandleDownloadUploaded($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        list($message) = $this->traitHandleDownloadCorrected($id);
        $this->flashMessage($message->getMessage(), $message->getLevel());
    }
}
