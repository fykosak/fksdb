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
class StudyYearTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Graduation years'));
    }

    public function getDescription(): ?string
    {
        return _('Compares graduation years of each year, checks if they are the same.');
    }

    /**
     * @param PersonModel $model
     */
    public function run(TestLogger $logger, Model $model): void
    {
        $histories = $model->getHistories()->order('ac_year');
        /** @var PersonHistoryModel[] $data */
        $data = [];
        /** @var PersonHistoryModel $current */
        foreach ($histories as $current) {
            if ($current->getGraduationYear()) {
                $data[] = $current;
            }
        }

        array_reduce($data, function (?PersonHistoryModel $last, PersonHistoryModel $datum) use ($logger) {
            if ($last && $last->getGraduationYear() !== $datum->getGraduationYear()) {
                $logger->log(
                    new TestMessage(
                        sprintf(
                            'In %d expected graduation "%s" given "%s"',
                            $datum->ac_year,
                            $last->getGraduationYear(),
                            $datum->getGraduationYear()
                        ),
                        Message::LVL_ERROR
                    )
                );
            }
            return $datum;
        }, null);
    }

    public function getId(): string
    {
        return 'PersonStudyYear';
    }
}