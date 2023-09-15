<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<Model,never>
 */
class LocalizedStringColumnFactory extends AbstractColumnFactory
{
    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(Model $model): Html
    {
        $localizedString = $model->{$this->modelAccessKey};
        if (!$localizedString instanceof LocalizedString) {
            throw new BadTypeException(LocalizedString::class, $localizedString);
        }
        return Html::el('span')->addText($localizedString->getText($this->translator->lang));
    }
}
