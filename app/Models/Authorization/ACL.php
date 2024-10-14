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
use FKSDB\Models\Authorization\Assertions\IsSelfOrganizerAssertion;
use FKSDB\Models\Authorization\Assertions\IsSelfPersonAssertion;
use FKSDB\Models\Authorization\Assertions\OwnSubmitAssertion;
use FKSDB\Models\Authorization\Assertions\Payments\OwnPaymentAssertion;
use FKSDB\Models\Authorization\Assertions\Payments\PaymentEditableAssertion;
use FKSDB\Models\Authorization\Assertions\StoredQueryTagAssertion;
use FKSDB\Models\Authorization\Roles\Base\GuestRole;
use FKSDB\Models\Authorization\Roles\Base\LoggedInRole;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Grant\BaseGrantModel;
use FKSDB\Models\ORM\Models\Grant\ContestGrantModel;
use FKSDB\Models\ORM\Models\Grant\EventGrantModel;
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
        $permission->addRole(LoggedInRole::RoleId);
        // BaseRole -> explicit

        $permission->addRole(BaseGrantModel::SchoolManager);
        // ContestRole -> implict
        $permission->addRole(OrganizerModel::RoleId);
        // ContestRole -> explicit
        $permission->addRole(ContestGrantModel::Aesop);
        $permission->addRole(ContestGrantModel::Web);
        $permission->addRole(ContestGrantModel::Wiki);
        $permission->addRole(ContestGrantModel::Webmaster);
        $permission->addRole(ContestGrantModel::TaskManager);
        $permission->addRole(
            ContestGrantModel::InboxManager,
            [BaseGrantModel::SchoolManager]
        );
        $permission->addRole(
            ContestGrantModel::EventManager,
            [BaseGrantModel::SchoolManager]
        );
        $permission->addRole(
            ContestGrantModel::DataManager,
            [ContestGrantModel::InboxManager, ContestGrantModel::TaskManager, ContestGrantModel::EventManager]
        );
        $permission->addRole(ContestGrantModel::Treasurer);
        $permission->addRole(
            ContestGrantModel::Boss,
            [
                ContestGrantModel::Treasurer,
                ContestGrantModel::TaskManager,
                ContestGrantModel::InboxManager,
                ContestGrantModel::DataManager,
                ContestGrantModel::EventManager,
                BaseGrantModel::SchoolManager,
            ]
        );

        // ContestYearRole -> implicit
        $permission->addRole(ContestantModel::RoleId);
        // EventRole -> implicit
        $permission->addRole(TeamTeacherModel::RoleId);
        $permission->addRole(TeamMemberModel::RoleId);
        $permission->addRole(EventParticipantModel::RoleId);
        $permission->addRole(EventOrganizerModel::RoleId);
        $permission->addRole(PersonScheduleModel::RoleId);
        // EventRole ->explicit
        $permission->addRole(EventGrantModel::GameInserter);
        $permission->addRole(EventGrantModel::ApplicationManager);

        // cartesian
        $permission->addRole(BaseGrantModel::Cartesian, ContestGrantModel::roles());
// permissions

        $permission->addResource(EventModel::RESOURCE_ID);
        self::createSchool($permission);
        $permission->addResource(TeacherModel::RESOURCE_ID);
        self::createApi($permission);

        // tasks
        $permission->addResource(TaskModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, TaskModel::RESOURCE_ID, 'points');
        $permission->allow(
            [ContestGrantModel::TaskManager, ContestGrantModel::InboxManager],
            TaskModel::RESOURCE_ID
        );

        self::createContestant($permission);
        self::createPerson($permission);
        // contest
        $permission->addResource(ContestModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, ContestModel::RESOURCE_ID, ['chart', 'organizerDashboard']);
        $permission->allow(ContestGrantModel::Boss, ContestModel::RESOURCE_ID, 'acl');

        self::createOrganizer($permission);
        // submits
        $permission->addResource(SubmitModel::RESOURCE_ID);
        $permission->allow(
            [ContestGrantModel::TaskManager, ContestGrantModel::InboxManager],
            SubmitModel::RESOURCE_ID
        );
        self::createUpload($permission);
        //emails
        self::createEmails($permission);

        // events
        $permission->allow(ContestGrantModel::EventManager, EventModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, EventModel::RESOURCE_ID, 'list');
        $permission->allow(ContestGrantModel::Boss, EventModel::RESOURCE_ID, 'acl');
        $permission->allow(LoggedInRole::RoleId, EventModel::RESOURCE_ID, 'dashboard');
        // event organizers
        $permission->addResource(EventOrganizerModel::ResourceId);
        $permission->allow(ContestGrantModel::EventManager, EventOrganizerModel::ResourceId);

        self::createApplications($permission);
        self::createTeamApplications($permission);
        self::createSchedule($permission);

        // spam
        $permission->addResource(SchoolLabelModel::RESOURCE_ID);
        $permission->addResource(PersonHistoryModel::RESOURCE_ID);
        $permission->addResource(PersonMailModel::RESOURCE_ID);

        $permission->allow(OrganizerModel::RoleId, SchoolLabelModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, PersonHistoryModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, PersonMailModel::RESOURCE_ID);

        self::createPayment($permission);
        self::createGame($permission);
        self::createWarehouse($permission);

        $permission->allow(BaseGrantModel::Cartesian);
        return $permission;
    }

    private static function createPerson(Permission $permission): void
    {
        $permission->addResource(PersonModel::RESOURCE_ID);

        $permission->allow(OrganizerModel::RoleId, PersonModel::RESOURCE_ID, 'search');
        $permission->allow(
            OrganizerModel::RoleId,
            PersonModel::RESOURCE_ID,
            ['edit', 'detail.full'],
            new IsSelfPersonAssertion()
        );
        $permission->allow(
            OrganizerModel::RoleId,
            PersonModel::RESOURCE_ID,
            'detail.basic',
            new ContestRelatedAssertion()
        );
        $permission->allow(
            ContestGrantModel::InboxManager,
            PersonModel::RESOURCE_ID,
            ['detail.restrict', 'edit'],
            new ContestRelatedAssertion()
        );

        $permission->allow(
            [ContestGrantModel::EventManager, ContestGrantModel::DataManager, ContestGrantModel::Boss],
            PersonModel::RESOURCE_ID
        );
    }

    private static function createOrganizer(Permission $permission): void
    {
        $permission->addResource(OrganizerModel::Resourceid);
        $permission->allow(OrganizerModel::RoleId, OrganizerModel::Resourceid, 'list');
        $permission->allow(
            OrganizerModel::RoleId,
            OrganizerModel::Resourceid,
            'edit',
            new IsSelfOrganizerAssertion()
        );
        $permission->allow(
            [ContestGrantModel::DataManager, ContestGrantModel::Boss],
            OrganizerModel::Resourceid
        );
    }

    private static function createSchool(Permission $permission): void
    {
        $permission->addResource(SchoolModel::RESOURCE_ID);
        $permission->allow(OrganizerModel::RoleId, SchoolModel::RESOURCE_ID, ['list', 'detail']);
        $permission->allow(BaseGrantModel::SchoolManager, SchoolModel::RESOURCE_ID);
    }

    private static function createContestant(Permission $permission): void
    {
        $permission->addResource(ContestantModel::ResourceId);
        $permission->allow(OrganizerModel::RoleId, ContestantModel::ResourceId, 'list');
        $permission->allow(ContestGrantModel::InboxManager, ContestantModel::ResourceId, ['create']);
        $permission->allow(
            ContestGrantModel::InboxManager,
            ContestantModel::ResourceId,
            'edit',
            new ContestContestantAssertion()
        );
    }

    private static function createApplications(Permission $permission): void
    {
        $permission->addResource(EventParticipantModel::ResourceId);
        $permission->allow(
            GuestRole::RoleId,
            EventParticipantModel::ResourceId,
            'create',
            new LogicAnd(
                new IsRegistrationOpened(),
                new IsOpenTypeEvent()
            )
        );
        $permission->allow(
            EventParticipantModel::RoleId,
            EventParticipantModel::ResourceId,
            'detail',
            new LogicAnd(
                new NotDisqualified(),
                new OwnApplication()
            )
        );
        $permission->allow(
            EventParticipantModel::RoleId,
            EventParticipantModel::ResourceId,
            'edit',
            new LogicAnd(
                new NotDisqualified(),
                new OwnApplication(),
                new IsRegistrationOpened()
            )
        );
        $permission->allow(
            [ContestGrantModel::EventManager, EventGrantModel::ApplicationManager],
            EventParticipantModel::ResourceId
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
                TeamTeacherModel::RoleId,
                TeamMemberModel::RoleId,
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
                TeamTeacherModel::RoleId,
                TeamMemberModel::RoleId,
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
            [ContestGrantModel::EventManager, EventGrantModel::ApplicationManager],
            TeamModel2::RESOURCE_ID
        );
    }

    private static function createUpload(Permission $permission): void
    {
        $permission->allow(ContestantModel::RoleId, ContestModel::RESOURCE_ID, ['contestantDashboard']);
        // contestatn upload
        $permission->allow(ContestantModel::RoleId, SubmitModel::RESOURCE_ID, ['list', 'upload']);
        $permission->allow(
            ContestantModel::RoleId,
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
            ContestGrantModel::Web,
            ['export', QueryModel::RESOURCE_ID],
            'execute',
            new StoredQueryTagAssertion(['web-safe'])
        );
        $permission->allow(
            ContestGrantModel::Wiki,
            ['export', QueryModel::RESOURCE_ID],
            'execute',
            new StoredQueryTagAssertion(['wiki-safe'])
        );
        $permission->allow(
            [ContestGrantModel::Wiki, ContestGrantModel::Web, OrganizerModel::RoleId],
            WebServiceModel::SOAP_RESOURCE_ID
        );
        $permission->allow(ContestGrantModel::Aesop, AESOPPresenter::AESOP_RESOURCE_ID);
        $permission->allow([ContestGrantModel::InboxManager, ContestGrantModel::EventManager], 'export', 'execute');
        $permission->allow(ContestGrantModel::DataManager, QueryModel::RESOURCE_ID);
        $permission->allow(ContestGrantModel::DataManager, ['export', 'export.adhoc']);
        $permission->allow([OrganizerModel::RoleId, ContestGrantModel::Web], RestApiPresenter::RESOURCE_ID);
    }

    private static function createPayment(Permission $permission): void
    {
        $permission->addResource(PaymentModel::RESOURCE_ID);
        $permission->allow(
            LoggedInRole::RoleId,
            PaymentModel::RESOURCE_ID,
            'detail',
            new OwnPaymentAssertion()
        );
        $permission->allow(
            LoggedInRole::RoleId,
            PaymentModel::RESOURCE_ID,
            'edit',
            new LogicAnd(
                new OwnPaymentAssertion(),
                new PaymentEditableAssertion()
            )
        );
        $permission->allow(
            [
                TeamMemberModel::RoleId,
                TeamTeacherModel::RoleId,
                PersonScheduleModel::RoleId,
            ],
            PaymentModel::RESOURCE_ID,
            'create'
        );
        $permission->allow(ContestGrantModel::Treasurer, PaymentModel::RESOURCE_ID);
    }

    private static function createGame(Permission $permission): void
    {
        $permission->addResource('game');
        $permission->addResource(\FKSDB\Models\ORM\Models\Fyziklani\TaskModel::RESOURCE_ID);
        $permission->addResource(\FKSDB\Models\ORM\Models\Fyziklani\SubmitModel::RESOURCE_ID);

        $permission->allow(
            EventGrantModel::GameInserter,
            [
                \FKSDB\Models\ORM\Models\Fyziklani\SubmitModel::RESOURCE_ID,
                \FKSDB\Models\ORM\Models\Fyziklani\TaskModel::RESOURCE_ID,
            ]
        );
        $permission->allow(
            EventGrantModel::GameInserter,
            'game',
            ['diplomas.results', 'close', 'dashboard']
        );
        $permission->allow(
            OrganizerModel::RoleId,
            'game',
            ['gameSetup', 'statistics', 'presentation', 'seating', 'diplomas']
        );
        $permission->allow(
            [
                OrganizerModel::RoleId,
                EventOrganizerModel::RoleId,
                EventGrantModel::GameInserter,
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
            OrganizerModel::RoleId,
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
        $permission->addResource(PersonScheduleModel::ResourceId);
        $permission->allow(
            EventOrganizerModel::RoleId,
            ScheduleGroupModel::RESOURCE_ID,
            ['list', 'detail']
        ); // TODO
        $permission->allow(
            EventOrganizerModel::RoleId,
            ScheduleItemModel::RESOURCE_ID,
            'detail'
        ); // TODO
        $permission->allow(
            EventOrganizerModel::RoleId,
            PersonScheduleModel::ResourceId,
            ['list', 'detail']
        ); // TODO
        $permission->allow(ContestGrantModel::EventManager, ScheduleGroupModel::RESOURCE_ID);
        $permission->allow(ContestGrantModel::EventManager, ScheduleItemModel::RESOURCE_ID);
        $permission->allow(ContestGrantModel::EventManager, PersonScheduleModel::ResourceId);
    }

    private static function createEmails(Permission $permission): void
    {
        $permission->addResource(EmailMessageModel::RESOURCE_ID); // dashboard, detail, list, template, howTo
        $permission->allow(
            [ContestGrantModel::DataManager, ContestGrantModel::EventManager, ContestGrantModel::Boss],
            EmailMessageModel::RESOURCE_ID,
            ['dashboard', 'howTo', 'list', 'template']
        );
    }
}
