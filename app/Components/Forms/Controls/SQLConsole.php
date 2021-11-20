<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

class SQLConsole extends TextArea
{

    protected const CSS_CLASS = 'sqlConsole';

    public function getControl(): Html
    {
        $control = parent::getControl();
        $control->class = self::CSS_CLASS;
        return $control;
    }
}
