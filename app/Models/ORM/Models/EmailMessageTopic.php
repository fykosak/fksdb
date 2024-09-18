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
    public const Fykos = 'fykos';//phpcs:ignore
    public const Vyfuk = 'vyfuk';//phpcs:ignore
    public const FOF = 'fof';
    public const FOL = 'fol';
    public const DSEF = 'dsef';
    public const Internal = 'internal';//phpcs:ignore

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::SpamOther:
            case self::SpamMff:
            case self::SpamContest:
                return 'warning';
            case self::Fykos:
                return 'fykos';
            case self::Vyfuk:
                return 'vyfuk';
            case self::FOF:
                return 'fof';
            case self::FOL:
                return 'fol';
            case self::DSEF:
                return 'dsef';
            default:
            case self::Internal:
                return 'secondary';
        }
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
            case self::Fykos:
                return _('FYKOS');
            case self::Vyfuk:
                return _('Výfuk');
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
            new self(self::Fykos),
            new self(self::Vyfuk),
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

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getReason(): LocalizedString
    {
        switch ($this->value) {
            case self::Fykos:
                return new LocalizedString([
                    'cs' => 'Tento mail dostávate pretože ste prihlasený do semináru FYKOS, 
                    souteže Výfuku, na soustředení FYKOSu alebo tábor Výfuku.',
                    'en' => '', // TODO
                ]);
            case self::Vyfuk:
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
