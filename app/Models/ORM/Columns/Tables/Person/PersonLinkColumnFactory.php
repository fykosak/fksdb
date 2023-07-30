<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ValuePrinters\PersonLink;
use Fykosak\NetteORM\Model;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonModel>
 */
class PersonLinkColumnFactory extends ColumnFactory
{
    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param PersonModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new PersonLink($this->presenterComponent))($model);
    }
}
