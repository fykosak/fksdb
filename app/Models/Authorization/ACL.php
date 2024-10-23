<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization\Assertions\ContestContestantAssertion;
use FKSDB\Models\Authorization\Assertions\ContestRelatedAssertion;
use FKSDB\Models\Authorization\Assertions\Events\IsOpenTypeEvent;
use FKSDB\Models\Authorization\Assertions\Events\IsRegistrationOpened;
use FKSDB\Models\Authorization\Assertions\Events\NotDisqualified;
use FKSDB\Models\Authorization\Assertions\Events\OwnApplication;
use FKSDB\Models\Authorization\Assertions\Events\OwnTeamApplication;
use FKSDB\Models\Authorization\Assertions\IsSelfPersonAssertion;
use FKSDB\Models\Authorization\Assertions\OwnSubmitAssertion;
use FKSDB\Models\Authorization\Assertions\Payments\OwnPaymentAssertion;
use FKSDB\Models\Authorization\Assertions\Payments\PaymentEditableAssertion;
use FKSDB\Models\Authorization\Assertions\IsSelfOrganizerAssertion;
use FKSDB\Models\Authorization\Assertions\StoredQueryTagAssertion;
use FKSDB\Models\Authorization\Roles\Base\ExplicitBaseRole;
use FKSDB\Models\Authorization\Roles\Base\GuestRole;
use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
use FKSDB\Models\Authorization\Roles\Contest\ExplicitContestRole;
use FKSDB\Models\Authorization\Roles\Contest\OrganizerRole;
use FKSDB\Models\Authorization\Roles\ContestYear\ContestantRole;
use FKSDB\Models\Authorization\Roles\Events\EventOrganizerRole;
use FKSDB\Models\Authorization\Roles\Events\ExplicitEventRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\Roles\Events\Fyziklani\TeamTeacherRole;
use FKSDB\Models\Authorization\Roles\Events\ParticipantRole;
use FKSDB\Models\Authorization\Roles\Events\ScheduleParticipant;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonMailModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\CoreModule\AESOPPresenter;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Security\Permission;

final class ACL
{
    public static function create(): Permission
    {
        $permission = new Permission();
        // BaseRole -> implicit
        $permission->addRole(GuestRole::RoleId);
        $permission->addRole(LoggedInRole::RoleId, GuestRole::RoleId);
        // BaseRole -> explicit
        $permission->addRole(ExplicitBaseRole::Cartesian);
        $permission->addRole(ExplicitBaseRole::SchoolManager);
        // ContestRole -> implict
        $permission->addRole(OrganizerRole::RoleId, LoggedInRole::RoleId);
        // ContestRole -> explicit
        $permission->addRole(ExplicitContestRole::Aesop);
        $permission->addRole(ExplicitContestRole::Web);
        $permission->addRole(ExplicitContestRole::Wiki);
        $permission->addRole(ExplicitContestRole::Webmaster, OrganizerRole::RoleId);
        $permission->addRole(ExplicitContestRole::TaskManager, OrganizerRole::RoleId);
        $permission->addRole(
            ExplicitContestRole::InboxManager,
            [OrganizerRole::RoleId, ExplicitBaseRole::SchoolManager]
        );
        $permission->addRole(
            ExplicitContestRole::EventManager,
            [OrganizerRole::RoleId, ExplicitBaseRole::SchoolManager]
        );
        $permission->addRole(
            ExplicitContestRole::DataManager,
            [ExplicitContestRole::InboxManager, ExplicitContestRole::TaskManager, ExplicitContestRole::EventManager]
        );
        $permission->addRole(ExplicitContestRole::Treasurer);
        $permission->addRole(
            ExplicitContestRole::Boss,
            [
                OrganizerRole::RoleId,
                ExplicitContestRole::Treasurer,
                ExplicitContestRole::TaskManager,
                ExplicitBaseRole::SchoolManager,
                ExplicitContestRole::InboxManager,
                ExplicitContestRole::DataManager,
                ExplicitContestRole::EventManager,
            ]
        );

        // ContestYearRole -> implicit
        $permission->addRole(ContestantRole::RoleId, LoggedInRole::RoleId);
        // EventRole -> implicit
        $permission->addRole(TeamTeacherRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(TeamMemberRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(ParticipantRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(EventOrganizerRole::RoleId, LoggedInRole::RoleId);
        $permission->addRole(ScheduleParticipant::RoleId, LoggedInRole::RoleId);
        // EventRole ->explicit
        $permission->addRole(ExplicitEventRole::GameInserter, LoggedInRole::RoleId);
        $permission->addRole(ExplicitEventRole::ApplicationManager, LoggedInRole::RoleId);

// permissions

        $permission->addResource(EventModel::RESOURCE_ID);
        self::createSchool($permission);
        $permission->addResource(TeacherModel::RESOURCE_ID);
        self::createApi($permission);

        // tasks
        $permission->addResource(TaskModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, TaskModel::RESOURCE_ID, 'points');
        $permission->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            TaskModel::RESOURCE_ID
        );

        self::createContestant($permission);
        self::createPerson($permission);
        // contest
        $permission->addResource(ContestModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, ContestModel::RESOURCE_ID, ['chart', 'organizerDashboard']);
        $permission->allow(ExplicitContestRole::Boss, ContestModel::RESOURCE_ID, 'acl');

        self::createOrganizer($permission);
        // submits
        $permission->addResource(SubmitModel::RESOURCE_ID);
        $permission->allow(
            [ExplicitContestRole::TaskManager, ExplicitContestRole::InboxManager],
            SubmitModel::RESOURCE_ID
        );
        self::createUpload($permission);
        //emails
        self::createEmails($permission);

        // events
        $permission->allow(ExplicitContestRole::EventManager, EventModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, EventModel::RESOURCE_ID, 'list');
        $permission->allow(ExplicitContestRole::Boss, EventModel::RESOURCE_ID, 'acl');
        $permission->allow(LoggedInRole::RoleId, EventModel::RESOURCE_ID, 'dashboard');
        // event organizers
        $permission->addResource(EventOrganizerModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, EventOrganizerModel::RESOURCE_ID);

        self::createApplications($permission);
        self::createTeamApplications($permission);
        self::createSchedule($permission);

        // spam
        $permission->addResource(SchoolLabelModel::RESOURCE_ID);
        $permission->addResource(PersonHistoryModel::RESOURCE_ID);
        $permission->addResource(PersonMailModel::RESOURCE_ID);

        $permission->allow(OrganizerRole::RoleId, SchoolLabelModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, PersonHistoryModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, PersonMailModel::RESOURCE_ID);

        self::createPayment($permission);
        self::createGame($permission);
        self::createWarehouse($permission);

        $permission->allow(ExplicitBaseRole::Cartesian);
        return $permission;
    }

    private static function createPerson(Permission $permission): void
    {
        $permission->addResource(PersonModel::RESOURCE_ID);

        $permission->allow(OrganizerRole::RoleId, PersonModel::RESOURCE_ID, 'search');
        $permission->allow(
            OrganizerRole::RoleId,
            PersonModel::RESOURCE_ID,
            ['edit', 'detail.full'],
            new IsSelfPersonAssertion()
        );
        $permission->allow(
            OrganizerRole::RoleId,
            PersonModel::RESOURCE_ID,
            'detail.basic',
            new ContestRelatedAssertion()
        );
        $permission->allow(
            ExplicitContestRole::InboxManager,
            PersonModel::RESOURCE_ID,
            ['detail.restrict', 'edit'],
            new ContestRelatedAssertion()
        );

        $permission->allow(
            [ExplicitContestRole::EventManager, ExplicitContestRole::DataManager, ExplicitContestRole::Boss],
            PersonModel::RESOURCE_ID
        );
    }

    private static function createOrganizer(Permission $permission): void
    {
        $permission->addResource(OrganizerModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, OrganizerModel::RESOURCE_ID, 'list');
        $permission->allow(
            OrganizerRole::RoleId,
            OrganizerModel::RESOURCE_ID,
            'edit',
            new IsSelfOrganizerAssertion()
        );
        $permission->allow(
            [ExplicitContestRole::DataManager, ExplicitContestRole::Boss],
            OrganizerModel::RESOURCE_ID
        );
    }

    private static function createSchool(Permission $permission): void
    {
        $permission->addResource(SchoolModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, SchoolModel::RESOURCE_ID, ['list', 'detail']);
        $permission->allow(ExplicitBaseRole::SchoolManager, SchoolModel::RESOURCE_ID);
    }

    private static function createContestant(Permission $permission): void
    {
        $permission->addResource(ContestantModel::RESOURCE_ID);
        $permission->allow(OrganizerRole::RoleId, ContestantModel::RESOURCE_ID, 'list');
        $permission->allow(ExplicitContestRole::InboxManager, ContestantModel::RESOURCE_ID, ['create']);
        $permission->allow(
            ExplicitContestRole::InboxManager,
            ContestantModel::RESOURCE_ID,
            'edit',
            new ContestContestantAssertion()
        );
    }

    private static function createApplications(Permission $permission): void
    {
        $permission->addResource(EventParticipantModel::RESOURCE_ID);
        $permission->allow(
            GuestRole::RoleId,
            EventParticipantModel::RESOURCE_ID,
            'create',
            new LogicAnd(
                new IsRegistrationOpened(),
                new IsOpenTypeEvent()
            )
        );
        $permission->allow(
            ParticipantRole::RoleId,
            EventParticipantModel::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new NotDisqualified(),
                new OwnApplication()
            )
        );
        $permission->allow(
            ParticipantRole::RoleId,
            EventParticipantModel::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new NotDisqualified(),
                new OwnApplication(),
                new IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ExplicitContestRole::EventManager, ExplicitEventRole::ApplicationManager],
            EventParticipantModel::RESOURCE_ID
        );
    }

    private static function createTeamApplications(Permission $permission): void
    {
        $permission->addResource(TeamModel2::RESOURCE_ID);
        $permission->allow(
            GuestRole::RoleId,
            TeamModel2::RESOURCE_ID,
            'create',
            new IsRegistrationOpened()
        );
        $permission->allow(
            [
                TeamTeacherRole::RoleId,
                TeamMemberRole::RoleId,
            ],
            TeamModel2::RESOURCE_ID,
            'detail',
            new LogicAnd(
                new NotDisqualified(),
                new OwnTeamApplication()
            )
        );
        $permission->allow(
            [
                TeamTeacherRole::RoleId,
                TeamMemberRole::RoleId,
            ],
            TeamModel2::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new NotDisqualified(),
                new OwnTeamApplication(),
                new IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ExplicitContestRole::EventManager, ExplicitEventRole::ApplicationManager],
            TeamModel2::RESOURCE_ID
        );
    }

    private static function createUpload(Permission $permission): void
    {
        $permission->allow(ContestantRole::RoleId, ContestModel::RESOURCE_ID, ['contestantDashboard']);
        // contestatn upload
        $permission->allow(ContestantRole::RoleId, SubmitModel::RESOURCE_ID, ['list', 'upload']);
        $permission->allow(
            ContestantRole::RoleId,
            SubmitModel::RESOURCE_ID,
            ['revoke', 'download.corrected', 'download.uploaded', 'download'],
            new OwnSubmitAssertion()
        );
    }

    private static function createApi(Permission $permission): void
    {
        $permission->addResource('export.adhoc');
        $permission->addResource('export');
        $permission->addResource(RestApiPresenter::RESOURCE_ID);
        $permission->addResource(AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->addResource(WebServiceModel::SOAP_RESOURCE_ID);
        $permission->addResource(QueryModel::RESOURCE_ID);

        $permission->allow(
            ExplicitContestRole::Web,
            ['export', QueryModel::RESOURCE_ID],
            'execute',
            new StoredQueryTagAssertion(['web-safe'])
        );
        $permission->allow(
            ExplicitContestRole::Wiki,
            ['export', QueryModel::RESOURCE_ID],
            'execute',
            new StoredQueryTagAssertion(['wiki-safe'])
        );
        $permission->allow(
            [ExplicitContestRole::Wiki, ExplicitContestRole::Web, OrganizerRole::RoleId],
            WebServiceModel::SOAP_RESOURCE_ID
        );
        $permission->allow(ExplicitContestRole::Aesop, AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->allow([ExplicitContestRole::InboxManager, ExplicitContestRole::EventManager], 'export', 'execute');
        $permission->allow(ExplicitContestRole::DataManager, QueryModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::DataManager, ['export', 'export.adhoc']);
        $permission->allow([OrganizerRole::RoleId, ExplicitContestRole::Web], RestApiPresenter::RESOURCE_ID);
    }

    private static function createPayment(Permission $permission): void
    {
        $permission->addResource(PaymentModel::ResourceId);
        $permission->allow(
            LoggedInRole::RoleId,
            PaymentModel::ResourceId,
            'detail',
            new OwnPaymentAssertion()
        );
        $permission->allow(
            LoggedInRole::RoleId,
            PaymentModel::ResourceId,
            'edit',
            new LogicAnd(
                new OwnPaymentAssertion(),
                new PaymentEditableAssertion()
            )
        );
        $permission->allow(
            [
                TeamMemberRole::RoleId,
                TeamTeacherRole::RoleId,
                ScheduleParticipant::RoleId,
            ],
            PaymentModel::ResourceId,
            'create'
        );
        $permission->allow(ExplicitContestRole::Treasurer, PaymentModel::ResourceId);
    }

    private static function createGame(Permission $permission): void
    {
        $permission->addResource('game');
        $permission->addResource(\FKSDB\Models\ORM\Models\Fyziklani\TaskModel::RESOURCE_ID);
        $permission->addResource(\FKSDB\Models\ORM\Models\Fyziklani\SubmitModel::RESOURCE_ID);

        $permission->allow(
            ExplicitEventRole::GameInserter,
            [
                \FKSDB\Models\ORM\Models\Fyziklani\SubmitModel::RESOURCE_ID,
                \FKSDB\Models\ORM\Models\Fyziklani\TaskModel::RESOURCE_ID,
            ]
        );
        $permission->allow(
            ExplicitEventRole::GameInserter,
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
                EventOrganizerRole::RoleId,
                ExplicitEventRole::GameInserter,
            ],
            'game',
            'howTo'
        );
    }

    private static function createWarehouse(Permission $permission): void
    {
        $permission->addResource(ProducerModel::RESOURCE_ID);
        $permission->addResource(ProductModel::RESOURCE_ID);
        $permission->addResource(ItemModel::RESOURCE_ID);
        $permission->allow(
            OrganizerRole::RoleId,
            [
                ProducerModel::RESOURCE_ID,
                ProductModel::RESOURCE_ID,
                ItemModel::RESOURCE_ID,
            ]
        );
    }

    private static function createSchedule(Permission $permission): void
    {
        // schedule
        $permission->addResource(ScheduleGroupModel::RESOURCE_ID);
        $permission->addResource(ScheduleItemModel::RESOURCE_ID);
        $permission->addResource(PersonScheduleModel::RESOURCE_ID);
        $permission->allow(
            EventOrganizerRole::RoleId,
            ScheduleGroupModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $permission->allow(
            EventOrganizerRole::RoleId,
            ScheduleItemModel::RESOURCE_ID,
            'detail'
        ); // TODO
        $permission->allow(
            EventOrganizerRole::RoleId,
            PersonScheduleModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $permission->allow(ExplicitContestRole::EventManager, ScheduleGroupModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, ScheduleItemModel::RESOURCE_ID);
        $permission->allow(ExplicitContestRole::EventManager, PersonScheduleModel::RESOURCE_ID);
    }

    private static function createEmails(Permission $permission): void
    {
        $permission->addResource(EmailMessageModel::ResourceId); // dashboard, detail, list, template, howTo
        $permission->allow(
            [ExplicitContestRole::DataManager, ExplicitContestRole::EventManager, ExplicitContestRole::Boss],
            EmailMessageModel::ResourceId,
            ['dashboard', 'howTo', 'list', 'template']
        );
    }
}
