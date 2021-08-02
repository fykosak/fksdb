<?php

namespace FKSDB\Components\Controls\Inbox\SubmitsPreview;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

class SubmitsPreviewComponent extends SeriesTableComponent
{

    private SubmitHandlerFactory $submitHandlerFactory;

    final public function injectSubmitDownloadFactory(SubmitHandlerFactory $submitHandlerFactory): void
    {
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    /**
     * @param int $id
     * @return void
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        }
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadCorrected($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException$exception) {
            $this->flashMessage(new Message($exception->getMessage(), Message::LVL_DANGER));
        }
    }
}
