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
use Nette\DI\Container;

class DataTestFactory
{
    /**
     * @phpstan-return Test<PersonModel>[]
     */
    public static function getPersonTests(Container $container): array
    {
        return [
            ...PersonModel::getTests($container),
            ...self::applyAdaptor(
                PersonHistoryAdapter::class,
                PersonHistoryModel::getTests($container),
                $container
            ),
            ...self::applyAdaptor(
                PersonInfoAdapter::class,
                PersonInfoModel::getTests($container),
                $container
            ),
        ];
    }

    /**
     * @phpstan-return Test<EventModel>[]
     */
    public static function getEventTests(Container $container): array
    {
        return [
            ...EventModel::getTests($container),
            ...self::applyAdaptor(EventToPersonsAdapter::class, [new ConflictRole($container)], $container),
            ...self::applyAdaptor(
                ScheduleGroupAdapter::class,
                self::applyAdaptor(ItemAdapter::class, [new RunOutCapacity($container)], $container),
                $container
            ),
            ...self::applyAdaptor(TeamAdapter::class, self::getTeamTests($container), $container),
        ];
    }

    /**
     * @phpstan-return Test<TeamModel2>[]
     */
    public static function getTeamTests(Container $container): array
    {
        return [
            ...TeamModel2::getTests($container),
            ...self::applyAdaptor(
                TeamToPersonAdapter::class,
                PersonModel::getTests($container),
                $container
            ),
            ...self::applyAdaptor(
                TeamToPersonHistoryAdapter::class,
                PersonHistoryModel::getTests($container),
                $container
            ),
        ];
    }

    /**
     * @phpstan-return Test<ContestYearModel>[]
     */
    public static function getContestYearTests(Container $container): array
    {
        return [
            ...ContestYearModel::getTests($container),
            ...self::applyAdaptor(
                ContestYearToContestantsAdapter::class,
                self::getContestantTests($container),
                $container
            ),
        ];
    }

    /**
     * @phpstan-return Test<ContestantModel>[]
     */
    public static function getContestantTests(Container $container): array
    {
        return [
            ...ContestantModel::getTests($container),
            ...self::applyAdaptor(
                ContestantToPersonHistoryAdapter::class,
                PersonHistoryModel::getTests($container),
                $container
            ),
            ...self::applyAdaptor(
                ContestantToPersonAdapter::class,
                PersonModel::getTests($container),
                $container
            ),
        ];
    }

    /**
     * @phpstan-template TModel of Model
     * @phpstan-param Test<TModel>[] $tests
     * @phpstan-param TModel $model
     * @phpstan-return array<string,TestMessage[]>
     */
    public static function runForModel(Model $model, array $tests): array
    {
        $log = [];
        foreach ($tests as $test) {
            $logger = new TestLogger();
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
