<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int $contestant_id
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $year
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read \DateTimeInterface $created
 * @property-read int $contest_category_id
 * @property-read ContestCategoryModel|null $contest_category
 */
final class ContestantModel extends Model implements Resource
{
    public const RESOURCE_ID = 'contestant';

    public function getContestYear(): ContestYearModel
    {
        return $this->contest->getContestYear($this->year);
    }

    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->person->getHistoryByContestYear($this->getContestYear());
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getSubmits(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SUBMIT, 'contestant_id');//@phpstan-ignore-line
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getSubmitsForSeries(int $series): TypedGroupedSelection
    {
        return $this->getSubmits()->where('task.series', $series);
    }

    public function getSubmitForTask(TaskModel $task): ?SubmitModel
    {
        /** @var SubmitModel|null $submit */
        $submit = $this->getSubmits()->where('task_id', $task->task_id)->fetch();
        return $submit;
    }

    public function getAnswer(SubmitQuestionModel $question): ?SubmitQuestionAnswerModel
    {
        /** @var SubmitQuestionAnswerModel|null $answer */
        $answer = $this->related(DbNames::TAB_SUBMIT_QUESTION_ANSWER, 'contestant_id')
            ->where('submit_question_id', $question->submit_question_id)
            ->fetch();
        return $answer;
    }
}
