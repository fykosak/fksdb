<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonHistoryModel,never>
 */
class SchoolIdColumnFactory extends ColumnFactory
{
    private Container $container;
    private LinkGenerator $linkGenerator;

    public function injectSchoolFactory(Container $container, LinkGenerator $linkGenerator): void
    {
        $this->container = $container;
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @throws NotImplementedException
     */
    protected function createHtmlValue(Model $model): Html
    {
        throw new NotImplementedException();
    }

    /**
     * @throws InvalidLinkException
     */
    protected function createFormControl(...$args): BaseControl
    {
        return new SchoolSelectField($this->container, $this->linkGenerator);
    }
}
