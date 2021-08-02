<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Components\Controls\Loaders\StylesheetCollector;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

class SQLConsole extends TextArea
{

    protected const CSS_CLASS = 'sqlConsole';

    private bool $attachedJS = false;

    private bool $attachedCSS = false;

    /**
     * SQLConsole constructor.
     * @param null $label
     */
    public function __construct($label = null)
    {
        parent::__construct($label);
        $this->monitor(JavaScriptCollector::class, function (JavaScriptCollector $collector) {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $collector->registerJSFile('js/codemirror.min.js');
                $collector->registerJSFile('js/sqlconsole.js');
            }
        });
        $this->monitor(StylesheetCollector::class, function (StylesheetCollector $collector) {
            if (!$this->attachedCSS) {
                $this->attachedCSS = true;
                $collector->registerStylesheetFile('css/codemirror.css', ['screen', 'projection', 'tv']);
            }
        });
    }

    public function getControl(): Html
    {
        $control = parent::getControl();
        $control->class = self::CSS_CLASS;
        return $control;
    }
}
