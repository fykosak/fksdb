<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\HashPrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Utils\Html;

class PasswordColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelFyziklaniTeam $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return (new HashPrinter())($model->password);
    }
}
