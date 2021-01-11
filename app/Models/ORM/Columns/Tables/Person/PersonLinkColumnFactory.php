<?php

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ValuePrinters\PersonLink;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonLinkColumnFactory extends ColumnFactory {

    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new PersonLink($this->presenterComponent))($model);
    }
}
