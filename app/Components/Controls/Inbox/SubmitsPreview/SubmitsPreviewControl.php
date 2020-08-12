<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\Messages\Message;
use FKSDB\Submits\StorageException;
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

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id): void {
        try {
            $this->submitDownloadFactory->handleDownloadUploaded($this->getPresenter(), $id);
        } catch (ForbiddenRequestException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (NotFoundException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (StorageException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        }
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id): void {
        try {
            $this->submitDownloadFactory->handleDownloadCorrected($this->getPresenter(), $id);
        } catch (ForbiddenRequestException$exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (NotFoundException$exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        } catch (StorageException $exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        }
    }
}
