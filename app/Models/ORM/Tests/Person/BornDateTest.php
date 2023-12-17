<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
final class BornDateTest extends Test
{
    private const UNIX_YEAR = 31556926;

    /**
     * @param PersonModel $model
     * @throws \Exception
     */
    public function run(TestLogger $logger, Model $model): void
    {
        $info = $model->getInfo();
        if (!$info || !$info->born) {
            return;
        }
        /** @var PersonHistoryModel $history */
        foreach ($model->getHistories() as $history) {
            $graduationYear = $history->getGraduationYear();
            if ($graduationYear) {
                $graduationDate = new \DateTime($graduationYear . '-06-01');
                $deltaYear = ($graduationDate->getTimestamp() - $info->born->getTimestamp()) / self::UNIX_YEAR;
                if (abs($deltaYear - 19) > 4) {
                    $logger->log(
                        new TestMessage(
                            sprintf(
                                _('Expected graduation at the age of %01.2f'),
                                $deltaYear
                            ),
                            Message::LVL_ERROR
                        )
                    );
                } elseif (abs($deltaYear - 19) > 2) {
                    $logger->log(
                        new TestMessage(
                            sprintf(
                                _('Expected graduation at the age of %01.2f'),
                                $deltaYear
                            ),
                            Message::LVL_WARNING
                        )
                    );
                }
            }
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Born date'));
    }

    public function getId(): string
    {
        return 'BornDate';
    }
}
