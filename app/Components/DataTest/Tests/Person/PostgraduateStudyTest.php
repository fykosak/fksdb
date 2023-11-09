<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class PostgraduateStudyTest extends Test
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
    public function run(Logger $logger, Model $model): void
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
                    new Message(
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
        return 'PersonPostgraduateStudy';
    }
}
