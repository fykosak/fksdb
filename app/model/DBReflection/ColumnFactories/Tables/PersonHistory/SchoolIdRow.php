<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
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
