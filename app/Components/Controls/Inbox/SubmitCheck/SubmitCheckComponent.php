<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\SubmitCheck;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitSource;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;
use FKSDB\Models\Submits\FileSystemStorage\UploadedStorage;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class SubmitCheckComponent extends BaseComponent
{
    private CorrectedStorage $correctedStorage;
    private UploadedStorage $uploadedStorage;
    private SubmitService $submitService;
    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(Container $context, ContestYearModel $contestYear, int $series)
    {
        parent::__construct($context);
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    final public function injectPrimary(
        UploadedStorage $uploadedStorage,
        CorrectedStorage $correctedStorage,
        SubmitService $submitService
    ): void {
        $this->uploadedStorage = $uploadedStorage;
        $this->correctedStorage = $correctedStorage;
        $this->submitService = $submitService;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    public function handleCheck(): void
    {
        $errors = 0;
        /** @var SubmitModel $submit */
        foreach ($this->submitService->getForContestYear($this->contestYear, $this->series) as $submit) {
            if ($submit->source->value === SubmitSource::UPLOAD && !$this->uploadedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(
                    sprintf(_('Uploaded submit #%d is broken'), $submit->submit_id),
                    Message::LVL_ERROR
                );
            }

            if (!$submit->isQuiz() && $submit->corrected && !$this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(
                    sprintf(_('Corrected submit #%d is broken'), $submit->submit_id),
                    Message::LVL_ERROR
                );
            }
            if (!$submit->corrected && $this->correctedStorage->fileExists($submit)) {
                $errors++;
                $this->flashMessage(
                    sprintf(_('Uploaded unregister corrected submit #%d'), $submit->submit_id),
                    Message::LVL_ERROR
                );
            }
        }
        $this->flashMessage(
            sprintf(ngettext("Test done, found %d error", 'Test done, found %d errors', $errors), $errors),
            $errors ? Message::LVL_WARNING : Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }
}
