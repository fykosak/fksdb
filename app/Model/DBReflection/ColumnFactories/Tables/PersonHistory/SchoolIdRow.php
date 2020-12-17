<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\DBReflection\MetaDataFactory;
use FKSDB\Model\Exceptions\NotImplementedException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SchoolIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolIdRow extends DefaultColumnFactory {

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