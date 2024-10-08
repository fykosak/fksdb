<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
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
            case 2:
            case 14:
                return 'dsef';
            case 3:
                return 'fav';
            case 4:
            case 5:
                return 'sous';
            case 6:
            case 7:
                return 'tsaf';
            case 9:
                return 'fol';
            case 16:
                return 'fov';
            default:
                return $this->contest->getContestSymbol();
        }
    }

    public function isOpenType(): bool
    {
        switch ($this->event_type_id) {
            case 1: // FOF
            case 9: // FOL
            case 11: // setkani 2x
            case 12:
            case 2: // DSEF 2x
            case 14:
                return true;
            default:
                return false;
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
                    'deadline' => Expect::type(\DateTimeInterface::class)->default('1990-09-09'),
                    'letterSignature' => Expect::string('Student Pilný'),
                    'letterDecisionDeadline' => Expect::type(\DateTimeInterface::class)->default('1990-09-09 00:00:00'),
                    'letterResolutionTime' => Expect::type(\DateTimeInterface::class)->default('1990-19-09 00:00:00'),
                ])->castTo('array')->otherItems(Expect::mixed());
            case 10:
                return Expect::structure([
                    'notifyBcc' => Expect::string('vyfuk@vyfuk.org'),
                    'notifyFrom' => Expect::string('Výfučí tábor <vyfuk@vyfuk.org>'),
                    'capacity' => Expect::int(29),
                    'letterSignature' => Expect::string('Student Pilný'),
                ])->castTo('array');
            case 11:
            case 12:
                return Expect::structure([
                    'capacity' => Expect::int(0),
                    'letterSignature' => Expect::string('Student Pilný'),
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
