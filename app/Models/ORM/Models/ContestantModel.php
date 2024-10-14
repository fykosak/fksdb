<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\ContestYearResource;
use FKSDB\Models\Authorization\Roles\ContestYearRole;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Tests\Contestant\ConflictRole;
use FKSDB\Models\ORM\Tests\Contestant\InvalidCategory;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

/**
 * @property-read int $contestant_id
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $year
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int|null $contest_category_id
 * @property-read ContestCategoryModel|null $contest_category
 * @property-read DateTime $created
 */
final class ContestantModel extends Model implements ContestYearResource, ContestYearRole
{
    public const ResourceId = 'contestant'; // phpcs:ignore
    public const RoleId = 'contestYear.contestant'; // phpcs:ignore

    public function getContestYear(): ContestYearModel
    {
        return $this->contest->getContestYear($this->year);
    }

    public function getPersonHistory(): PersonHistoryModel
    {
        return $this->person->getHistory($this->getContestYear());
    }


    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getSubmits(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<SubmitModel> $selection */
        $selection = $this->related(DbNames::TAB_SUBMIT, 'contestant_id');
        return $selection;
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

    /**
     * @phpstan-return Test<self>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new InvalidCategory($container),
            new ConflictRole($container),
        ];
    }

    public function getContest(): ContestModel
    {
        return $this->contest;
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getResourceId(): string
    {
        return self::ResourceId;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary'])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        return 'řešitel semináře, role je automaticky přiřazována při vytvoření řešitele';
    }

    public function label(): string
    {
        return 'Contestant';
    }
}
