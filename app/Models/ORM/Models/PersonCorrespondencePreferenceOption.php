<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Localization\LangMap;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class PersonCorrespondencePreferenceOption extends FakeStringEnum implements EnumColumn
{
    public const SpamContest = 'spam_contest'; //phpcs:ignore
    public const SpamMff = 'spam_mff';//phpcs:ignore
    public const SpamOther = 'spam_other';//phpcs:ignore
    public const SpamPost = 'spam_post';//phpcs:ignore

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function behaviorType(): string
    {
        return 'primary';
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::SpamContest:
                return _('Our events');
            case self::SpamMff:
                return _('Events of MFF CUNI');
            case self::SpamOther:
                return _('Other related events');
            case self::SpamPost:
                return _('Postal mail');
        }
        throw new InvalidStateException();
    }

    /**
     * @phpstan-return LangMap<'cs'|'en',string>
     */
    public function description(): LangMap
    {
        switch ($this->value) {
            case self::SpamContest:
                return new LangMap([
                    'cs' => 'Informace o seminářích a akcích pořádaných FYKOSem a Výfukem',
                    'en' => 'Information about competitions and events of FYKOS and Výfuk',
                ]);
            case self::SpamMff:
                return new LangMap([
                    'cs' => 'Informace o akcích, seminářích a táborech pořádaných ostatními semináři nebo MFF UK',
                    'en' => 'Information about events, competitions and camps organized by other seminars or MFF CUNI',
                ]);
            case self::SpamOther:
                return new LangMap([
                    'cs' => 'Relevantní informace od našich partnerů',
                    'en' => 'Relevant information from our partners',
                ]);
            case self::SpamPost:
                return new LangMap([
                    'cs' => 'Letáčky a další materiály zasílané fyzicky poštou',
                    'en' => 'Posters and other materials sent by post'
                ]);
        }
        throw new InvalidStateException();
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    /**
     * @return self[]
     */
    public static function emailCases(): array
    {
        return [
            new self(self::SpamContest),
            new self(self::SpamMff),
            new self(self::SpamOther),
        ];
    }
    public static function cases(): array
    {
        return [
            new self(self::SpamContest),
            new self(self::SpamMff),
            new self(self::SpamOther),
            new self(self::SpamPost),
        ];
    }
}
