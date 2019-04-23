<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\PhonePrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class PhoneRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PhoneRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone');
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $accessKey): Html {
        return (new PhonePrinter)($model->{$accessKey});
    }
}
