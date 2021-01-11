<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Columns\MetaDataFactory;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SchoolIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolIdColumnFactory extends DefaultColumnFactory {

    private SchoolFactory $schoolFactory;

    public function __construct(SchoolFactory $schoolFactory, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->schoolFactory = $schoolFactory;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws NotImplementedException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        throw new NotImplementedException();
    }

    protected function createFormControl(...$args): BaseControl {
        return $this->schoolFactory->createSchoolSelect();
    }
}
