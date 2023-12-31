<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\School;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<SchoolModel>
 */
final class StudyYearFillTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Filled study years'));
    }

    public function getDescription(): ?string
    {
        return _('Check if school has filled any of study types (study_* fields)');
    }

    /**
     * @param SchoolModel $model
     */
    public function run(TestLogger $logger, Model $model): void
    {
        if ($model->active && !$model->study_p && !$model->study_h && !$model->study_u) {
            $logger->log(new TestMessage($this->formatId($model), _('Missing study years'), Message::LVL_ERROR));
        }
    }

    public function getId(): string
    {
        return 'schoolStudyYearFill';
    }
}
