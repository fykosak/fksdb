<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\UI\PersonLink;
use Fykosak\NetteORM\Model;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PersonModel,never>
 */
class PersonLinkColumnFactory extends AbstractColumnFactory
{
    private LinkGenerator $linkGenerator;

    public function injectLink(LinkGenerator $linkGenerator): void
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param PersonModel $model
     * @throws InvalidLinkException
     */
    protected function createHtmlValue(Model $model): Html
    {
        return (new PersonLink($this->linkGenerator))($model);
    }
}
