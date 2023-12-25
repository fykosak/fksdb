<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Contestant;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ContestantModel>
 */
class ConflictRole extends Test
{
    /**
     * @param ContestantModel $model
     */
    public function run(TestLogger $logger, Model $model): void
    {
        self::checkEventOrganizer($model, $logger);
        self::checkOrganizer($model, $logger);
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Conflict role'));
    }

    public function getId(): string
    {
        return 'ConflictRole';
    }

    private function checkEventOrganizer(ContestantModel $contestant, TestLogger $logger): void
    {
        $contestYear = $contestant->getContestYear();
        /** @var EventOrganizerModel $eventOrganizer */
        foreach ($contestant->person->getEventOrganizers() as $eventOrganizer) {
            $eventContestYear = $eventOrganizer->event->getContestYear();
            if (
                $eventContestYear->contest_id === $contestYear->contest_id
                && $eventContestYear->year === $contestYear->year
            ) {
                $logger->log(
                    new TestMessage(
                        sprintf(
                            _('Conflict role Contestant and EventOrganizer on event %s(%s)'),
                            $eventOrganizer->event->name,
                            $eventOrganizer->event_id
                        ),
                        Message::LVL_ERROR
                    ),
                );
                return;
            }
        }
    }

    private static function checkOrganizer(ContestantModel $contestant, TestLogger $logger): void
    {
        $contestYear = $contestant->getContestYear();
        /** @var OrganizerModel $organizer */
        foreach ($contestant->person->getOrganizers() as $organizer) {
            $contestYears[$organizer->contest_id] = [];
            foreach (range($organizer->since, $organizer->until ?? $organizer->contest->getLastYear()) as $year) {
                $organizerContestYear = $organizer->contest->getContestYear($year);
                if (
                    $organizerContestYear->contest_id === $contestYear->contest_id
                    && $organizerContestYear->year === $contestYear->year
                ) {
                    $logger->log(
                        new TestMessage(
                            _('Conflict role Contestant and Organizer'),
                            Message::LVL_ERROR
                        ),
                    );
                    return;
                }
            }
        }
    }
}
