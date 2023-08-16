<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\OmittedControlException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model,never>
 */
class LocalizedStringColumnFactory extends ColumnFactory
{
    private GettextTranslator $translator;

    public function inject(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }

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

    protected function createFormControl(...$args): BaseControl
    {
        throw new OmittedControlException();
    }
}
