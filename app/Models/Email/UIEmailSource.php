<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\Forms\Form;

/**
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of array
 * @phpstan-extends EmailSource<TTemplateParam,TSchema>
 */
abstract class UIEmailSource extends EmailSource
{
    abstract public function title(): Title;

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    abstract public function description(): LocalizedString;//@phpstan-ignore-line

    abstract public function creatForm(Form $form): void;
}
