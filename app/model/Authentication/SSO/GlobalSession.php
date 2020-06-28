<?php

namespace Authentication\SSO;

use FKSDB\Authentication\SSO\IGlobalSession;
use FKSDB\Authentication\SSO\IGSIDHolder;
use FKSDB\ORM\Models\ModelGlobalSession;
use FKSDB\ORM\Services\ServiceGlobalSession;
use Nette\Utils\DateTime;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GlobalSession implements IGlobalSession {

    /**
     * @var ServiceGlobalSession
     */
    private $serviceGlobalSession;

    /**
     * @var IGSIDHolder
     */
    private $gsidHolder;

    /**
     * @var ModelGlobalSession|null
     */
    private $globalSession;

    /**
     * @var string  expecting string like '+10 days'
     */
    private $expiration;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * GlobalSession constructor.
     * @param $expiration
     * @param ServiceGlobalSession $serviceGlobalSession
     * @param IGSIDHolder $gsidHolder
     */
    public function __construct($expiration, ServiceGlobalSession $serviceGlobalSession, IGSIDHolder $gsidHolder) {
        $this->expiration = $expiration;
        $this->serviceGlobalSession = $serviceGlobalSession;
        $this->gsidHolder = $gsidHolder;
    }

    /**
     * @param null $sessionId
     */
    public function start($sessionId = null) {
        $sessionId = $sessionId ?: $this->gsidHolder->getGSID();
        if ($sessionId) {
            $this->globalSession = $this->serviceGlobalSession->findByPrimary($sessionId);

            // touch the session for another expiration period
            if ($this->globalSession && !$this->globalSession->isValid()) {
                $this->globalSession->until = DateTime::from($this->expiration);
                $this->serviceGlobalSession->save($this->globalSession);
            }
        }
        $this->started = true;
    }

    /**
     * @return int|null|string
     */
    public function getId() {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($this->globalSession) {
            return $this->globalSession->session_id;
        } else {
            /*
             * TODO login_id is mandatory field (so far there's no use case
             * where it shouldn't be), that's why we cannot implement session
             * without any data.
             */
            // This must pass silently...
            // throw new NotImplementedException();
            // user_error("Cannot get session ID of session without data. Return null.", E_USER_NOTICE);
            return null;
        }
    }

    public function destroy() {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($this->globalSession) {
            $this->serviceGlobalSession->dispose($this->globalSession);
            $this->globalSession = null;
        }
        $this->started = false;
        $this->gsidHolder->setGSID(null);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($offset == self::UID) {
            return (bool)$this->globalSession;
        }
        return false;
    }

    /**
     * @param mixed $offset
     * @return bool|int|mixed
     */
    public function offsetGet($offset) {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($offset != self::UID) {
            throw new InvalidArgumentException("Cannot get offset '$offset' from global session.");
        }
        if ($this->globalSession) {
            return $this->globalSession->login_id;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value) {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($offset != self::UID) {
            throw new InvalidArgumentException("Cannot set offset '$offset' in global session.");
        }

        // lazy initialization because we need to know login id
        if (!$this->globalSession) {
            $until = DateTime::from($this->expiration);
            $this->globalSession = $this->serviceGlobalSession->createSession($value, $until);
            $this->gsidHolder->setGSID($this->globalSession->session_id);
        }

        if ($value != $this->globalSession->login_id) {
            // $this->globalSession->update(['login_id' => $value]);
            // $this->serviceGlobalSession->updateModel2($this->globalSession, ['login_id' => $value]);
            //  $this->globalSession = $this->serviceGlobalSession->refresh($this->globalSession);
            //TODO
            $this->globalSession->login_id = $value;
            $this->serviceGlobalSession->save($this->globalSession);
        }
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        if (!$this->started) {
            throw new InvalidStateException("Global session not started.");
        }
        if ($offset != self::UID) {
            throw new InvalidArgumentException("Cannot unset offset '$offset' in global session.");
        }

        // unsetting UID currently means destroying whole global session
        if ($this->globalSession) {
            $this->serviceGlobalSession->dispose($this->globalSession);
            $this->globalSession = null;
        }
    }

}
