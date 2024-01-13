<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\Authorization\Assertions\OwnApplicationAssertion;
use FKSDB\Models\Authorization\Assertions\OwnerAssertion;
use FKSDB\Models\Authorization\Assertions\PaymentEditableAssertion;
use FKSDB\Models\Authorization\Assertions\SelfAssertion;
use FKSDB\Models\Authorization\Assertions\StoredQueryTagAssertion;
use FKSDB\Models\Authorization\EventRole\ContestOrganizerRole;
use FKSDB\Models\Authorization\EventRole\EventOrganizerRole;
use FKSDB\Models\Authorization\EventRole\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\EventRole\Fyziklani\TeamTeacherRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
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
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use Nette\Security\Permission;

final class ACL
{
    public static function create(
        OwnerAssertion $ownerAssertion,
        SelfAssertion $selfAssertion,
        PaymentEditableAssertion $paymentEditableAssertion,
        OwnApplicationAssertion $ownApplicationAssertion
    ): Permission {
        $service = new Permission();
        $service->addRole(TeamTeacherRole::ROLE_ID);
        $service->addRole(TeamMemberRole::ROLE_ID);
        $service->addRole(EventOrganizerRole::ROLE_ID);
        $service->addRole(ContestOrganizerRole::ROLE_ID);
        $service->addRole(ParticipantRole::ROLE_ID);

        $service->addRole(BaseRole::Guest);
        $service->addRole(BaseRole::Registered, BaseRole::Guest);
        $service->addRole(ContestRole::Contestant, BaseRole::Registered);
        $service->addRole(ContestRole::Organizer, BaseRole::Registered);
        $service->addRole(ContestRole::Webmaster, ContestRole::Organizer);
        $service->addRole(ContestRole::TaskManager, ContestRole::Organizer);
        $service->addRole(ContestRole::InboxManager, ContestRole::Organizer);
        $service->addRole(ContestRole::SchoolManager);
        $service->addRole(ContestRole::EventManager, [ContestRole::Organizer, ContestRole::SchoolManager]);
        $service->addRole(
            ContestRole::DataManager,
            [ContestRole::InboxManager, ContestRole::TaskManager, ContestRole::EventManager]
        );
        $service->addRole(ContestRole::Boss, ContestRole::Organizer);
        $service->addRole(
            ContestRole::Superuser,
            [
                ContestRole::Organizer,
                ContestRole::TaskManager,
                ContestRole::SchoolManager,
                ContestRole::InboxManager,
                ContestRole::DataManager,
                ContestRole::EventManager,
                ContestRole::Boss,
            ]
        );
        $service->addRole(ContestRole::Cartesian);
        $service->addRole(ContestRole::Aesop);
        $service->addRole(ContestRole::Web);
        $service->addRole(ContestRole::Wiki);

        $service->addResource(SubmitModel::RESOURCE_ID);
        $service->addResource(TaskModel::RESOURCE_ID);
        $service->addResource(PersonModel::RESOURCE_ID);
        $service->addResource(ContestantModel::RESOURCE_ID);
        $service->addResource(SchoolModel::RESOURCE_ID);
        $service->addResource(QueryModel::RESOURCE_ID);
        $service->addResource(OrganizerModel::RESOURCE_ID);
        $service->addResource(EventModel::RESOURCE_ID);
        $service->addResource(TeacherModel::RESOURCE_ID);
        $service->addResource(EmailMessageModel::RESOURCE_ID);
        $service->addResource(ContestModel::RESOURCE_ID);
        $service->addResource(EventParticipantModel::RESOURCE_ID);
        $service->addResource(TeamModel2::RESOURCE_ID);
        $service->addResource(ScheduleGroupModel::RESOURCE_ID);
        $service->addResource(ScheduleItemModel::RESOURCE_ID);
        $service->addResource(PersonScheduleModel::RESOURCE_ID);
        $service->addResource(ProducerModel::RESOURCE_ID);
        $service->addResource(ProductModel::RESOURCE_ID);
        $service->addResource(ItemModel::RESOURCE_ID);
        $service->addResource(PaymentModel::RESOURCE_ID);
        $service->addResource(EventOrganizerModel::RESOURCE_ID);

        $service->addResource('export.adhoc');
        $service->addResource('export');
        $service->addResource('webService');
        $service->addResource('aesop');
        $service->addResource('soap');


// contestatn upload
        $service->allow(ContestRole::Contestant, SubmitModel::RESOURCE_ID, ['list', 'upload']);
        $service->allow(
            ContestRole::Contestant,
            SubmitModel::RESOURCE_ID,
            ['revoke', 'download.corrected', 'download.uploaded', 'download'],
            fn(...$args): bool => $ownerAssertion->isSubmitUploader(...$args)
        );
// basic for organizer
        $service->allow(ContestRole::Organizer, TaskModel::RESOURCE_ID, 'points');
        $service->allow(ContestRole::Organizer, ContestantModel::RESOURCE_ID, 'list');
        $service->allow(ContestRole::Organizer, SchoolModel::RESOURCE_ID, 'list');
        $service->allow(ContestRole::Organizer, OrganizerModel::RESOURCE_ID, 'list');
        $service->allow(
            ContestRole::Organizer,
            OrganizerModel::RESOURCE_ID,
            'edit',
            $selfAssertion
        );
        $service->allow(ContestRole::Organizer, PersonModel::RESOURCE_ID, 'search');
        $service->allow(
            ContestRole::Organizer,
            PersonModel::RESOURCE_ID,
            'edit',
            $selfAssertion
        );
        $service->allow(
            ContestRole::Organizer,
            PersonModel::RESOURCE_ID,
            'detail.basic',
            fn(...$args) => $ownerAssertion->existsOwnContestant(...$args)
        );
        $service->allow(
            ContestRole::Organizer,
            PersonModel::RESOURCE_ID,
            'detail.full',
            $selfAssertion
        );
        $service->allow(ContestRole::Organizer, ContestModel::RESOURCE_ID, 'chart');
        $service->allow(ContestRole::Organizer, 'webService', 'default');

        $service->allow(ContestRole::InboxManager, 'export', 'execute');
        $service->allow(ContestRole::InboxManager, SubmitModel::RESOURCE_ID);
        $service->allow(ContestRole::InboxManager, TaskModel::RESOURCE_ID);
        $service->allow(ContestRole::InboxManager, ContestantModel::RESOURCE_ID, ['list', 'create']);
        $service->allow(
            ContestRole::InboxManager,
            ContestantModel::RESOURCE_ID,
            'edit',
            fn(...$args) => $ownerAssertion->isOwnContestant(...$args)
        );
        $service->allow(ContestRole::InboxManager, SchoolModel::RESOURCE_ID);
        $service->allow(
            ContestRole::InboxManager,
            PersonModel::RESOURCE_ID,
            'edit',
            fn(...$args) => $ownerAssertion->existsOwnContestant(...$args)
        );
        $service->allow(
            ContestRole::InboxManager,
            PersonModel::RESOURCE_ID,
            'detail.restrict',
            fn(...$args) => $ownerAssertion->existsOwnContestant(...$args)
        );
        $service->allow(ContestRole::TaskManager, SubmitModel::RESOURCE_ID);
        $service->allow(ContestRole::TaskManager, TaskModel::RESOURCE_ID);
        $service->allow(ContestRole::DataManager, QueryModel::RESOURCE_ID);
        $service->allow(ContestRole::DataManager, 'export');
        $service->allow(ContestRole::DataManager, 'export.adhoc');
        $service->allow(ContestRole::DataManager, PersonModel::RESOURCE_ID);
        $service->allow(ContestRole::DataManager, EmailMessageModel::RESOURCE_ID, 'list');
        $service->allow(ContestRole::SchoolManager, SchoolModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, EventModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, 'export', 'execute');
        $service->allow(ContestRole::EventManager, PersonModel::RESOURCE_ID, ['edit', 'detail.full']);
        $service->allow(ContestRole::Boss, OrganizerModel::RESOURCE_ID);
        $service->allow(ContestRole::Boss, PersonModel::RESOURCE_ID);
        $service->allow(ContestRole::Boss, EmailMessageModel::RESOURCE_ID, 'list');
        $service->allow(ContestRole::Boss, ContestModel::RESOURCE_ID, 'acl');
        $service->allow(
            ContestRole::Web,
            'export',
            'execute',
            new StoredQueryTagAssertion(['web-safe'])
        );
        $service->allow(ContestRole::Web, 'webService');
        $service->allow(
            ContestRole::Wiki,
            'export',
            'execute',
            new StoredQueryTagAssertion(['wiki-safe'])
        );
        $service->allow([ContestRole::Wiki, ContestRole::Web, ContestRole::Organizer], 'soap', 'default');
        $service->allow(BaseRole::Guest, [TeamModel2::RESOURCE_ID, EventParticipantModel::RESOURCE_ID], 'create');
        $service->allow(
            [TeamTeacherRole::ROLE_ID, TeamMemberRole::ROLE_ID, ParticipantRole::ROLE_ID],
            [TeamModel2::RESOURCE_ID, EventParticipantModel::RESOURCE_ID],
            ['detail', 'edit'],
            $ownApplicationAssertion
        );
        $service->allow(
            ContestRole::EventManager,
            ['fyziklani.team', 'event.participant']
        );
        $service->allow(ContestRole::Organizer, EventModel::RESOURCE_ID, 'list');
        $service->allow(EventOrganizerRole::ROLE_ID, EventParticipantModel::RESOURCE_ID, ['list', 'detail']);
        $service->allow(EventOrganizerRole::ROLE_ID, ScheduleGroupModel::RESOURCE_ID, ['list', 'detail']);
        $service->allow(EventOrganizerRole::ROLE_ID, ScheduleItemModel::RESOURCE_ID, 'detail');
        $service->allow(EventOrganizerRole::ROLE_ID, PersonScheduleModel::RESOURCE_ID, ['list', 'detail']);
        $service->allow(ContestRole::EventManager, EventOrganizerModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, EventParticipantModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, EventModel::RESOURCE_ID, 'chart');
        $service->allow(ContestRole::EventManager, ScheduleGroupModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, ScheduleItemModel::RESOURCE_ID);
        $service->allow(ContestRole::EventManager, PersonScheduleModel::RESOURCE_ID);

        $service->allow(BaseRole::Registered, EventModel::RESOURCE_ID,'dashboard');
        $service->allow(BaseRole::Registered, PaymentModel::RESOURCE_ID, 'detail', $selfAssertion);
        $service->allow(
            BaseRole::Registered,
            PaymentModel::RESOURCE_ID,
            'edit',
            new LogicAnd($selfAssertion, $paymentEditableAssertion)// @phpstan-ignore-line
        );
        $service->allow(
            [TeamMemberRole::ROLE_ID, TeamTeacherRole::ROLE_ID],
            PaymentModel::RESOURCE_ID,
            'create'
        );
        $service->allow(ContestRole::EventManager, PaymentModel::RESOURCE_ID);
        $service->allow(ContestRole::Organizer, ProducerModel::RESOURCE_ID);
        $service->allow(ContestRole::Organizer, ProductModel::RESOURCE_ID);
        $service->allow(ContestRole::Organizer, ItemModel::RESOURCE_ID);

        self::createGame($service);

        $service->allow(ContestRole::Cartesian);
        return $service;
    }

    private static function createGame(Permission $permission): void
    {
        $permission->addResource('game.dashboard');
        $permission->addResource('game.task');
        $permission->addResource('game.close');
        $permission->addResource('game.presentation');
        $permission->addResource('game.submit');
        $permission->addResource('game.gameSetup');
        $permission->addResource('game.diplomas');
        $permission->addResource('game.seating');
        $permission->addResource('game.statistics');

        $permission->allow([ContestRole::Organizer, EventOrganizerRole::ROLE_ID], 'game.dashboard');
        $permission->allow([ContestRole::Organizer, EventOrganizerRole::ROLE_ID], 'game.task');
        $permission->allow([ContestRole::Organizer, EventOrganizerRole::ROLE_ID], 'game.close', 'default');
        $permission->allow([ContestRole::Organizer, EventOrganizerRole::ROLE_ID], 'game.submit');
        $permission->allow([ContestRole::Organizer, EventOrganizerRole::ROLE_ID], 'game.diplomas', 'results');
        $permission->allow(ContestRole::Organizer, 'game.presentation');
        $permission->allow(ContestRole::Organizer, 'game.gameSetup');
        $permission->allow(ContestRole::Organizer, 'game.statistics');
        $permission->allow(ContestRole::Organizer, 'game.seating');
        $permission->allow(ContestRole::Organizer, 'game.diplomas');
    }
}
