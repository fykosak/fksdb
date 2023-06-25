<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @property-read int $event_type_id
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read string $name
 */
class EventTypeModel extends Model
{
    public function getEvents(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT, 'event_type_id');
    }

    public function getSymbol(): string
    {
        return match ($this->event_type_id) {
            1 => 'fof',
            9 => 'fol',
            2, 14 => 'dsef',
            16 => 'fov',
            default => 'secondary'
        };
    }

    public function getParamSchema(): Schema
    {
        switch ($this->event_type_id) {
            default:
                return Expect::null();
            case 15:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.mff.cuni.cz'),
                    'notifyFrom' => Expect::string(' Výfučí Kyber Koncil <vyfuk@vyfuk.mff.cuni.cz>'),
                    'capacity' => Expect::int(0),
                ])->castTo('array');
            case 2:
            case 14:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vercah@fykos.cz'),
                    'notifyFrom' => Expect::string('Den s experimentální fyzikou <dsef@fykos.cz>'),
                ])->castTo('array');
            case 16:
                return Expect::structure([
                    'notifyBcc' => Expect::string('soustredeni@fykos.cz'),
                    'notifyFrom' => Expect::string('soustredeni@fykos.cz'),
                    'deadline' => Expect::type(\DateTimeInterface::class)->default(
                        new \DateTime('2021-05-09 23:59:00')
                    ),
                    'letterWhere' => Expect::string('nikde'),
                    'letterSignature' => Expect::string('Student Pilný'),
                    'letterResolutionTime' => Expect::string('až naprší a uschne'),
                ])->castTo('array');
            case 11:
            case 12:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.mff.cuni.cz'),
                    'notifyFrom' => Expect::string('Setkáni řešitelů Výfuku <vyfuk@vyfuk.mff.cuni.cz>'),
                    'capacity' => Expect::int(30),
                ])->castTo('array');
            case 3:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vaf@fykos.cz'),
                    'notifyFrom' => Expect::string('Víkend s aplikovanou fyzikou <vaf@fykos.cz>'),
                    'capacity' => Expect::int(0),
                ])->castTo('array');
            case 4:
            case 5:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vercah@fykos.cz'),
                    'notifyFrom' => Expect::string('FYKOSí Soustředění <soustredeni@fykos.cz>'),
                    'letterWhere' => Expect::string('Hejnice'),
                    'deadline' => Expect::type(\DateTimeInterface::class)->default(2022 - 04 - 04),
                    'letterSignature' => Expect::string('Veronika Hendrychová'),
                    'letterDecisionDeadline' => Expect::type(\DateTimeInterface::class)->default(2022 - 04 - 04),
                    'letterResolutionTime' => Expect::type(\DateTimeInterface::class)->default(2022 - 04 - 06),
                ])->castTo('array');
            case 10:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.mff.cuni.cz'),
                    'notifyFrom' => Expect::string('Výfučí tábor <vyfuk@vyfuk.mff.cuni.cz>'),
                    'capacity' => Expect::int(29),
                    'letterWhere' => Expect::string('nikde'),
                    'letterSignature' => Expect::string('Student Pilný'),
                ])->castTo('array');
        }
    }
}
