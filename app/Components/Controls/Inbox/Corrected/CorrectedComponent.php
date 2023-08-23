<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\Corrected;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Submits\FileSystemStorage\CorrectedStorage;

class CorrectedComponent extends SeriesTableComponent
{
    private CorrectedStorage $correctedStorage;

    final public function injectCorrectedStorage(CorrectedStorage $correctedStorage): void
    {
        $this->correctedStorage = $correctedStorage;
    }

    final public function render(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte',
            ['correctedSubmitStorage' => $this->correctedStorage]
        );
    }
}
