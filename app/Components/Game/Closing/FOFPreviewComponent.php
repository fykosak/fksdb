<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;

class FOFPreviewComponent extends PreviewComponent
{
    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->task = $this->handler->getNextTask($this->team);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fof.latte');
    }
}
