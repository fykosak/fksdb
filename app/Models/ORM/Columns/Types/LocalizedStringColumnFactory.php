<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<Model>
 */
class LocalizedStringColumnFactory extends AbstractColumnFactory
{
    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(Model $model): Html
    {
        $localizedString = $model->{$this->modelAccessKey . '_' . $this->translator->lang};
        return Html::el('span')->addText($localizedString);
    }
}
