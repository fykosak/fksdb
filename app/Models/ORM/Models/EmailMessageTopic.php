<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

enum EmailMessageTopic: string implements EnumColumn
{
    case SpamContest = 'spam_contest';
    case SpamMff = 'spam_mff';
    case SpamOther = 'spam_other';
    case Fykos = 'fykos';
    case Vyfuk = 'vyfuk';
    case FOF = 'fof';
    case FOL = 'fol';
    case DSEF = 'dsef';
    case Internal = 'internal';

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function behaviorType(): string
    {
        switch ($this) {
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
        switch ($this) {
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

    public function isSpam(): bool
    {
        return match ($this) {
            self::SpamContest, self::SpamMff, self::SpamOther => true,
            default => false,
        };
    }

    public function mapToPreference(): ?PersonCorrespondencePreferenceOption
    {
        return match ($this) {
            self::SpamContest => PersonCorrespondencePreferenceOption::SpamContest,
            self::SpamMff => PersonCorrespondencePreferenceOption::SpamMff,
            self::SpamOther => PersonCorrespondencePreferenceOption::SpamOther,
            default => null,
        };
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getReason(): LocalizedString
    {
        switch ($this) {
            case self::Fykos:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali proto, že jste účastníkem semináře FYKOS,
                     případně FYKOSího soustředění.',
                    'en' => 'You received this email because you are a participant in the FYKOS competition.',
                ]);
            case self::Vyfuk:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali proto, že jste účastníkem semináře Výfuk,
                     případně Výfučího tábora.',
                    'en' => 'You received this email because you are a participant in the Výfuk competition.',
                ]);
            case self::FOL:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali proto, že jste zaregistrováni do soutěže Fyziklání Online.',
                    'en' => 'You received this email because you are registered for the Physics Brawl Online.',
                ]);
            case self::FOF:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali proto, že jste zaregistrováni do soutěže Fyziklání.',
                    'en' => 'You received this email because you are registered for the Fyziklani
                    competition in Prague.',
                ]);
            case self::DSEF:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali proto, že jste zaregistrováni na DSEF.',
                    'en' => 'You received this email because you are registered to DSEF.',
                ]);
            case self::Internal:
                return new LocalizedString([
                    'cs' => 'Tento mail jste dostali, protože jste ho potřebovali.',
                    'en' => 'You received this email because you needed it.',
                ]);
        }
        throw new InvalidStateException();
    }
}
