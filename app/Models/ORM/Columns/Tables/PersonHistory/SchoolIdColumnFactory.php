<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonHistoryModel,never>
 */
class SchoolIdColumnFactory extends ColumnFactory
{
    private SchoolFactory $schoolFactory;

    public function injectSchoolFactory(SchoolFactory $schoolFactory): void
    {
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
