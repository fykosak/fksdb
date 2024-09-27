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
        $permission = new Permission();
// BaseRole -> implicit
        $permission->addRole(GuestRole::RoleId);
        $permission->addRole(LoggedInRole::RoleId, GuestRole::RoleId);
// ContestRole -> implict
        $permission->addRole(OrganizerRole::RoleId, LoggedInRole::RoleId);
        // ContestRole -> explicit
        $permission->addRole(ExplicitContestRole::Aesop);
        $permission->addRole(ExplicitContestRole::Web);
        $permission->addRole(ExplicitContestRole::Wiki);

        $permission->addRole(ExplicitContestRole::Webmaster, OrganizerRole::RoleId);
        $permission->addRole(ExplicitContestRole::TaskManager, OrganizerRole::RoleId);
        $permission->addRole(ExplicitContestRole::SchoolManager);
        $permission->addRole(
            ExplicitContestRole::InboxManager,
            [OrganizerRole::RoleId, ExplicitContestRole::SchoolManager]
        );
        $permission->addRole(
            ExplicitContestRole::EventManager,
            [OrganizerRole::RoleId, ExplicitContestRole::SchoolManager]
        );
        $permission->addRole(
            ExplicitContestRole::DataManager,
            [ExplicitContestRole::InboxManager, ExplicitContestRole::TaskManager, ExplicitContestRole::EventManager]
        );
        $permission->addRole(
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
        $permission->addRole(ExplicitContestRole::Boss, [ExplicitContestRole::Superuser]);

        $permission->addRole(ExplicitContestRole::Cartesian);
        // ContestYearRole -> implicit
        $permission->addRole(ContestantRole::RoleId, LoggedInRole::RoleId);
        // EventRole -> implicit
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\ParticipantRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\EventOrganizerRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\ScheduleParticipant::RoleId, LoggedInRole::RoleId);
        // EventRole ->explicit
        $permission->addRole(Authorization\Roles\Events\ExplicitEventRole::GameInserter, LoggedInRole::RoleId);
        $permission->addRole(Authorization\Roles\Events\ExplicitEventRole::ApplicationManager, LoggedInRole::RoleId);
// permissions
        $permission->addResource(Models\EventModel::RESOURCE_ID);
        self::createSchool($permission);
        $permission->addResource(Models\TeacherModel::RESOURCE_ID);
        self::createApi($permission);

        // tasks
        $permission->addResource(Models\TaskModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\TaskModel::RESOURCE_ID, 'points');
        $permission->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            Models\TaskModel::RESOURCE_ID
        );

        self::createContestant($permission);
        self::createPerson($permission, $selfAssertion, $ownerAssertion);
        // contest
        $permission->addResource(Models\ContestModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\ContestModel::RESOURCE_ID, ['chart', 'organizerDashboard']);
        $permission->allow(ExplicitContestRole::Boss, Models\ContestModel::RESOURCE_ID, 'acl');

        self::createOrganizer($permission, $selfAssertion);
        // submits
        $permission->addResource(Models\SubmitModel::RESOURCE_ID);
        $permission->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            Models\SubmitModel::RESOURCE_ID
        );
        self::createUpload($permission, $submitUploaderAssertion);
        //emails
        self::createEmails($permission);

        // events
        $permission->allow(ExplicitContestRole::EventManager, Models\EventModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\EventModel::RESOURCE_ID, 'list');
        $permission->allow(ExplicitContestRole::Boss, Models\EventModel::RESOURCE_ID, 'acl');
        $permission->allow(LoggedInRole::RoleId, Models\EventModel::RESOURCE_ID, 'dashboard');
        // event organizers
        $permission->addResource(Models\EventOrganizerModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, Models\EventOrganizerModel::RESOURCE_ID);

        self::createApplications($permission);
        self::createTeamApplications($permission);
        self::createSchedule($permission);

        // spam
        $permission->addResource(Models\SchoolLabelModel::RESOURCE_ID);
        $permission->addResource(Models\PersonHistoryModel::RESOURCE_ID);
        $permission->addResource(Models\PersonMailModel::RESOURCE_ID);

        $permission->allow(OrganizerRole::RoleId, Models\SchoolLabelModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\PersonHistoryModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, Models\PersonMailModel::RESOURCE_ID);

        self::createPayment($permission, $selfAssertion);
        self::createGame($permission);
        self::createWarehouse($permission);

        $permission->allow(ExplicitContestRole::Cartesian);
        return $permission;
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

        $permission->addResource(Models\EventParticipantModel::RESOURCE_ID);

        $permission->allow(
            GuestRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'create',
            new LogicAnd(
                new Assertions\Events\IsRegistrationOpened(),
                new Assertions\Events\IsOpenTypeEvent()
            )
        );
        $permission->allow(
            Authorization\Roles\Events\ParticipantRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new Assertions\Events\NotDisqualified(),
                new Assertions\Events\OwnParticipant()
            )
        );
        $permission->allow(
            Authorization\Roles\Events\ParticipantRole::RoleId,
            Models\EventParticipantModel::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new Assertions\Events\NotDisqualified(),
                new Assertions\Events\OwnParticipant(),
                new Assertions\Events\IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ExplicitContestRole::EventManager, Authorization\Roles\Events\ExplicitEventRole::ApplicationManager],
            Models\EventParticipantModel::RESOURCE_ID
        );
    }

    private static function createTeamApplications(Permission $permission): void
    {

        $permission->addResource(Models\Fyziklani\TeamModel2::RESOURCE_ID);

        $permission->allow(
            GuestRole::RoleId,
            Models\Fyziklani\TeamModel2::RESOURCE_ID,
            'create',
            new Assertions\Events\IsRegistrationOpened()
        );
        $permission->allow(
            [
                Authorization\Roles\Events\Fyziklani\TeamTeacherRole::RoleId,
                Authorization\Roles\Events\Fyziklani\TeamMemberRole::RoleId,
            ],
            Models\Fyziklani\TeamModel2::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new Assertions\Events\NotDisqualified(),
                new Assertions\Events\OwnTeam()
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
                new Assertions\Events\NotDisqualified(),
                new Assertions\Events\OwnTeam(),
                new Assertions\Events\IsRegistrationOpened()
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

    private static function createSchedule(Permission $permission): void
    {
        // schedule
        $permission->addResource(Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $permission->addResource(Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $permission->addResource(Models\Schedule\PersonScheduleModel::RESOURCE_ID);
        $permission->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\ScheduleGroupModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $permission->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\ScheduleItemModel::RESOURCE_ID,
            'detail'
        ); // TODO
        $permission->allow(
            Authorization\Roles\Events\EventOrganizerRole::RoleId,
            Models\Schedule\PersonScheduleModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $permission->allow(ExplicitContestRole::EventManager, Models\Schedule\ScheduleGroupModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, Models\Schedule\ScheduleItemModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, Models\Schedule\PersonScheduleModel::RESOURCE_ID);
    }

    private static function createEmails(Permission $permission): void
    {
        $permission->addResource(Models\EmailMessageModel::RESOURCE_ID); // dashboard, detail, list, template, howTo
        $permission->allow(
            [ExplicitContestRole::DataManager, ExplicitContestRole::EventManager, ExplicitContestRole::Boss],
            Models\EmailMessageModel::RESOURCE_ID,
            ['dashboard', 'howTo', 'list', 'template']
        );
    }
}
