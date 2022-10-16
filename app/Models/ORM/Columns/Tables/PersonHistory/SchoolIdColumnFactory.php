<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\MetaDataFactory;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class SchoolIdColumnFactory extends ColumnFactory
{
    private SchoolFactory $schoolFactory;

    public function __construct(SchoolFactory $schoolFactory, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->schoolFactory = $schoolFactory;
    }

    /**
     * @throws NotImplementedException
     */
    protected function createHtmlValue(Model $model): Html
    {
        throw new NotImplementedException();
    }

    protected function createFormControl(...$args): BaseControl
    {
        return $this->schoolFactory->createSchoolSelect();
    }
}
