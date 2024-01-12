<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Security\Role;
use Nette\Utils\Html;

/**
 * POD for briefer encapsulation of granted roles (instead of ModelMGrant).
 */
class ContestRole implements Role
{
    // phpcs:disable
    public const Registered = 'registered';
    public const Guest = 'guest';

    public const Webmaster = 'webmaster';
    public const TaskManager = 'taskManager';
    public const Dispatcher = 'dispatcher';
    public const DataManager = 'dataManager';
    public const EventManager = 'eventManager';
    public const InboxManager = 'inboxManager';
    public const Boss = 'boss';
    public const Organizer = 'org';
    public const Contestant = 'contestant';
    public const ExportDesigner = 'exportDesigner';
    public const Aesop = 'aesop';
    public const SchoolManager = 'schoolManager';
    public const Web = 'web';
    public const Wiki = 'wiki';
    public const Superuser = 'superuser';
    public const Cartesian = 'cartesian';
    // phpcs:enable

    private ?ContestModel $contest;
    private string $roleId;

    public function __construct(string $roleId, ?ContestModel $contest = null)
    {
        $this->roleId = $roleId;
        $this->contest = $contest;
    }

    public function getContest(): ?ContestModel
    {
        return $this->contest;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }

    public function badge(): Html
    {
        $className = 'badge bg-color-8';

        switch ($this->roleId) {
            case self::TaskManager:
                $className = 'bg-color-1';
                break;
            case self::DataManager:
                $className = 'bg-color-6';
                break;
            case self::EventManager:
                $className = 'bg-color-7';
                break;
            case self::InboxManager:
                $className = 'bg-color-9';
                break;
            case self::Boss:
                $className = 'bg-color-4';
                break;
            case self::Organizer:
            case self::Contestant:
                $className = 'bg-color-2';
                break;
            case self::SchoolManager:
                $className = 'bg-color-5';
                break;
            case self::Aesop:
            case self::Web:
            case self::Wiki:
                $className = 'bg-color-10';
                break;
            case self::Superuser:
                $className = 'bg-color-3';
                break;
            case self::Cartesian:
                $className = 'bg-color-11';
                break;
        }
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge ' . $className])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        switch ($this->roleId) {
            case self::TaskManager:
                return 'úlohář';
            case self::DataManager:
                return 'správce (dat) DB';
            case self::EventManager:
                return 'správce přihlášek';
            case self::InboxManager:
                return 'příjemce řešení';
            case self::Boss:
                return 'hlavní organizátor (šéf)';
            case self::Organizer:
                return 'základní role organizátora';
            case self::Contestant:
                return 'řešitel semináře, role je automaticky přiřazována při vytvoření řešitele';
            case self::Aesop:
                return 'oslizávač dat pro AESOP';
            case self::SchoolManager:
                return 'správce dat škol';
            case self::Web:
                return 'Dokuwiki uživatel pro fksdbexport';
            case self::Wiki:
                return 'Uživatel neveřejné Dokuwiki pro fksdbexport';
            case self::Superuser:
                return 'složení všech rolí, ACL pro authfksdb Dokuwiki plugin';
            case self::Cartesian:
                return 'cokoli s čímkoli';
        }
        return '';
    }

    public function label(): string
    {
        switch ($this->roleId) {
            case self::TaskManager:
                return 'Task manager';
            case self::DataManager:
                return 'Data manager';
            case self::EventManager:
                return 'Event manager';
            case self::InboxManager:
                return 'Inbox manager';
            case self::Boss:
                return 'Boss';
            case self::Organizer:
                return 'Organizer';
            case self::Contestant:
                return 'Contestant';
            case self::Aesop:
                return 'AESOP';
            case self::SchoolManager:
                return 'School manager';
            case self::Web:
                return 'web';
            case self::Wiki:
                return 'wiki';
            case self::Superuser:
                return 'superuser';
            case self::Cartesian:
                return 'Cartesian';
        }
        return 'unknown role';
    }
}
