<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\SubmitsPreview;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\Logging\Message;
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
     * @throws BadRequestException
     */
    public function handleDownloadUploaded(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadUploaded($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    /**
     * @throws BadRequestException
     */
    public function handleDownloadCorrected(int $id): void
    {
        try {
            $submit = $this->submitHandlerFactory->getSubmit($id);
            $this->submitHandlerFactory->handleDownloadCorrected($this->getPresenter(), $submit);
        } catch (ForbiddenRequestException | NotFoundException | StorageException$exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }
}
