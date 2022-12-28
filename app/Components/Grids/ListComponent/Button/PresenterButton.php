<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Button;

class PresenterButton extends DefaultButton
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'presenter.latte';
    }
}
