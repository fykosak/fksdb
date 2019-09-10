<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\Messages\Message;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 * @property-read string state
 * @property-read integer e_fyziklani_team_id
 * @property-read integer points
 * @property-read integer fyziklani_task_id
 * @property-read integer fyziklani_submit_id
 * @property-read integer task_id
 * @property-read ActiveRow e_fyziklani_team
 * @property-read ActiveRow fyziklani_task
 * @property-read DateTime created
 * @property-read DateTime modified
 */
class ModelFyziklaniSubmit extends AbstractModelSingle {
    const STATE_NOT_CHECKED = 'not_checked';
    const STATE_CHECKED = 'checked';

    const DEBUGGER_LOG_PRIORITY = 'fyziklani-info';

    /**
     * @return ModelFyziklaniTask
     */
    public function getTask(): ModelFyziklaniTask {
        return ModelFyziklaniTask::createFromActiveRow($this->fyziklani_task);
    }

    /**
     * @return ModelFyziklaniTeam
     */
    public function getTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromActiveRow($this->e_fyziklani_team);
    }

    /**
     * @return bool
     */
    public function isChecked(): bool {
        return $this->state === self::STATE_CHECKED;
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'points' => $this->points,
            'teamId' => $this->e_fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'created' => $this->created->format('c'),
        ];
    }

    /**
     * @return bool
     */
    public function canChange(): bool {
        if ($this->getTeam()->hasOpenSubmitting()) {
            return true;
        }
        return false;
    }

    /**
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     */
    public function changePoints(int $points, User $user): Message {
        if (!$this->canChange()) {
            throw new ClosedSubmittingException($this->getTeam());
        }
        $this->update([
            'points' => $points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'state' => self::STATE_NOT_CHECKED,
            'modified' => null,
        ]);
        Debugger::log(\sprintf('Submit edited points %d by %s', $points, $user->getIdentity()->getId()), self::DEBUGGER_LOG_PRIORITY);

        return new Message(\sprintf(_('Body byly upraveny. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $this->getTeam()->name,
            $this->getTeam()->e_fyziklani_team_id,
            $this->getTask()->label,
            $this->getTask()->name), Message::LVL_SUCCESS);
    }

    /**
     * @param User $user
     * @return Message
     */
    public function revoke(User $user): Message {
        $this->update([
            'points' => null,
            'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        Debugger::log(\sprintf('Submit %d revoked by %s', $this->fyziklani_submit_id, $user->getIdentity()->getId()), self::DEBUGGER_LOG_PRIORITY);
        return new Message(\sprintf(_('Submit %d has been revoked.'), $this->fyziklani_submit_id), Message::LVL_SUCCESS);
    }

    /**
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     */
    public function check(int $points, User $user): Message {
        if (!$this->canChange()) {
            throw new ClosedSubmittingException($this->getTeam());
        }
        if ($this->points != $points) {
            throw new PointsMismatchException();
        }
        $this->update([
            'state' => self::STATE_CHECKED,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null,
        ]);
        Debugger::log(\sprintf('Submit %d checked by %s', $this->fyziklani_submit_id, $user->getIdentity()->getId()), self::DEBUGGER_LOG_PRIORITY);

        return new Message(\sprintf(_('Bodovanie bolo overené. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $this->getTeam()->name,
            $this->getTeam()->e_fyziklani_team_id,
            $this->getTask()->label,
            $this->getTask()->name), Message::LVL_SUCCESS);
    }
}
