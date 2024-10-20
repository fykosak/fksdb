<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Contest;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Grant\ContestGrantModel;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class ExplicitContestRole implements ContestRole
{
    // phpcs:disable
    public const Webmaster = 'contest.webmaster';
    public const TaskManager = 'contest.taskManager';
    public const DataManager = 'contest.dataManager';
    public const EventManager = 'contest.eventManager';
    public const InboxManager = 'contest.inboxManager';
    public const Treasurer = 'contest.treasurer';
    public const Boss = 'contest.boss';

    public const Aesop = 'contest.aesop';
    public const Web = 'contest.web';
    public const Wiki = 'contest.wiki';

    // phpcs:enable
    private ContestGrantModel $contestGrant;

    public function __construct(ContestGrantModel $contestGrant)
    {
        $this->contestGrant = $contestGrant;
    }

    public function getContest(): ContestModel
    {
        return $this->contestGrant->contest;
    }

    /**
     * @phpstan-return self::*
     */
    public function getRoleId(): string
    {
        return $this->contestGrant->role; //@phpstan-ignore-line
    }

    public function badge(): Html
    {
        $className = 'badge bg-color-8';

        switch ($this->contestGrant->role) {
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
            case self::Aesop:
            case self::Web:
            case self::Wiki:
                $className = 'bg-color-10';
                break;
        }
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge ' . $className])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        switch ($this->contestGrant->role) {
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
            case self::Aesop:
                return 'oslizávač dat pro AESOP';
            case self::Web:
                return 'Dokuwiki uživatel pro fksdbexport';
            case self::Wiki:
                return 'Uživatel neveřejné Dokuwiki pro fksdbexport';
        }
        throw new InvalidStateException();
    }

    public function label(): string
    {
        switch ($this->contestGrant->role) {
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
            case self::Aesop:
                return 'AESOP';
            case self::Web:
                return 'web';
            case self::Wiki:
                return 'wiki';
        }
        throw new InvalidStateException();
    }
}
