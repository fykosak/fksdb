<?php

declare(strict_types=1);

namespace FKSDB\Models\Router;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\Routers\RouteList;

class Router
{
    private const CONTESTS = ['fykos' => ContestModel::ID_FYKOS, 'vyfuk' => ContestModel::ID_VYFUK];
    // constestModules: organizer|public|warehouse
    // rootPresenters: settings
    public static function createRouter(): RouteList
    {
        $service = new RouteList();
        $service->addRoute('index.php', ['module' => 'Public', 'presenter' => 'Dashboard', 'action' => 'default'], 1);

        RestApiPresenter::createRouter($service->withPath('api/'));
        $service->addRoute(
            'web-service/<action>',
            ['module' => 'Organizer', 'presenter' => 'WebService', 'action' => 'default'],
            1
        );
        $service->addRoute(
            'aesop/<contestId fykos|vyfuk><year [0-9]+>/contestant/[<category [0-4]>]',
            [
                'module' => 'Core',
                'presenter' => 'AESOP',
                'action' => 'contestant',
                'contestId' => ['filterTable' => self::CONTESTS],
            ]
        );
        $service->addRoute(
            'aesop/<contestId fykos|vyfuk><year [0-9]+>/event.<eventName>[/<type>]',
            [
                'module' => 'Core',
                'presenter' => 'AESOP',
                'action' => 'event',
                'contestId' => ['filterTable' => self::CONTESTS],
            ]
        );
        $service->addRoute(
            '<contestId fykos|vyfuk><year [0-9]+>[.<series [0-9]+>]/q/<qid>',
            [
                'module' => 'Org',
                'presenter' => 'Export',
                'action' => 'execute',
                'contestId' => ['filterTable' => self::CONTESTS],
            ]
        );
        $service->addRoute(
            'auth/<action login|logout|fb-login|recover|google>',
            ['module' => 'Core', 'presenter' => 'Authentication']
        );
        $service->addRoute('profile/<presenter=Dashboard>/<action=default>', ['module' => 'Profile']);
        $service->addRoute(
            'register/[<contestId fykos|vyfuk>/[year<year [0-9]+>/]]<action=default>',
            [
                'module' => 'Core',
                'presenter' => 'Register',
                'contestId' => ['filterTable' => self::CONTESTS],
                'year' => null,
            ]
        );
        $service->addRoute(
            '[<contestId fykos|vyfuk>/]<presenter register>/<action=default>',
            ['module' => 'Public', 'contestId' => ['filterTable' => self::CONTESTS]],
            1
        );
        $service->addRoute('/', ['module' => 'Core', 'presenter' => 'Dispatch', 'action' => 'default']);
        $service->addRoute('<presenter settings|school>/<action=default>[/<id>]', ['module' => 'Core']);

        self::addEventsModule($service->withPath('events/'));

        $service->addRoute(
            '<module event|game>/[<eventId [0-9]+>/]<presenter>/<action=default>[/<id>]',
            ['presenter' => 'Dashboard']
        );

        $service->addRoute(
            '<module event|game>[<eventId [0-9]+>]/<presenter>/<action=default>[/<id>]',
            ['presenter' => 'Dashboard', 'flag' => [\Nette\Routing\Router::ONE_WAY]]
        );
        $service->addRoute(
            'event[<eventId [0-9]+>]/TeamApplication/<action=default>[/<id>]',
            ['module' => 'Event', 'presenter' => 'Team', 'flag' => [\Nette\Routing\Router::ONE_WAY]]
        );
        // phpcs:disable
        $service->addRoute(
            '<module organizer|public|warehouse>/[<contestId fykos|vyfuk>[<year [0-9]+>]/[series<series [0-9]+>/]]<presenter>/<action=default>[/<id>]',
            ['presenter' => 'Dashboard', 'contestId' => ['filterTable' => self::CONTESTS]]
        );
        // phpcs:enable
        return $service;
    }

    private static function addEventsModule(RouteList $list): void
    {
        $list->addRoute('', [
            'module' => 'Event',
            'presenter' => 'Dispatch',
            'action' => 'default',
        ]);
        $list->addRoute(
            '<eventId [0-9]+>/schedule/<action>',
            [
                'module' => 'Schedule',
                'presenter' => 'Dashboard',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/schedule/feedback/<id [0-9]+>',
            [
                'module' => 'Schedule',
                'presenter' => 'Feedback',
                'action' => 'default',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/schedule/groups[/<id [0-9]+>]/<action>',
            [
                'module' => 'Schedule',
                'presenter' => 'Group',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/schedule/groups/<groupId [0-9]+>/items[/<id [0-9]+>]/<action>',
            [
                'module' => 'Schedule',
                'presenter' => 'Item',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/schedule/persons[/<id [0-9]+>]/<action>',
            [
                'module' => 'Schedule',
                'presenter' => 'Person',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/payments[/<id [0-9]+>]/<action>',
            [
                'module' => 'Event',
                'presenter' => 'Payments',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/teams[/<id [0-9]+>]/<action=default>',
            [
                'module' => 'Event',
                'presenter' => 'Team',
            ]
        );
        $list->addRoute(
            '<eventId [0-9]+>/attendance[/<id [0-9]+>]/<action=default>',
            [
                'module' => 'Event',
                'presenter' => 'Attendance',
            ]
        );
    }
}
