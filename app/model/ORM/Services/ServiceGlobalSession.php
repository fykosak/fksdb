<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelGlobalSession;
use Nette\Database\Connection;
use Nette\Http\Request;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGlobalSession extends AbstractServiceSingle {

    const SESSION_ID_LENGTH = 32;

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelGlobalSession::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_GLOBAL_SESSION;
    }

    /**
     * @var Request
     */
    private $request;

    /**
     * FKSDB\ORM\Services\ServiceGlobalSession constructor.
     * @param Request $request
     * @param Connection $connection
     */
    function __construct(Request $request, Connection $connection) {
        parent::__construct($connection);
        $this->request = $request;
    }

    /**
     *
     * @param string $loginId
     * @param DateTime $until
     * @param DateTime $since
     * @return ModelGlobalSession
     */
    public function createSession($loginId, DateTime $until = null, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        $this->getConnection()->beginTransaction();

        do {
            $sessionId = Random::generate(self::SESSION_ID_LENGTH, 'a-zA-Z0-9');
        } while ($this->findByPrimary($sessionId));
        /**
         * @var $session ModelGlobalSession
         */
        $session = $this->createNewModel([
            'session_id' => $sessionId,
            'login_id' => $loginId,
            'since' => $since,
            'until' => $until,
            'remote_ip' => $this->request->getRemoteAddress(),
        ]);
        $this->getConnection()->commit();

        return $session;
    }
}

