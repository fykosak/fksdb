<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\ValuePrinters\HashPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class PasswordRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class PasswordRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Password');
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @param string $fieldNam
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldNam): Html {
        return (new HashPrinter)($model->password);
    }
}
