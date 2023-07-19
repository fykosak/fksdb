<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\SubmitSource;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\SubmitQuestionAnswerService;
use FKSDB\Models\ORM\Services\SubmitQuestionService;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\ORM\Services\TaskService;

// phpcs:disable
$container = require '../../Bootstrap.php';

// phpcs:enable
class PublicModule extends AbstractPageDisplayTestCase
{
    private TaskModel $task;
    private SubmitModel $submit;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var ContestantModel $contestant */
        $contestant = $this->container->getByType(ContestantService::class)->storeModel(
            ['person_id' => $this->person->person_id, 'contest_id' => 1, 'year' => 1, 'contest_category_id' => 1]
        );
        $this->task = $this->container->getByType(TaskService::class)->storeModel(
            ['label' => 1,'contest_id' => 1, 'year' => 1, 'series' => 1]
        );
        $this->submit = $this->container->getByType(SubmitService::class)->storeModel([
            'contestant_id' => $contestant->contestant_id,
            'task_id' => $this->task->task_id,
            'source' => SubmitSource::QUIZ,
            'submitted_on' => new \DateTime(),
        ]);
        /** @var SubmitQuestionModel $question */
        $question = $this->container->getByType(SubmitQuestionService::class)->storeModel(
            ['task_id' => $this->task->task_id]
        );
        $this->container->getByType(SubmitQuestionAnswerService::class)->storeModel([
            'contestant_id' => $contestant->contestant_id,
            'submit_question_id' => $question->submit_question_id
        ]);

        // not quiz task
        $this->container->getByType(TaskService::class)->storeModel(
            ['label' => 2,'contest_id' => 1, 'year' => 1, 'series' => 1]
        );
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        [$presenterName, $action, $params] = parent::transformParams($presenterName, $action, $params);
        $params['year'] = '1';
        $params['contestId'] = '1';
        if ($action === 'quizDetail') {
            $params['id'] = $this->submit->submit_id;
        } elseif ($action === 'quiz' || $presenterName === 'Public:QuizRegister') {
            $params['id'] = $this->task->task_id;
        }
        return [$presenterName, $action, $params];
    }

    public function getPages(): array
    {
        return [
            ['Public:Dashboard', 'default'],
            ['Public:Submit', 'default'],
            ['Public:Submit', 'legacy'],
            ['Public:Submit', 'quiz'],
            ['Public:Submit', 'quizDetail'],
            ['Public:QuizRegister', 'default'],
        ];
    }
}

// phpcs:disable
$testCase = new PublicModule($container);
$testCase->run();
// phpcs:enable
