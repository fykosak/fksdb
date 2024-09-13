<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization;
use FKSDB\Models\Authorization\Roles\Base\GuestRole;
use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
use FKSDB\Models\Authorization\Roles\Contest\ExplicitContestRole;
use FKSDB\Models\Authorization\Roles\Contest\OrganizerRole;
use FKSDB\Models\Authorization\Roles\ContestYear\ContestantRole;
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

        $service->addRole(Authorization\Roles\Events\EventOrganizerRole::RoleId);

        $service->addRole(GuestRole::RoleId);
        $service->addRole(LoggedInRole::RoleId, GuestRole::RoleId);

        $service->addRole(OrganizerRole::RoleId, LoggedInRole::RoleId);
        $service->addRole(ExplicitContestRole::Webmaster, OrganizerRole::RoleId);
        $service->addRole(ExplicitContestRole::TaskManager, OrganizerRole::RoleId);

        self::createSchool($service);

        $service->addRole(
            ExplicitContestRole::InboxManager,
            [OrganizerRole::RoleId, ExplicitContestRole::SchoolManager]
        );
        $service->addRole(
            ExplicitContestRole::EventManager,
            [OrganizerRole::RoleId, ExplicitContestRole::SchoolManager]
        );
        $service->addRole(Authorization\Roles\Events\ExplicitEventRole::ApplicationManager);
        $service->addRole(
            ExplicitContestRole::DataManager,
            [ExplicitContestRole::InboxManager, ExplicitContestRole::TaskManager, ExplicitContestRole::EventManager]
        );
        $service->addRole(
            ExplicitContestRole::Superuser,
            [
                OrganizerRole::RoleId,
                ExplicitContestRole::TaskManager,
                ExplicitContestRole::SchoolManager,
                ExplicitContestRole::InboxManager,
                ExplicitContestRole::DataManager,
                ExplicitContestRole::EventManager,
            ]
        );
        $service->addRole(ExplicitContestRole::Boss, [ExplicitContestRole::Superuser]);

        $service->addRole(ExplicitContestRole::Cartesian);

        $service->addResource(Models\TeacherModel::RESOURCE_ID);
        self::createApi($service);

        // tasks
        $service->addResource(Models\TaskModel::RESOURCE_ID);
        $service->allow(OrganizerRole::RoleId, Models\TaskModel::RESOURCE_ID, 'points');
        $service->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            Models\TaskModel::RESOURCE_ID
        );

        self::createContestant($service);
        self::createPerson($service, $selfAssertion, $ownerAssertion);
        // contest
        $service->addResource(Models\ContestModel::RESOURCE_ID);
        $service->allow(OrganizerRole::RoleId, Models\ContestModel::RESOURCE_ID, ['chart', 'organizerDashboard']);
        $service->allow(ExplicitContestRole::Boss, Models\ContestModel::RESOURCE_ID, 'acl');

        self::createOrganizer($service, $selfAssertion);
        // submits
        $service->addResource(Models\SubmitModel::RESOURCE_ID);
        $service->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            Models\SubmitModel::RESOURCE_ID
        );
        self::createUpload($service, $submitUploaderAssertion);
        //emails
        $service->addResource(Models\EmailMessageModel::RESOURCE_ID);
        $service->allow(
            [ExplicitContestRole::DataManager, ExplicitContestRole::Boss],
            Models\EmailMessageModel::RESOURCE_ID,
            'list'
        );
        // events
        $service->allow(ExplicitContestRole::EventManager, Models\EventModel::RESOURCE_ID);
        $service->allow(OrganizerRole::RoleId, Models\EventModel::RESOURCE_ID, 'list');
        $service->allow(ExplicitContestRole::Boss, Models\EventModel::RESOURCE_ID, 'acl');
        $service->allow(LoggedInRole::RoleId, Models\EventModel::RESOURCE_ID, 'dashboard');
        // event organizers
        $service->addResource(Models\EventOrganizerModel::RESOURCE_ID);
        $service->allow(ExplicitContestRole::EventManager, Models\EventOrganizerModel::RESOURCE_ID);

        self::createApplications($service);
        self::createTeamApplications($service);
        // schedule
        $service->addResource(Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $service->addResource(Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $service->addResource(Models\Schedule\PersonScheduleModel::RESOURCE_ID);
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\ScheduleGroupModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\ScheduleItemModel::RESOURCE_ID,
            'detail'
        ); // TODO
        $service->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\PersonScheduleModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $service->allow(ExplicitContestRole::EventManager, Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $service->allow(ExplicitContestRole::EventManager, Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $service->allow(ExplicitContestRole::EventManager, Models\Schedule\PersonScheduleModel::RESOURCE_ID);

        // spam
        $service->addResource(Models\SchoolLabelModel::RESOURCE_ID);
        $service->addResource(Models\PersonHistoryModel::RESOURCE_ID);
        $service->addResource(Models\PersonMailModel::RESOURCE_ID);

        $service->allow(OrganizerRole::RoleId, Models\SchoolLabelModel::RESOURCE_ID);
        $service->allow(OrganizerRole::RoleId, Models\PersonHistoryModel::RESOURCE_ID);
        $service->allow(OrganizerRole::RoleId, Models\PersonMailModel::RESOURCE_ID);

        self::createPayment($service, $selfAssertion);
        self::createGame($service);
        self::createWarehouse($service);

        $service->allow(ExplicitContestRole::Cartesian);
        return $service;
    }

    private static function createPerson(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion,
        Authorization\Assertions\ContestRelatedAssertion $ownerAssertion
    ): void {
        $permission->addResource(Models\PersonModel::RESOURCE_ID);

        $permission->allow(OrganizerRole::RoleId, Models\PersonModel::RESOURCE_ID, 'search');
        $permission->allow(
            OrganizerRole::RoleId,
            Models\PersonModel::RESOURCE_ID,
            ['edit', 'detail.full'],
            $selfAssertion
        );
        $permission->allow(
            OrganizerRole::RoleId,
            Models\PersonModel::RESOURCE_ID,
            'detail.basic',
            $ownerAssertion
        );
        $permission->allow(
            ExplicitContestRole::InboxManager,
            Models\PersonModel::RESOURCE_ID,
            ['detail.restrict', 'edit'],
            $ownerAssertion
        );

        $permission->allow(
            [ExplicitContestRole::EventManager, ExplicitContestRole::DataManager, ExplicitContestRole::Boss],
            Models\PersonModel::RESOURCE_ID
        );
    }

    private static function createOrganizer(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion
    ): void {
        $permission->addResource(Models\OrganizerModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\OrganizerModel::RESOURCE_ID, 'list');
        $permission->allow(
            OrganizerRole::RoleId,
            Models\OrganizerModel::RESOURCE_ID,
            'edit',
            $selfAssertion
        );
        $permission->allow(
            [ExplicitContestRole::DataManager, ExplicitContestRole::Boss],
            Models\OrganizerModel::RESOURCE_ID
        );
    }

    private static function createSchool(Permission $permission): void
    {
        $permission->addResource(Models\SchoolModel::RESOURCE_ID);
        $permission->addRole(ExplicitContestRole::SchoolManager);
        $permission->allow(OrganizerRole::RoleId, Models\SchoolModel::RESOURCE_ID, ['list', 'detail']);
        $permission->allow(ExplicitContestRole::SchoolManager, Models\SchoolModel::RESOURCE_ID);
    }

    private static function createContestant(Permission $permission): void
    {
        $permission->addResource(Models\ContestantModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\ContestantModel::RESOURCE_ID, 'list');
        $permission->allow(ExplicitContestRole::InboxManager, Models\ContestantModel::RESOURCE_ID, ['list', 'create']);
        $permission->allow(
            ExplicitContestRole::InboxManager,
            Models\ContestantModel::RESOURCE_ID,
            'edit',
            new Authorization\Assertions\ContestContestantAssertion()
        );
    }

    private static function createApplications(Permission $permission): void
    {
        $permission->addRole(Authorization\Roles\Events\ParticipantRole::RoleId);
        $permission->addResource(Models\EventParticipantModel::RESOURCE_ID);

        $permission->allow(
            GuestRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'create',
            new LogicAnd(
                new Authorization\Assertions\IsRegistrationOpened(),
                new Authorization\Assertions\IsOpenEvent()
            )
        );
        $permission->allow(
            Authorization\Roles\Events\ParticipantRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new Authorization\Assertions\NotDisqualified(),
                new Authorization\Assertions\OwnApplicationAssertion()
            )
        );
        $permission->allow(
            Authorization\Roles\Events\ParticipantRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new Authorization\Assertions\NotDisqualified(),
                new Authorization\Assertions\OwnApplicationAssertion(),
                new Authorization\Assertions\IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ExplicitContestRole::EventManager, Authorization\Roles\Events\ExplicitEventRole::ApplicationManager],
            Models\EventParticipantModel::RESOURCE_ID
        );
    }

    private static function createTeamApplications(Permission $permission): void
    {
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId);
        $permission->addResource(Models\Fyziklani\TeamModel2::RESOURCE_ID);

        $permission->allow(
            GuestRole::RoleId,
            Models\Fyziklani\TeamModel2::RESOURCE_ID,
            'create',
            new Authorization\Assertions\IsRegistrationOpened()
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId,
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId,
            ],
            Models\Fyziklani\TeamModel2::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new Authorization\Assertions\NotDisqualified(),
                new Authorization\Assertions\OwnTeamAssertion()
            )
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId,
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId,
            ],
            Models\Fyziklani\TeamModel2::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new Authorization\Assertions\NotDisqualified(),
                new Authorization\Assertions\OwnTeamAssertion(),
                new Authorization\Assertions\IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ExplicitContestRole::EventManager, Authorization\Roles\Events\ExplicitEventRole::ApplicationManager],
            Models\Fyziklani\TeamModel2::RESOURCE_ID
        );
    }

    private static function createUpload(
        Permission $permission,
        Authorization\Assertions\OwnSubmitAssertion $submitUploaderAssertion
    ): void {
        $permission->addRole(ContestantRole::RoleId, LoggedInRole::RoleId);
        $permission->allow(ContestantRole::RoleId, Models\ContestModel::RESOURCE_ID, ['contestantDashboard']);
        // contestatn upload
        $permission->allow(ContestantRole::RoleId, Models\SubmitModel::RESOURCE_ID, ['list', 'upload']);
        $permission->allow(
            ContestantRole::RoleId,
            Models\SubmitModel::RESOURCE_ID,
            ['revoke', 'download.corrected', 'download.uploaded', 'download'],
            $submitUploaderAssertion
        );
    }

    private static function createApi(Permission $permission): void
    {
        $permission->addRole(ExplicitContestRole::Aesop);
        $permission->addRole(ExplicitContestRole::Web);
        $permission->addRole(ExplicitContestRole::Wiki);

        $permission->addResource('export.adhoc');
        $permission->addResource('export');
        $permission->addResource(RestApiPresenter::RESOURCE_ID);
        $permission->addResource(AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->addResource(WebServiceModel::SOAP_RESOURCE_ID);
        $permission->addResource(Models\StoredQuery\QueryModel::RESOURCE_ID);

        $permission->allow(
            ExplicitContestRole::Web,
            'export',
            'execute',
            new Authorization\Assertions\StoredQueryTagAssertion(['web-safe'])
        );
        $permission->allow(
            ExplicitContestRole::Wiki,
            'export',
            'execute',
            new Authorization\Assertions\StoredQueryTagAssertion(['wiki-safe'])
        );
        $permission->allow(
            [ExplicitContestRole::Wiki, ExplicitContestRole::Web, OrganizerRole::RoleId],
            WebServiceModel::SOAP_RESOURCE_ID
        );
        $permission->allow(ExplicitContestRole::Aesop, AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->allow([ExplicitContestRole::InboxManager, ExplicitContestRole::EventManager], 'export', 'execute');
        $permission->allow(ExplicitContestRole::DataManager, Models\StoredQuery\QueryModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::DataManager, ['export', 'export.adhoc']);
        $permission->allow([OrganizerRole::RoleId, ExplicitContestRole::Web], RestApiPresenter::RESOURCE_ID);
    }

    private static function createPayment(
        Permission $permission,
        Authorization\Assertions\SelfAssertion $selfAssertion
    ): void {
        $permission->addResource(Models\PaymentModel::RESOURCE_ID);

        $permission->allow(LoggedInRole::RoleId, Models\PaymentModel::RESOURCE_ID, 'detail', $selfAssertion);
        $permission->allow(
            LoggedInRole::RoleId,
            Models\PaymentModel::RESOURCE_ID,
            'edit',
            new LogicAnd(
                $selfAssertion,
                new Authorization\Assertions\PaymentEditableAssertion()
            )
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId,
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId,
            ],
            Models\PaymentModel::RESOURCE_ID,
            'create'
        );
        $permission->allow(ExplicitContestRole::EventManager, Models\PaymentModel::RESOURCE_ID);
    }

    private static function createGame(Permission $permission): void
    {
        $permission->addRole(Authorization\Roles\Events\ExplicitEventRole::GameInserter);

        $permission->addResource('game');
        $permission->addResource(Models\Fyziklani\TaskModel::RESOURCE_ID);
        $permission->addResource(Models\Fyziklani\SubmitModel::RESOURCE_ID);

        $permission->allow(
            Authorization\Roles\Events\ExplicitEventRole::GameInserter,
            [
                Models\Fyziklani\SubmitModel::RESOURCE_ID,
                Models\Fyziklani\TaskModel::RESOURCE_ID,
            ]
        );
        $permission->allow(
            Authorization\Roles\Events\ExplicitEventRole::GameInserter,
            'game',
            ['diplomas.results', 'close', 'dashboard']
        );
        $permission->allow(
            OrganizerRole::RoleId,
            'game',
            ['gameSetup', 'statistics', 'presentation', 'seating', 'diplomas']
        );
        $permission->allow(
            [
                OrganizerRole::RoleId,
                Authorization\Roles\Events\EventOrganizerRole::RoleId,
                Authorization\Roles\Events\ExplicitEventRole::GameInserter,
            ],
            'game',
            'howTo'
        );
    }

    private static function createWarehouse(Permission $permission): void
    {
        $permission->addResource(Models\Warehouse\ProducerModel::RESOURCE_ID);
        $permission->addResource(Models\Warehouse\ProductModel::RESOURCE_ID);
        $permission->addResource(Models\Warehouse\ItemModel::RESOURCE_ID);

        $permission->allow(
            OrganizerRole::RoleId,
            [
                Models\Warehouse\ProducerModel::RESOURCE_ID,
                Models\Warehouse\ProductModel::RESOURCE_ID,
                Models\Warehouse\ItemModel::RESOURCE_ID,
            ]
        );
    }
}
