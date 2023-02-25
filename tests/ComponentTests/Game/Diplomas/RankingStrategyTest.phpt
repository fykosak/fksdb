<?php

declare(strict_types=1);

namespace FKSDB\Tests\ComponentTests\Game\Diplomas;

// phpcs:disable
$container = require '../../../Bootstrap.php';

// phpcs:enable
use FKSDB\Components\Game\Diplomas\RankingStrategy;
use FKSDB\Components\EntityForms\Fyziklani\NoMemberException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Tests\PresentersTests\FyziklaniModule\FyziklaniTestCase;
use Nette\Utils\DateTime;
use Tester\Assert;

class RankingStrategyTest extends FyziklaniTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createEvent([]);
        $this->rankingStrategy = new RankingStrategy($this->event, $this->container->getByType(TeamService2::class));

        $tasks = [];
        $tasks[] = $this->createTask(['fyziklani_task_id' => 1, 'label' => 'AA']);
        $tasks[] = $this->createTask(['fyziklani_task_id' => 2, 'label' => 'AB']);
        $tasks[] = $this->createTask(['fyziklani_task_id' => 3, 'label' => 'AC']);

        $teamSubmitPoints = [
            [5, 5, 5], // higher sum
            [5, 3],    // higher average
            [5, 2, 1], // more 5 point submits
            [3, 3, 2], // higher sum
            [3, 1], // more 3 point submits
            [2, 2], // lower team coefficient
            [2, 2], // older team
            [2, 2],
        ];

        $this->teams = [];
        foreach ($teamSubmitPoints as $index => $points) {
            $sum = array_sum($points);
            $this->teams[] = $this->createTeam([
                'fyziklani_team_id' => $index + 1,
                'state' => 'participated',
                'points' => $sum,
                'rank_total' => $index + 1,
                'rank_category' => $index + 1
            ]);
        }

        $this->teams[5]->update(['created' => new \DateTime('2016-01-01T10:00:00')]);
        $this->teams[6]->update(['created' => new \DateTime('2016-01-02T10:00:00')]);
        $this->teams[7]->update(['created' => new \DateTime('2016-01-03T10:00:00')]);



        foreach ($this->teams as $teamIndex => $team) {
            foreach ($teamSubmitPoints[$teamIndex] as $pointIndex => $points) {
                $this->createSubmit([
                    'fyziklani_task_id' => $pointIndex + 1,
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                    'points' => $points,
                    'state' => 'checked'
                ]);
            }
        }
    }

    public function getCategories(): array
    {
        return [
            [null],
            [TeamCategory::tryFrom('A')],
        ];
    }

    /**
     * @dataProvider getCategories
     */
    public function testPointsValidation(?TeamCategory $category): void
    {
        $invalidTeams = $this->rankingStrategy->getInvalidTeamsPoints($category);
        Assert::count(0, $invalidTeams);
    }

    /**
     * @dataProvider getCategories
     */
    public function testPointsValidationChanged(?TeamCategory $category): void
    {
        $this->createSubmit([
            'fyziklani_task_id' => 3,
            'fyziklani_team_id' => 2,
            'points' => 5,
            'state' => 'checked'
        ]);

        $this->createSubmit([
            'fyziklani_task_id' => 3,
            'fyziklani_team_id' => 5,
            'points' => 5,
            'state' => 'checked'
        ]);

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsPoints($category);
        Assert::count(2, $invalidTeams);
    }

    /**
     * @dataProvider getCategories
     * @throws FKSDB\Components\EntityForms\Fyziklani\NoMemberException
     */
    public function testRankValidationException(?TeamCategory $category): void
    {
        $invalidTeams = $this->rankingStrategy->getInvalidTeamsRank($category);
    }


    /**
     * @dataProvider getCategories
     */
    public function testRankValidation(?TeamCategory $category): void
    {
        // Create team members for teams so they can be compared by team coefficient.
        // Last two teams have higher coefficient and should be compared by creation date.
        foreach ($this->teams as $index => $team) {
            $teamMember = $this->createTeamMember(['fyziklani_team_id' => $team->fyziklani_team_id]);
            $this->createPersonHistory(
                $teamMember->person,
                ContestYearService::getCurrentAcademicYear(),
                null,
                ($index < count($this->teams) - 2) ? 1 : 4
            );
        }

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsRank($category);
        Assert::count(0, $invalidTeams);

        // reverse team order, so every two teams should have wrong rank
        foreach ($this->teams as $index => $team) {
            $newrank = count($this->teams) - $index;
            $team->update(['rank_total' => $newrank]);
            $team->update(['rank_category' => $newrank]);
        }

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsRank($category);
        Assert::count(count($this->teams) - 1, $invalidTeams);
    }
}
// phpcs:disable
$testCase = new RankingStrategyTest($container);
$testCase->run();
// phpcs:enable
