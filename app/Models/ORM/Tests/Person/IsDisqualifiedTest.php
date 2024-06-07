<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\DisqualifiedPersonModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class IsDisqualifiedTest extends Test
{
    /**
     * @param PersonModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        /**
         * @var DisqualifiedPersonModel $disqualification
         */
        foreach ($model->getDisqualifications() as $disqualification) {
            $logger->log(new TestMessage(
                $id,
                sprintf(_('Person was disqualified  caseId:%s'), $disqualification->case_id),
                Message::LVL_WARNING
            ));
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Has ever had any ban?'));
    }

    public function getId(): string
    {
        return 'IsDisqualified';
    }
}