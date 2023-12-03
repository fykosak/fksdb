<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Contestant;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\Results\ResultsModelFactory;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;

/**
 * @phpstan-extends Test<ContestantModel>
 */
class InvalidCategory extends Test
{
    /**
     * @throws BadRequestException
     */
    public function run(Logger $logger, Model $model): void
    {
        $evaluationStrategy = ResultsModelFactory::findEvaluationStrategy($this->container, $model->getContestYear());
        try {
            $expected = $evaluationStrategy->studyYearsToCategory($model->person);
            if ($model->contest_category->contest_category_id !== $expected->contest_category_id) {
                $logger->log(
                    new Message(
                        sprintf(
                            _('Invalid category, expected: %s, given: %s.'),
                            $expected->label,
                            $model->contest_category->label
                        ),
                        Message::LVL_WARNING
                    )
                );
            }
        } catch (InvalidArgumentException $exception) {
            $logger->log(
                new Message(
                    _('Invalid category, check study year!'),
                    Message::LVL_WARNING
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Invalid category'));
    }

    public function getId(): string
    {
        return 'InactiveContest';
    }
}
