<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Submits\SubmitHandlerFactory;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class SubmitsPreviewControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitsPreviewControl extends SeriesTableComponent {

    private SubmitHandlerFactory $submitDownloadFactory;

    public function injectSubmitDownloadFactory(SubmitHandlerFactory $submitDownloadFactory): void {
        $this->submitDownloadFactory = $submitDownloadFactory;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
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
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleDownloadCorrected(int $id) {
        $logger = new MemoryLogger();
        $this->submitDownloadFactory->handleDownloadCorrected($this->getPresenter(), $logger, $id);
        FlashMessageDump::dump($logger, $this);
    }
}
