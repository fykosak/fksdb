<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;

class CtyrbojPreviewComponent extends PreviewComponent
{
    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'ctyrboj.latte');
    }
}
