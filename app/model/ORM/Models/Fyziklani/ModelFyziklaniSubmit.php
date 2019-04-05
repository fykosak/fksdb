<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\Messages\Message;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 * @property integer fyziklani_submit_id
 * @property integer e_fyziklani_team_id
 * @property ActiveRow e_fyziklani_team
 * @property integer fyziklani_task_id
 * @property ActiveRow fyziklani_task
 *
 * @property integer points
 * @property string state
 *
 * @property DateTime created
 * @property DateTime modified
 */
class ModelFyziklaniSubmit extends AbstractModelSingle {
    const STATE_NOT_CHECKED = 'not_checked';
    const STATE_CHECKED = 'checked';

    /**
     * @return ModelFyziklaniTask
     */
    public function getTask(): ModelFyziklaniTask {
        return ModelFyziklaniTask::createFromTableRow($this->fyziklani_task);
    }

    /**
     * @return ModelFyziklaniTeam
     */
    public function getTeam(): ModelFyziklaniTeam {
        return ModelFyziklaniTeam::createFromTableRow($this->e_fyziklani_team);
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
     * @return Message
     * @throws ClosedSubmittingException
     */
    public function changePoints(int $points): Message {
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

        return new Message(\sprintf(_('Body byly upraveny. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $this->getTeam()->name,
            $this->getTeam()->e_fyziklani_team_id,
            $this->getTask()->label,
            $this->getTask()->name), Message::LVL_SUCCESS);
    }

    /**
     * @param int $points
     * @return Message
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     */
    public function check(int $points): Message {
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
        return new Message(\sprintf(_('Bodovanie bolo overené. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $this->getTeam()->name,
            $this->getTeam()->e_fyziklani_team_id,
            $this->getTask()->label,
            $this->getTask()->name), Message::LVL_SUCCESS);
    }

}
