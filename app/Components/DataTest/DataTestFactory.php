<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\DataTest\Tests\Event\NoRoleSchedule;
use FKSDB\Components\DataTest\Tests\Event\Team\CategoryCheck;
use FKSDB\Components\DataTest\Tests\Event\Team\PersonAdapter;
use FKSDB\Components\DataTest\Tests\Event\TeamAdapter;
use FKSDB\Components\DataTest\Tests\Person\EventCoveringTest;
use FKSDB\Components\DataTest\Tests\Person\GenderFromBornNumberTest;
use FKSDB\Components\DataTest\Tests\Person\ParticipantsDurationTest;
use FKSDB\Components\DataTest\Tests\Person\PersonHistoryAdapter;
use FKSDB\Components\DataTest\Tests\Person\PersonInfoAdapter;
use FKSDB\Components\DataTest\Tests\Person\PostgraduateStudyTest;
use FKSDB\Components\DataTest\Tests\Person\SchoolChangeTest;
use FKSDB\Components\DataTest\Tests\Person\StudyYearTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\SetSchoolTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\StudyTypeTest;
use FKSDB\Components\DataTest\Tests\PersonInfo\PersonInfoFileLevelTest;
use FKSDB\Components\DataTest\Tests\School\StudyYearFillTest;
use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class DataTestFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @phpstan-return Test<PersonModel>[]
     */
    public function getPersonTests(): array
    {
        return [
            new GenderFromBornNumberTest($this->container),
            new ParticipantsDurationTest($this->container),
            new EventCoveringTest($this->container),
            new StudyYearTest($this->container),
            new PersonHistoryAdapter(new StudyTypeTest($this->container), $this->container),
            new PersonHistoryAdapter(new SetSchoolTest($this->container), $this->container),
            new PostgraduateStudyTest($this->container),
            new SchoolChangeTest($this->container),
            new PersonInfoAdapter(
                new PersonInfoFileLevelTest('phone', $this->container),
                $this->container
            ),
            new PersonInfoAdapter(
                new PersonInfoFileLevelTest('phone_parent_d', $this->container),
                $this->container
            ),
            new PersonInfoAdapter(
                new PersonInfoFileLevelTest('phone_parent_m', $this->container),
                $this->container
            ),
        ];
    }

    /**
     * @phpstan-return Test<SchoolModel>[]
     */
    public function getSchoolTests(): array
    {
        return [
            'study' => new StudyYearFillTest($this->container),
        ];
    }

    /**
     * @phpstan-return Test<EventModel>[]
     */
    public function getEventTests(): array
    {
        $tests = [
            new NoRoleSchedule($this->container),
        ];
        foreach ($this->getTeamTests() as $test) {
            $tests[] = new TeamAdapter($test, $this->container);
        }
        return $tests;
    }

    /**
     * @phpstan-return Test<TeamModel2>[]
     */
    public function getTeamTests(): array
    {
        return [
            new CategoryCheck($this->container),
            new PersonAdapter(
                new GenderFromBornNumberTest($this->container),
                $this->container
            ),
            new PersonAdapter(new SchoolChangeTest($this->container), $this->container),
            new PersonAdapter(
                new PostgraduateStudyTest($this->container),
                $this->container
            ),
            new PersonAdapter(new StudyYearTest($this->container), $this->container),
            new Tests\Event\Team\PersonHistoryAdapter(
                new StudyTypeTest($this->container),
                $this->container
            ),
            new Tests\Event\Team\PersonHistoryAdapter(
                new SetSchoolTest($this->container),
                $this->container
            ),
        ];
    }

    /**
     * @phpstan-template TModel of Model
     * @phpstan-param Test<TModel>[] $tests
     * @phpstan-param TModel $model
     * @phpstan-return array<string,Message[]>
     */
    public static function runForModel(Model $model, array $tests): array
    {
        $log = [];
        foreach ($tests as $test) {
            $logger = new MemoryLogger();
            $test->run($logger, $model);
            $testLog = $logger->getMessages();
            if (count($testLog)) {
                $log[$test->getId()] = $testLog;
            }
        }
        return $log;
    }
}
