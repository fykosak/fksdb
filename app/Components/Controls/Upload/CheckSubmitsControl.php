<?php

namespace FKSDB\Components\Controls\Upload;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemUploadedSubmitStorage;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Templating\FileTemplate;

/**
 * Class CheckSubmitsControl
 * @package FKSDB\Components\Controls\Upload
 * @property FileTemplate $template
 */
class CheckSubmitsControl extends BaseControl {
    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'CheckSubmitsControl.latte');
        $this->template->render();
    }

    /**
     * @throws AbortException
     */
    public function handleCheck() {
        /** @var FilesystemUploadedSubmitStorage $submitUploadedStorage */
        $submitUploadedStorage = $this->getContext()->getByType(FilesystemUploadedSubmitStorage::class);
        /** @var FilesystemCorrectedSubmitStorage $submitCorrectedStorage */
        $submitCorrectedStorage = $this->getContext()->getByType(FilesystemCorrectedSubmitStorage::class);
        /** @var ModelSubmit $submit */
        $errors = 0;
        foreach ($this->seriesTable->getSubmits() as $submit) {
            if ($submit->source === ModelSubmit::SOURCE_UPLOAD && !$submitUploadedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded submit #%d is broken'), $submit->submit_id), ILogger::ERROR);
            }

            if ($submit->corrected && !$submitCorrectedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Corrected submit #%d is broken'), $submit->submit_id), ILogger::ERROR);
            }
            if (!$submit->corrected && $submitCorrectedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded unregister corrected submit #%d'), $submit->submit_id), ILogger::ERROR);
            }
        }
        $this->flashMessage(sprintf(_('Test done, found %d errors'), $errors), $errors ? ILogger::WARNING : ILogger::SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}
