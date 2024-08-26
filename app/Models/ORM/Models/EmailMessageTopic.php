<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class EmailMessageTopic extends FakeStringEnum implements EnumColumn
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
        switch ($this->value) {
            case self::SpamContest:
                return _('Spam contest');
            case self::SpamMff:
                return _('Spam MFF');
            case self::SpamOther:
                return _('Spam others');
            case self::Contest:
                return _('Contest');
            case self::FOF:
                return _('FOF');
            case self::FOL:
                return _('FOL');
            case self::DSEF:
                return _('DSEF');
            case self::Internal:
                return _('Internal');
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
            new self(self::Contest),
            new self(self::FOF),
            new self(self::FOL),
            new self(self::DSEF),
            new self(self::Internal),
        ];
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

    public function getReason(): LocalizedString
    {
        switch ($this->value) {
            case self::Contest:
                return new LocalizedString([
                    'cs' => 'Tento mail dostávate pretože ste prihlasený do semináru FYKOS, 
                    souteže Výfuku, na soustředení FYKOSu alebo tábor Výfuku.',
                    'en' => '', // TODO
                ]);
            case self::FOL:
                return new LocalizedString([
                    'cs' => '',// TODO
                    'en' => '',// TODO
                ]);
            case self::FOF:
                return new LocalizedString([
                    'cs' => '',// TODO
                    'en' => '',// TODO
                ]);
            case self::DSEF:
                return new LocalizedString([
                    'cs' => '',// TODO
                    'en' => '',// TODO
                ]);
            case self::Internal:
                return new LocalizedString([
                    'cs' => 'Tento mail ste dostali pretože ste on požiadali.',
                    'en' => '',
                ]);
        }
        throw new InvalidStateException();
    }
}
