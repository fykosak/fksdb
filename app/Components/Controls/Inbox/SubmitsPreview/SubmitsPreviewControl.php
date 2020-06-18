<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class SubmitsPreviewControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitsPreviewControl extends SeriesTableComponent {
    /**
     * @var SubmitHandlerFactory
     */
    private $submitDownloadFactory;

    /**
     * @param SubmitHandlerFactory $submitDownloadFactory
     * @return void
     */
    public function injectSubmitDownloadFactory(SubmitHandlerFactory $submitDownloadFactory) {
        $this->submitDownloadFactory = $submitDownloadFactory;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id) {
        $logger = new MemoryLogger();
        $this->submitDownloadFactory->handleDownloadUploaded($this->getPresenter(), $logger, $id);
        FlashMessageDump::dump($logger, $this);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id) {
        $logger = new MemoryLogger();
        $this->submitDownloadFactory->handleDownloadCorrected($this->getPresenter(), $logger, $id);
        FlashMessageDump::dump($logger, $this);
    }
}
