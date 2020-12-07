<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\HashPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

/**
 * Class PasswordRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PasswordRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelFyziklaniTeam $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new HashPrinter())($model->password);
    }
}
