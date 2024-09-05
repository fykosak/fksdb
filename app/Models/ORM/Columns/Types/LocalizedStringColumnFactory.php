<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LangMap;
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
        $langMap = $model->{$this->modelAccessKey};
        if (!$langMap instanceof LangMap) {
            throw new BadTypeException(LangMap::class, $langMap);
        }
        return Html::el('span')->addText($this->translator->getVariant($langMap));
    }
}
