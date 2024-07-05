<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class PersonEmailPreferenceOption extends FakeStringEnum implements EnumColumn
{
    public const SpamContest = 'spam_contest'; //phpcs:ignore
    public const SpamMff = 'spam_mff';//phpcs:ignore
    public const SpamOther = 'spam_other';//phpcs:ignore

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::SpamContest:
                return _('Spam Contest');
            case self::SpamMff:
                return _('Spam MFF');
            case self::SpamOther:
                return _('Spam Other');
        }
        throw new InvalidStateException();
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public static function cases(): array
    {
        return [
            new self(self::SpamContest),
            new self(self::SpamMff),
            new self(self::SpamOther),
        ];
    }
}