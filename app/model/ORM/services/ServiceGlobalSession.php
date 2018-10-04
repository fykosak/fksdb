<?php

use Nette\Database\Connection;
use Nette\DateTime;
use Nette\Http\Request;
use Nette\Utils\Strings;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGlobalSession extends AbstractServiceSingle {

    const SESSION_ID_LENGTH = 32;

    protected $tableName = DbNames::TAB_GLOBAL_SESSION;
    protected $modelClassName = 'ModelGlobalSession';

    /**
     * @var Request
     */
    private $request;

    function __construct(Request $request, Connection $connection,\Nette\Database\IReflection $reflection) {
        parent::__construct($connection,$reflection);
        $this->request = $request;
    }

    /**
     *
     * @param string $type
     * @param DateTime $until
     * @param DateTime $since
     * @return ModelAuthToken
     */
    public function createSession($loginId, DateTime $until = null, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        $this->getConnection()->beginTransaction();

        do {
            $sessionId = Strings::random(self::SESSION_ID_LENGTH, 'a-zA-Z0-9');
        } while ($this->findByPrimary($sessionId));

        $session = $this->createNew([
            'session_id' => $sessionId,
            'login_id' => $loginId,
            'since' => $since,
            'until' => $until,
            'remote_ip' => $this->request->getRemoteAddress(),
        ]);
        $this->save($session);
        $this->getConnection()->commit();

        return $session;
    }

    //TODO garbage collection
}

