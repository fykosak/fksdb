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
final class PostgraduateStudyTest extends Test
{

    public function getTitle(): Title
    {
        return new Title(null, _('Postgraduate study'));
    }

    public function getDescription(): ?string
    {
        return _('Checks if any of postgraduate studies are not followed by undergraduate');
    }

    /**
     * @param PersonModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        $histories = $model->getHistories()->order('ac_year');
        /** @var PersonHistoryModel|null $postgraduate */
        $postgraduate = null;
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            if ($history->getGraduationYear() === null) {
                $postgraduate = $history;
                continue;
            }
            if ($postgraduate) {
                $logger->log(
                    new TestMessage(
                        $id,
                        sprintf(
                            'Found undergraduate study year %d after postgraduate',
                            $history->ac_year
                        ),
                        Message::LVL_ERROR
                    )
                );
            }
        }
    }

    public function getId(): string
    {
        return 'personPostgraduateStudy';
    }
}
