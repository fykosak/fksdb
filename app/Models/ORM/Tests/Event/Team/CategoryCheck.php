<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Components\EntityForms\Fyziklani\Processing\Category\FOFCategoryProcessing;
use FKSDB\Components\EntityForms\Fyziklani\Processing\Category\FOLCategoryProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<TeamModel2>
 */
final class CategoryCheck extends Test
{
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if ($model->event->event_type_id === 1) {
            $processing = new FOFCategoryProcessing($this->container);
        } elseif ($model->event->event_type_id === 9) {
            $processing = new FOLCategoryProcessing($this->container);
        } else {
            return;
        }
        try {
            $actual = $model->category->value;
            $calculated = $processing->test($model)->value;
            if ($actual !== $calculated) {
                $logger->log(
                    new TestMessage(
                        $id,
                        sprintf(
                            _('Wrong category, actual %s calculated %s'),
                            $actual,
                            $calculated
                        ),
                        Message::LVL_ERROR
                    )
                );
            }
        } catch (\Throwable $exception) {
            $logger->log(
                new TestMessage(
                    $id,
                    $exception->getMessage(),
                    Message::LVL_ERROR
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Category check'), 'fas fa-poo');
    }

    public function getId(): string
    {
        return 'teamCategory';
    }
}
