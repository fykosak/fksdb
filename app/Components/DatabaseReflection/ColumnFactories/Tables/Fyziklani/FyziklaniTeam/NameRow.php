<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class NameRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO fix input
 */
class NameRow extends AbstractFyziklaniTeamRow {

    public function getTitle(): string {
        return _('Team name');
    }

    protected function getModelAccessKey(): string {
        return 'name';
    }

    /* TODO fix it
     *   public function createField(...$args): BaseControl {
     *       $control = new TextInput($this->getTitle());
     *       $control->addRule(Form::PATTERN, _('Název týmu smí obsahovat pouze latinku, řečtinu, cyrilici a ASCII znaky.'), '/^[\p{Latin}\p{Greek}\p{Cyrillic}\x{0020}-\x{00FF}]+$/u');
     *       return $control;
     *   }
     * */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
