<?php

namespace FKSDB\DBReflection\ColumnFactories\Person;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonLinkRow extends DefaultColumnFactory {

    private LinkGenerator $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param LinkGenerator $presenterComponent
     * @param MetaDataFactory $metaDataFactory
     */
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
