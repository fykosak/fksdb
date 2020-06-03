<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\PointsMismatchException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Tracy\Debugger;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 * @method ModelFyziklaniSubmit createNewModel(array $data)
 */
class ServiceFyziklaniSubmit extends AbstractServiceSingle {
    const DEBUGGER_LOG_PRIORITY = 'fyziklani-info';

    const LOG_FORMAT = 'Submit %d was %s by %s';

    public function getModelClassName(): string {
        return ModelFyziklaniSubmit::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_SUBMIT;
    }

    /**
     * @param ModelFyziklaniTask $task
     * @param ModelFyziklaniTeam $team
     * @return ModelFyziklaniSubmit|null
     */
    public function findByTaskAndTeam(ModelFyziklaniTask $task, ModelFyziklaniTeam $team) {
        /** @var ModelFyziklaniSubmit $row */
        $row = $this->getTable()->where([
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'e_fyziklani_team_id' => $team->e_fyziklani_team_id,
        ])->fetch();
        return $row ?: null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
    }

    /**
     * @param ModelEvent $event
     * @param null $lastUpdated
     * @return array
     */
    public function getSubmitsAsArray(ModelEvent $event, $lastUpdated = null): array {
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $row) {
            $submit = ModelFyziklaniSubmit::createFromActiveRow($row);
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }

    public function createSubmit(ModelFyziklaniTask $task, ModelFyziklaniTeam $team, int $points, User $user): Message {
        $submit = $this->createNewModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'e_fyziklani_team_id' => $team->e_fyziklani_team_id,
            'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
            /* ugly, force current timestamp in database
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'created' => null,
        ]);
        $this->logEvent($submit, $user, 'created', \sprintf(' points %d', $points));

        return new Message(\sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $team->name,
            $team->e_fyziklani_team_id,
            $task->label,
            $task->name), ILogger::SUCCESS);
    }

    /**
     * @param ModelFyziklaniSubmit $submit
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     */
    public function changePoints(ModelFyziklaniSubmit $submit, int $points, User $user): Message {
        if (!$submit->canChange()) {
            throw new ClosedSubmittingException($submit->getFyziklaniTeam());
        }
        $this->updateModel2($submit, [
            'points' => $points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'state' => ModelFyziklaniSubmit::STATE_CHECKED,
            'modified' => null,
        ]);
        $this->logEvent($submit, $user, 'edited', \sprintf(' points %d', $points));
        return new Message(\sprintf(_('Body byly upraveny. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $submit->getFyziklaniTeam()->name,
            $submit->getFyziklaniTeam()->e_fyziklani_team_id,
            $submit->getFyziklaniTask()->label,
            $submit->getFyziklaniTask()->name), ILogger::SUCCESS);
    }

    /**
     * @param ModelFyziklaniSubmit $submit
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     * @throws BadRequestException
     */
    public function revokeSubmit(ModelFyziklaniSubmit $submit, User $user): Message {
        if (!$submit->canChange()) {
            throw new ClosedSubmittingException($submit->getFyziklaniTeam());
        }
        if (!$submit->canRevoke()) {
            throw new BadRequestException(_('Submit can\'t be revoked'));
        }
        $this->updateModel2($submit, [
            'points' => null,
            'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null,
        ]);
        $this->logEvent($submit, $user, 'revoked');
        return new Message(\sprintf(_('Submit %d has been revoked.'), $submit->fyziklani_submit_id), ILogger::SUCCESS);
    }

    /**
     * @param ModelFyziklaniSubmit $submit
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     */
    public function checkSubmit(ModelFyziklaniSubmit $submit, int $points, User $user): Message {
        if (!$submit->canChange()) {
            throw new ClosedSubmittingException($submit->getFyziklaniTeam());
        }
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->updateModel2($submit, [
            'state' => ModelFyziklaniSubmit::STATE_CHECKED,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null,
        ]);
        $this->logEvent($submit, $user, 'checked');

        return new Message(\sprintf(_('Bodovanie bolo overené. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $submit->getFyziklaniTeam()->name,
            $submit->getFyziklaniTeam()->e_fyziklani_team_id,
            $submit->getFyziklaniTask()->label,
            $submit->getFyziklaniTask()->name), ILogger::SUCCESS);
    }

    /**
     * @param ModelFyziklaniSubmit $submit
     * @param User $user
     * @param string $action
     * @param string|null $appendLog
     */
    public function logEvent(ModelFyziklaniSubmit $submit, User $user, string $action, string $appendLog = null) {
        Debugger::log(\sprintf(self::LOG_FORMAT . $appendLog, $submit->getPrimary(), $action, $user->getIdentity()->getId()), self::DEBUGGER_LOG_PRIORITY);
    }
}
