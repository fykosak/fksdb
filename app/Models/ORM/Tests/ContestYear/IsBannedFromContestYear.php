<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\ContestYear;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\BannedPersonModel;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<ContestantModel>
 */
class IsBannedFromContestYear extends Test
{
    /**
     * @param ContestantModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        $person = $model->person;
        $contestYear = $model->getContestYear();
        /**
         * @var BannedPersonModel $ban
         */
        foreach ($person->getBans() as $ban) {
            if ($ban->getBanForContestYear($contestYear)) {
                $logger->log(new TestMessage(
                    $id,
                    sprintf(_('Person was banned from contest year, caseId: %s'), $ban->case_id),
                    Message::LVL_ERROR
                ));
            }
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Detect contestants banned from a contest year'));
    }

    public function getId(): string
    {
        return 'IsBannedEvent';
    }
}
