<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Closing;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;

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
