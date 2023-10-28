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
    // eventModules: event|game|schedule
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
        $service->addRoute('<presenter settings>/<action=default>[/<id>]', ['module' => 'Core']);

        // EVENTS
        $service->withPath('events/')->addRoute(
            '<action=default>',
            [
                'module' => 'Events',
                'presenter' => 'Dispatch',
            ]
        )->addRoute(
            '[<eventId [0-9]+>/]<module game|schedule>/<presenter>/<action=default>[/<id>]',
            ['presenter' => 'Dashboard']
        )->addRoute(
            '<eventId [0-9]+>/<presenter>/<action=default>[/<id>]',
            [
                'module' => 'Events',
                'presenter' => 'Dashboard',
            ]
        );

        $service->addRoute(
            'event[<eventId [0-9]+>]/<presenter>/<action=default>[/<id>]',
            [
                'presenter' => 'Dashboard',
                'flag' => [\Nette\Routing\Router::ONE_WAY],
                'module' => 'Events',
            ]
        );
        $service->addRoute(
            'event[<eventId [0-9]+>]/TeamApplication/<action=default>[/<id>]',
            ['module' => 'Events', 'presenter' => 'Team', 'flag' => [\Nette\Routing\Router::ONE_WAY]]
        );
        // phpcs:disable
        $service->addRoute(
            '<module organizer|public|warehouse>/[<contestId fykos|vyfuk>[<year [0-9]+>/[series<series [0-9]+>/]]]<presenter>/<action=default>[/<id>]',
            ['presenter' => 'Dashboard', 'contestId' => ['filterTable' => self::CONTESTS]]
        );
        // phpcs:enable
        return $service;
    }
}
