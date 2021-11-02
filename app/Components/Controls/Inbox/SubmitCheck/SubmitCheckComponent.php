<?php

namespace FKSDB\Components\Controls\Inbox\SubmitCheck;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Models\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class SubmitCheckComponent extends BaseComponent {

    private SeriesTable $seriesTable;

    private CorrectedStorage $correctedStorage;

    private UploadedStorage $uploadedStorage;

    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
    }

    final public function injectPrimary(UploadedStorage $uploadedStorage, CorrectedStorage $correctedStorage): void {
        $this->uploadedStorage = $uploadedStorage;
        $this->correctedStorage = $correctedStorage;
    }

    final public function render(): void {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    public function handleCheck(): void {
        /** @var ModelSubmit $submit */
        $errors = 0;
        foreach ($this->seriesTable->getSubmits() as $submit) {
            if ($submit->source === ModelSubmit::SOURCE_UPLOAD && !$this->uploadedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded submit #%d is broken'), $submit->submit_id), Message::LVL_ERROR);
            }

            if ($submit->corrected && !$this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Corrected submit #%d is broken'), $submit->submit_id), Message::LVL_ERROR);
            }
            if (!$submit->corrected && $this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(sprintf(_('Uploaded unregister corrected submit #%d'), $submit->submit_id), Message::LVL_ERROR);
            }
        }
        $this->flashMessage(sprintf(_('Test done, found %d errors'), $errors), $errors ? Message::LVL_WARNING : Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }
}
