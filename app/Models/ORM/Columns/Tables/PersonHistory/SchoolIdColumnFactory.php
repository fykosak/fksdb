<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\LinkGenerator;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonHistoryModel,never>
 */
class SchoolIdColumnFactory extends ColumnFactory
{
    private SchoolProvider $schoolProvider;
    private LinkGenerator $linkGenerator;

    public function injectSchoolFactory(SchoolProvider $schoolProvider, LinkGenerator $linkGenerator): void
    {
        $this->schoolProvider = $schoolProvider;
        $this->linkGenerator = $linkGenerator;
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
        return new SchoolSelectField($this->schoolProvider, $this->linkGenerator);
    }
}
