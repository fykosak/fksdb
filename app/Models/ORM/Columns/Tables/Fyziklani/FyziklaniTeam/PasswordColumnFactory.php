<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniTeam;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ValuePrinters\HashPrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use Nette\Utils\Html;

class PasswordColumnFactory extends ColumnFactory
{
    /**
     * @param TeamModel|TeamModel2 $model
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new HashPrinter())($model->password);
    }
}
