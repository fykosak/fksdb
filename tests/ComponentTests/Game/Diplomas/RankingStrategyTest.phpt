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

        // create tasks
        $tasks = [];
        $tasks[] = $this->createTask(['fyziklani_task_id' => 1, 'label' => 'AA']);
        $tasks[] = $this->createTask(['fyziklani_task_id' => 2, 'label' => 'AB']);
        $tasks[] = $this->createTask(['fyziklani_task_id' => 3, 'label' => 'AC']);
        $tasks[] = $this->createTask(['fyziklani_task_id' => 4, 'label' => 'AD']);

        $this->teamsData = [
            // (key) team_id, category, team member coefficient, expected total rank, expected category rank, points array
            8 => ['A', 4, 1, 1, [5, 5, 5]], // higher sum
            2 => ['B', 4, 2, 1, [5, 3]],    // higher average
            1 => ['A', 4, 3, 2, [5, 2, 1]], // more 5 point submits
            4 => ['B', 4, 4, 2, [3, 3, 2]], // higher sum
            3 => ['A', 4, 5, 3, [3, 1]], // more 3 point submits
            7 => ['B', 1, 6, 3, [2, 2]], // lower team coefficient
            5 => ['A', 4, 7, 4, [2, 2]], // lower team id
            6 => ['B', 4, 8, 4, [2, 2]]
        ];

        // create teams
        $this->teams = [];
        foreach ($this->teamsData as $index => $data) {
            $sum = array_sum($data[4]);
            $this->teams[] = $this->createTeam([
                'fyziklani_team_id' => $index,
                'state' => 'participated',
                'points' => $sum,
                'category' => $data[0]
            ]);
        }

        // create submits for teams
        foreach ($this->teams as $teamIndex => $team) {
            foreach ($this->teamsData[$team->fyziklani_team_id][4] as $pointIndex => $points) {
                $this->createSubmit([
                    'fyziklani_task_id' => $tasks[$pointIndex]->fyziklani_task_id,
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
            [TeamCategory::tryFrom('B')]
        ];
    }

    private function addTeamMembers(): void
    {
        // Create team members for teams so they can be compared by team coefficient.
        // Last two teams have higher coefficient and should be compared by creation date.
        foreach ($this->teams as $index => $team) {
            $teamMember = $this->createTeamMember(['fyziklani_team_id' => $team->fyziklani_team_id]);
            $this->createPersonHistory(
                $teamMember->person,
                ContestYearService::getCurrentAcademicYear(),
                null,
                $this->teamsData[$team->fyziklani_team_id][1]
            );
        }

    }

    private function getExpectedRank(TeamModel2 $team, ?TeamCategory $category): int
    {
        $data = $this->teamsData[$team->fyziklani_team_id];
        return is_null($category) ? $data[2] : $data[3];
    }

    /**
     * Test correct team ranking
     * @dataProvider getCategories
     */
    public function testClosing(?TeamCategory $category): void
    {
        $this->addTeamMembers();
        $this->rankingStrategy->close($category);

        foreach ($this->event->getParticipatingTeams() as $team) {
            if (!is_null($category) && $team->category->value !== $category->value) {
                continue;
            }
            $expectedRank = $this->getExpectedRank($team, $category);
            $actualRank   = is_null($category) ? $team->rank_total : $team->rank_category;
            Assert::same($expectedRank, $actualRank, "Failed for teamId " . $team->fyziklani_team_id);
        }
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
        $changedTeamsCounter = 0;
        foreach ($this->teams as $team) {
            if ($team->category === $category || is_null($category)) {
                $this->createSubmit([
                    'fyziklani_task_id' => 4,
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                    'points' => 5,
                    'state' => 'checked'
                ]);
                $changedTeamsCounter++;
            }
        }

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsPoints($category);
        Assert::count($changedTeamsCounter, $invalidTeams);
    }

    /**
     * @throws FKSDB\Components\EntityForms\Fyziklani\NoMemberException
     */
    public function testException(): void
    {
        $this->rankingStrategy->close(null);
    }


    /**
     * Test rank validation function
     * @dataProvider getCategories
     */
    public function testRankValidation(?TeamCategory $category): void
    {
        $this->addTeamMembers();
        $this->rankingStrategy->close($category);

        // separate teams in category
        $teamsInCategory = [];
        $teamCountInCategory = 0;

        foreach ($this->event->getParticipatingTeams() as $team) {
            if (is_null($category) || $team->category->value === $category->value) {
                $teamsInCategory[] = $team;
                $teamCountInCategory++;
            }
        }

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsRank($category);
        Assert::count(0, $invalidTeams, "Teams should be in a valid order");

        // reverse team order, so every two teams should be in a wrong order
        foreach ($teamsInCategory as $index => $team) {
            $currentRank = is_null($category) ? $team->rank_total : $team->rank_category;
            $newrank = $teamCountInCategory - $currentRank;
            if (is_null($category)) {
                $team->update(['rank_total' => $newrank]);
            } else {
                $team->update(['rank_category' => $newrank]);
            }
        }

        $invalidTeams = $this->rankingStrategy->getInvalidTeamsRank($category);

        Assert::count($teamCountInCategory - 1, $invalidTeams, "All teams should have invalid order");
    }
}
// phpcs:disable
$testCase = new RankingStrategyTest($container);
$testCase->run();
// phpcs:enable
