<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Application\IStylesheetCollector;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SQLConsole extends TextArea {

    protected const CSS_CLASS = 'sqlConsole';

    private bool $attachedJS = false;

    private bool $attachedCSS = false;

    /**
     * SQLConsole constructor.
     * @param null $label
     */
    public function __construct($label = null) {
        parent::__construct($label);
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $collector->registerJSFile('js/codemirror.min.js');
                $collector->registerJSFile('js/sqlconsole.js');
            }
        });
        $this->monitor(IStylesheetCollector::class, function (IStylesheetCollector $collector) {
            if (!$this->attachedCSS) {
                $this->attachedCSS = true;
                $collector->registerStylesheetFile('css/codemirror.css', ['screen', 'projection', 'tv']);
            }
        });
    }

    /**
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();
        $control->class = self::CSS_CLASS;

        return $control;
    }

}
