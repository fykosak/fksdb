<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Tests\Contestant\ContestantToPersonAdapter;
use FKSDB\Models\ORM\Tests\Contestant\ContestantToPersonHistoryAdapter;
use FKSDB\Models\ORM\Tests\ContestYear\ContestYearToContestantsAdapter;
use FKSDB\Models\ORM\Tests\Event\ConflictRole;
use FKSDB\Models\ORM\Tests\Event\EventToPersonsAdapter;
use FKSDB\Models\ORM\Tests\Event\Schedule\ItemAdapter;
use FKSDB\Models\ORM\Tests\Event\Schedule\RunOutCapacity;
use FKSDB\Models\ORM\Tests\Event\ScheduleGroupAdapter;
use FKSDB\Models\ORM\Tests\Event\Team\TeamToPersonAdapter;
use FKSDB\Models\ORM\Tests\Event\Team\TeamToPersonHistoryAdapter;
use FKSDB\Models\ORM\Tests\Event\TeamAdapter;
use FKSDB\Models\ORM\Tests\Person\PersonHistoryAdapter;
use FKSDB\Models\ORM\Tests\Person\PersonInfoAdapter;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
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
            ...PersonModel::getTests($this->container),
            ...self::applyAdaptor(
                PersonHistoryAdapter::class,
                PersonHistoryModel::getTests($this->container),
                $this->container
            ),
            ...self::applyAdaptor(
                PersonInfoAdapter::class,
                PersonInfoModel::getTests($this->container),
                $this->container
            ),
        ];
    }

    /**
     * @phpstan-return Test<EventModel>[]
     */
    public function getEventTests(): array
    {
        return [
            ...EventModel::getTests($this->container),
            new EventToPersonsAdapter(new ConflictRole($this->container), $this->container),
            new ScheduleGroupAdapter(
                new ItemAdapter(
                    new RunOutCapacity($this->container),
                    $this->container
                ),
                $this->container
            ),
            ...self::applyAdaptor(TeamAdapter::class, $this->getTeamTests(), $this->container),
        ];
    }

    /**
     * @phpstan-return Test<TeamModel2>[]
     */
    public function getTeamTests(): array
    {
        return [
            ...TeamModel2::getTests($this->container),
            ...self::applyAdaptor(
                TeamToPersonAdapter::class,
                PersonModel::getTests($this->container),
                $this->container
            ),
            ...self::applyAdaptor(
                TeamToPersonHistoryAdapter::class,
                PersonHistoryModel::getTests($this->container),
                $this->container
            ),
        ];
    }

    /**
     * @phpstan-return Test<ContestYearModel>[]
     */
    public function getContestYearTests(): array
    {
        return [
            ...ContestYearModel::getTests($this->container),
            ...self::applyAdaptor(
                ContestYearToContestantsAdapter::class,
                [
                    ...ContestantModel::getTests($this->container),
                    ...self::applyAdaptor(
                        ContestantToPersonHistoryAdapter::class,
                        PersonHistoryModel::getTests($this->container),
                        $this->container
                    ),
                    ...self::applyAdaptor(
                        ContestantToPersonAdapter::class,
                        PersonModel::getTests($this->container),
                        $this->container
                    ),
                ],
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

    /**
     * @phpstan-template TOriginalModel of Model
     * @phpstan-template TTestedModel of Model
     * @phpstan-param Test<TTestedModel>[] $tests
     * @phpstan-param class-string<Adapter<TOriginalModel,TTestedModel>> $adapterClass
     * @phpstan-return Test<TOriginalModel>[]
     */
    public static function applyAdaptor(string $adapterClass, array $tests, Container $container): array
    {
        return array_map(
            fn(Test $test) => new $adapterClass($test, $container),
            $tests
        );
    }
}
