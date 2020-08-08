<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\DI\Container;

/**
 * Class SubmitCheckComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitCheckComponent extends BaseComponent {

    /** @var SeriesTable */
    private $seriesTable;

    /** @var CorrectedStorage */
    private $correctedStorage;

    /** @var UploadedStorage */
    private $uploadedStorage;

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param UploadedStorage $uploadedStorage
     * @param CorrectedStorage $correctedStorage
     * @return void
     */
    public function injectPrimary(UploadedStorage $uploadedStorage, CorrectedStorage $correctedStorage) {
        $this->uploadedStorage = $uploadedStorage;
        $this->correctedStorage = $correctedStorage;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }

    /**
     * @throws AbortException
     */
    public function handleCheck() {
        /** @var ModelSubmit $submit */
        $errors = 0;
        foreach ($this->seriesTable->getSubmits() as $submit) {
            if ($submit->source === ModelSubmit::SOURCE_UPLOAD && !$this->uploadedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded submit #%d is broken'), $submit->submit_id), ILogger::ERROR);
            }

            if ($submit->corrected && !$this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Corrected submit #%d is broken'), $submit->submit_id), ILogger::ERROR);
            }
            if (!$submit->corrected && $this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded unregister corrected submit #%d'), $submit->submit_id), ILogger::ERROR);
            }
        }
        $this->flashMessage(sprintf(_('Test done, found %d errors'), $errors), $errors ? ILogger::WARNING : ILogger::SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}
