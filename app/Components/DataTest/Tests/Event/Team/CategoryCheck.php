<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event\Team;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Components\EntityForms\Fyziklani\FOFCategoryProcessing;
use FKSDB\Components\EntityForms\Fyziklani\FOLCategoryProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<TeamModel2>
 */
class CategoryCheck extends Test
{
    /**
     * @param TeamModel2 $model
     */
    public function run(Logger $logger, Model $model): void
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
                    new Message(
                        sprintf(
                            _('Wrong category actual %s calculated %s'),
                            $actual,
                            $calculated
                        ),
                        Message::LVL_ERROR
                    )
                );
            }
        } catch (\Throwable $exception) {
            $logger->log(
                new Message(
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
