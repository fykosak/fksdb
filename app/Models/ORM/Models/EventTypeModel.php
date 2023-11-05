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
final class EventTypeModel extends Model
{
    /**
     * @phpstan-return TypedGroupedSelection<EventModel>
     */
    public function getEvents(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT, 'event_type_id');
        return $selection;
    }

    public function getSymbol(): string
    {
        switch ($this->event_type_id) {
            case 1:
                return 'fof';
            case 9:
                return 'fol';
            case 2:
            case 14:
                return 'dsef';
            case 16:
                return 'fov';
            default:
                return $this->contest->getContestSymbol();
        }
    }

    public function getParamSchema(): Schema
    {
        switch ($this->event_type_id) {
            default:
                return Expect::null();
            case 1:
            case 2:
            case 14:
                return Expect::structure([
                    'hashSalt' => Expect::string(),
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
                    'deadline' => Expect::type(\DateTimeInterface::class)->default('2022-04-04'),
                    'letterSignature' => Expect::string('Veronika Hendrychová'),
                    'letterDecisionDeadline' => Expect::type(\DateTimeInterface::class)->default('2022-04-04'),
                    'letterResolutionTime' => Expect::type(\DateTimeInterface::class)->default('2022-04-06'),
                ])->castTo('array');
            case 10:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.org'),
                    'notifyFrom' => Expect::string('Výfučí tábor <vyfuk@vyfuk.org>'),
                    'capacity' => Expect::int(29),
                    'letterWhere' => Expect::string('nikde'),
                    'letterSignature' => Expect::string('Student Pilný'),
                ])->castTo('array');
            case 11:
            case 12:
                return Expect::structure([
                    'capacity' => Expect::int(0),
                ])->castTo('array');
            case 15:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.org'),
                    'notifyFrom' => Expect::string(' Výfučí Kyber Koncil <vyfuk@vyfuk.org>'),
                    'capacity' => Expect::int(0),
                ])->castTo('array');
        }
    }
}
