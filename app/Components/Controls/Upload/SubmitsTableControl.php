<?php

namespace FKSDB\Components\Controls\Upload;

use FKSDB\Components\Control\AjaxUpload\SubmitDownloadTrait;
use FKSDB\Components\Controls\BaseControl;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * Class SubmitsTableControl
 * @package FKSDB\Components\Controls\Upload
 */
class SubmitsTableControl extends BaseControl {
    use SubmitDownloadTrait;
    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * SubmitsTableControl constructor.
     * @param Container $container
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $container, SeriesTable $seriesTable) {
        parent::__construct($container);
        $this->seriesTable = $seriesTable;
    }

    public function render() {
        $this->template->seriesTable = $this->seriesTable;
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
