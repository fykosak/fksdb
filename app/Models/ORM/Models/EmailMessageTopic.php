<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

class EmailMessageTopic extends FakeStringEnum implements EnumColumn
{
    public const SpamContest = 'spam_contest'; //phpcs:ignore
    public const SpamMff = 'spam_mff';//phpcs:ignore
    public const SpamOther = 'spam_other';//phpcs:ignore
    public const Contest = 'contest';//phpcs:ignore
    public const FOF = 'fof';
    public const FOL = 'fol';
    public const DSEF = 'dsef';
    public const Internal = 'internal';//phpcs:ignore

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    public function label(): string
    {
        return '';
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public static function cases(): array
    {
        return [];
    }

    public function isSpam(): bool
    {
        switch ($this->value) {
            case self::SpamContest:
            case self::SpamMff:
            case self::SpamOther:
                return true;
        }
        return false;
    }

    public function mapToPreference(): ?PersonEmailPreferenceOption
    {
        switch ($this->value) {
            case self::SpamContest:
                return PersonEmailPreferenceOption::from(PersonEmailPreferenceOption::SpamContest);
            case self::SpamMff:
                return PersonEmailPreferenceOption::from(PersonEmailPreferenceOption::SpamMff);
            case self::SpamOther:
                return PersonEmailPreferenceOption::from(PersonEmailPreferenceOption::SpamOther);
        }
        return null;
    }
}
