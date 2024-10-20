<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

class SQLConsole extends TextArea
{
    protected const CSS_CLASS = 'sql-console';

    public function getControl(): Html
    {
        $this->getControlPrototype()->addAttributes(['class' => self::CSS_CLASS]);
        return parent::getControl();
    }
}
