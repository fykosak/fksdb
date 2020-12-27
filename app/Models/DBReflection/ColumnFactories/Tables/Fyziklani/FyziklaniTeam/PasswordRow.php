<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\HashPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
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
