<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class ParticipantsDurationTest extends Test
{

    private const CONTESTS = [
        ContestModel::ID_FYKOS => [5, 6],
        ContestModel::ID_VYFUK => [5, 6],
    ];

    public function getTitle(): Title
    {
        return new Title(null, _('Participation duration'));
    }

    public function getDescription(): ?string
    {
        return _('Check how long person participated in events of the contest.');
    }

    /**
     * @param PersonModel $model
     */
    public function run(TestLogger $logger, Model $model): void
    {
        $data = [
            'event participant' => EventCoveringTest::getEventParticipant($model),
            'team member' => EventCoveringTest::getTeamMember($model, true),
            'contestant' => EventCoveringTest::getContestant($model),
        ];
        foreach (self::CONTESTS as $contestId => $thresholds) {
            $max = null;
            $min = null;
            foreach ($data as $datum) {
                if (isset($datum[$contestId])) {
                    $localMax = max(array_keys($datum[$contestId]));
                    $localMin = min(array_keys($datum[$contestId]));

                    $max = (is_null($max) || $max < $localMax) ? $localMax : $max;
                    $min = (is_null($min) || $min > $localMin) ? $localMin : $min;
                }
            }

            $delta = ($max - $min) + 1;
            if ($delta < $thresholds[0]) {
                continue;
            }
            if ($delta < $thresholds[1]) {
                $level = Message::LVL_WARNING;
            } else {
                $level = Message::LVL_ERROR;
            }
            $logger->log(
                new TestMessage(
                    \sprintf(_('Person participated %d years in contestId %d (and its events)'), $delta, $contestId),
                    $level
                )
            );
        }
    }

    public function getId(): string
    {
        return 'PersonParticipantsDuration';
    }
}
