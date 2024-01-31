<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization;
use FKSDB\Models\Authorization\Roles\BaseRole;
use FKSDB\Models\Authorization\Roles\ContestRole;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\ORM\Models;
use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\CoreModule\AESOPPresenter;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Security\Permission;

final class ACL
{
    public static function create(
        Authorization\Assertions\ContestRelatedAssertion $ownerAssertion,
        Authorization\Assertions\SelfAssertion $selfAssertion,
        Authorization\Assertions\OwnSubmitAssertion $submitUploaderAssertion
    ): Permission {
        $service = new Permission();

        $service->addResource(Models\EventModel::RESOURCE_ID);

        $service->addRole(Authorization\Roles\Events\EventOrganizerRole::ROLE_ID);
        $service->addRole(Authorization\Roles\Events\ContestOrganizerRole::ROLE_ID);

        $service->addRole(BaseRole::Guest);
        $service->addRole(BaseRole::Registered, BaseRole::Guest);

        $service->addRole(ContestRole::Organizer, BaseRole::Registered);
        $service->addRole(ContestRole::Webmaster, ContestRole::Organizer);
        $service->addRole(ContestRole::TaskManager, ContestRole::Organizer);

        self::createSchool($service);

        $service->addRole(ContestRole::InboxManager, [ContestRole::Organizer, ContestRole::SchoolManager]);
        $service->addRole(ContestRole::EventManager, [ContestRole::Organizer, ContestRole::SchoolManager]);
        $service->addRole(
            ContestRole::DataManager,
            [ContestRole::InboxManager, ContestRole::TaskManager, ContestRole::EventManager]
        );
        $service->addRole(
            ContestRole::Superuser,
            [
                ContestRole::Organizer,
                ContestRole::TaskManager,
                ContestRole::SchoolManager,
                ContestRole::InboxManager,
                ContestRole::DataManager,
                ContestRole::EventManager,
            ]
        );
        $service->addRole(ContestRole::Boss, [ContestRole::Superuser]);

        $service->addRole(ContestRole::Cartesian);

        $service->addResource(Models\TeacherModel::RESOURCE_ID);
        self::createApi($service);

// tasks
        $service->addResource(Models\TaskModel::RESOURCE_ID);
        $service->allow(ContestRole::Organizer, Models\TaskModel::RESOURCE_ID, 'points');
        $service->allow([ContestRole::TaskManager, ContestRole::InboxManager], Models\TaskModel::RESOURCE_ID);

        self::createContestant($service);
        self::createPerson($service, $selfAssertion, $ownerAssertion);
// contest
        $service->addResource(Models\ContestModel::RESOURCE_ID);
        $service->allow(ContestRole::Organizer, Models\ContestModel::RESOURCE_ID, ['chart', 'organizerDashboard']);
        $service->allow(ContestRole::Boss, Models\ContestModel::RESOURCE_ID, 'acl');

        self::createOrganizer($service, $selfAssertion);
// submits
        $service->addResource(Models\SubmitModel::RESOURCE_ID);
        $service->allow([ContestRole::TaskManager, ContestRole::InboxManager], Models\SubmitModel::RESOURCE_ID);
        self::createUpload($service, $submitUploaderAssertion);
//emails
        $service->addResource(Models\EmailMessageModel::RESOURCE_ID);
        $service->allow([ContestRole::DataManager, ContestRole::Boss], Models\EmailMessageModel::RESOURCE_ID, 'list');
// events
        $service->allow(ContestRole::EventManager, Models\EventModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, Models\EventModel::RESOURCE_ID, 'chart');
        $service->allow(ContestRole::Organizer, Models\EventModel::RESOURCE_ID, 'list');
        $service->allow(ContestRole::Boss, Models\EventModel::RESOURCE_ID, 'acl');
        $service->allow(BaseRole::Registered, Models\EventModel::RESOURCE_ID, 'dashboard');
// event organizers
        $service->addResource(Models\EventOrganizerModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, Models\EventOrganizerModel::RESOURCE_ID);

        self::createApplications($service);
// schedule
        $service->addResource(Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $service->addResource(Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $service->addResource(Models\Schedule\PersonScheduleModel::RESOURCE_ID);
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::ROLE_ID,
            Models\Schedule\ScheduleGroupModel::RESOURCE_ID,
            ['list', 'detail']
        );// TODO
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::ROLE_ID,
            Models\Schedule\ScheduleItemModel::RESOURCE_ID,
            'detail'
        );// TODO
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::ROLE_ID,
            Models\Schedule\PersonScheduleModel::RESOURCE_ID,
            ['list', 'detail']
        );// TODO
        $service->allow(ContestRole::EventManager, Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, Models\Schedule\PersonScheduleModel::RESOURCE_ID);

        self::createPayment($service, $selfAssertion);
        self::createGame($service);
        self::createWarehouse($service);

        $service->allow(ContestRole::Cartesian);
        return $service;
    }

    private static function createPerson(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion,
        Authorization\Assertions\ContestRelatedAssertion $ownerAssertion
    ): void {
        $permission->addResource(Models\PersonModel::RESOURCE_ID);

        $permission->allow(ContestRole::Organizer, Models\PersonModel::RESOURCE_ID, 'search');
        $permission->allow(
            ContestRole::Organizer,
            Models\PersonModel::RESOURCE_ID,
            ['edit', 'detail.full'],
            $selfAssertion
        );
        $permission->allow(
            ContestRole::Organizer,
            Models\PersonModel::RESOURCE_ID,
            'detail.basic',
            $ownerAssertion
        );
        $permission->allow(
            ContestRole::InboxManager,
            Models\PersonModel::RESOURCE_ID,
            ['detail.restrict', 'edit'],
            $ownerAssertion
        );

        $permission->allow(
            [ContestRole::EventManager, ContestRole::DataManager, ContestRole::Boss],
            Models\PersonModel::RESOURCE_ID
        );
    }

    private static function createOrganizer(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion
    ): void {
        $permission->addResource(Models\OrganizerModel::RESOURCE_ID);
        $permission->allow(ContestRole::Organizer, Models\OrganizerModel::RESOURCE_ID, 'list');
        $permission->allow(
            ContestRole::Organizer,
            Models\OrganizerModel::RESOURCE_ID,
            'edit',
            $selfAssertion
        );
        $permission->allow([ContestRole::DataManager, ContestRole::Boss], Models\OrganizerModel::RESOURCE_ID);
    }

    private static function createSchool(Permission $permission): void
    {
        $permission->addResource(Models\SchoolModel::RESOURCE_ID);
        $permission->addRole(ContestRole::SchoolManager);
        $permission->allow(ContestRole::Organizer, Models\SchoolModel::RESOURCE_ID, ['list', 'detail']);
        $permission->allow(ContestRole::SchoolManager, Models\SchoolModel::RESOURCE_ID);
    }

    private static function createContestant(Permission $permission): void
    {
        $permission->addResource(Models\ContestantModel::RESOURCE_ID);
        $permission->allow(ContestRole::Organizer, Models\ContestantModel::RESOURCE_ID, 'list');
        $permission->allow(ContestRole::InboxManager, Models\ContestantModel::RESOURCE_ID, ['list', 'create']);
        $permission->allow(
            ContestRole::InboxManager,
            Models\ContestantModel::RESOURCE_ID,
            'edit',
            new Authorization\Assertions\ContestContestantAssertion()
        );
    }

    private static function createApplications(Permission $permission): void
    {
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamTeacherRole::ROLE_ID);
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamMemberRole::ROLE_ID);
        $permission->addRole(Authorization\Roles\Events\ParticipantRole::ROLE_ID);

        $permission->addResource(Models\EventParticipantModel::RESOURCE_ID);
        $permission->addResource(Models\Fyziklani\TeamModel2::RESOURCE_ID);

        $permission->allow(
            BaseRole::Guest,
            [Models\Fyziklani\TeamModel2::RESOURCE_ID, Models\EventParticipantModel::RESOURCE_ID],
            'create'
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::ROLE_ID,
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::ROLE_ID,
                Authorization\Roles\Events\ParticipantRole::ROLE_ID,
            ],
            [Models\Fyziklani\TeamModel2::RESOURCE_ID, Models\EventParticipantModel::RESOURCE_ID],
            ['detail', 'edit'],
            new Authorization\Assertions\OwnApplicationAssertion()
        );
        $permission->allow(
            ContestRole::EventManager,
            [Models\Fyziklani\TeamModel2::RESOURCE_ID, Models\EventParticipantModel::RESOURCE_ID]
        );
    }

    private static function createUpload(
        Permission $permission,
        Authorization\Assertions\OwnSubmitAssertion $submitUploaderAssertion
    ): void {
        $permission->addRole(ContestRole::Contestant, BaseRole::Registered);
        $permission->allow(ContestRole::Contestant, Models\ContestModel::RESOURCE_ID, ['contestantDashboard']);
        // contestatn upload
        $permission->allow(ContestRole::Contestant, Models\SubmitModel::RESOURCE_ID, ['list', 'upload']);
        $permission->allow(
            ContestRole::Contestant,
            Models\SubmitModel::RESOURCE_ID,
            ['revoke', 'download.corrected', 'download.uploaded', 'download'],
            $submitUploaderAssertion
        );
    }

    private static function createApi(Permission $permission): void
    {
        $permission->addRole(ContestRole::Aesop);
        $permission->addRole(ContestRole::Web);
        $permission->addRole(ContestRole::Wiki);

        $permission->addResource('export.adhoc');
        $permission->addResource('export');
        $permission->addResource(RestApiPresenter::RESOURCE_ID);
        $permission->addResource(AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->addResource(WebServiceModel::SOAP_RESOURCE_ID);
        $permission->addResource(Models\StoredQuery\QueryModel::RESOURCE_ID);

        $permission->allow(
            ContestRole::Web,
            'export',
            'execute',
            new Authorization\Assertions\StoredQueryTagAssertion(['web-safe'])
        );
        $permission->allow(
            ContestRole::Wiki,
            'export',
            'execute',
            new Authorization\Assertions\StoredQueryTagAssertion(['wiki-safe'])
        );
        $permission->allow(
            [ContestRole::Wiki, ContestRole::Web, ContestRole::Organizer],
            WebServiceModel::SOAP_RESOURCE_ID
        );
        $permission->allow(ContestRole::Aesop, AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->allow([ContestRole::InboxManager,ContestRole::EventManager], 'export', 'execute');
        $permission->allow(ContestRole::DataManager, Models\StoredQuery\QueryModel::RESOURCE_ID);
        $permission->allow(ContestRole::DataManager, ['export','export.adhoc']);
        $permission->allow([ContestRole::Organizer, ContestRole::Web], RestApiPresenter::RESOURCE_ID);
    }

    private static function createPayment(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion
    ): void {
        $permission->addResource(Models\PaymentModel::RESOURCE_ID);

        $permission->allow(BaseRole::Registered, Models\PaymentModel::RESOURCE_ID, 'detail', $selfAssertion);
        $permission->allow(
            BaseRole::Registered,
            Models\PaymentModel::RESOURCE_ID,
            'edit',
            new LogicAnd($selfAssertion, new Authorization\Assertions\PaymentEditableAssertion())// @phpstan-ignore-line
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::ROLE_ID,
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::ROLE_ID,
            ],
            Models\PaymentModel::RESOURCE_ID,
            'create'
        );
        $permission->allow(ContestRole::EventManager, Models\PaymentModel::RESOURCE_ID);
    }

    private static function createGame(Permission $permission): void
    {
        $permission->addRole(Authorization\Roles\Events\EventRole::GameInserter);

        $permission->addResource('game');
        $permission->addResource(Models\Fyziklani\TaskModel::RESOURCE_ID);
        $permission->addResource(Models\Fyziklani\SubmitModel::RESOURCE_ID);

        $permission->allow(
            Authorization\Roles\Events\EventRole::GameInserter,
            [
                Models\Fyziklani\SubmitModel::RESOURCE_ID,
                Models\Fyziklani\TaskModel::RESOURCE_ID,
            ]
        );
        $permission->allow(
            Authorization\Roles\Events\EventRole::GameInserter,
            'game',
            ['diplomas.results', 'close', 'dashboard']
        );
        $permission->allow(
            ContestRole::Organizer,
            'game',
            ['gameSetup', 'statistics', 'presentation', 'seating', 'diplomas']
        );
    }

    private static function createWarehouse(Permission $permission): void
    {
        $permission->addResource(Models\Warehouse\ProducerModel::RESOURCE_ID);
        $permission->addResource(Models\Warehouse\ProductModel::RESOURCE_ID);
        $permission->addResource(Models\Warehouse\ItemModel::RESOURCE_ID);

        $permission->allow(
            ContestRole::Organizer,
            [
                Models\Warehouse\ProducerModel::RESOURCE_ID,
                Models\Warehouse\ProductModel::RESOURCE_ID,
                Models\Warehouse\ItemModel::RESOURCE_ID,
            ]
        );
    }
}
