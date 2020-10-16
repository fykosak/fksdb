<?php

namespace FKSDB\Authentication\SSO\ServiceSide;

use FKSDB\Authentication\SSO\IGSIDHolder;
use Nette\Database\Connection;

/**
 * Reads and stores GSID from "real" session (expects its started).
 * Furthemore, GSID may be infered from a token in GET data
 * (this overrides "real" session data).
 * The token is verified against shared MySQL database.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TokenGSIDHolder implements IGSIDHolder {

    public const TABLE = 'auth_token';
    public const URL_PARAM = 'at';
    public const SESSION_KEY = '_sso';

    private Connection $connection;
    /** @var bool */
    private $cachedGSID = false;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @return bool|null
     */
    public function getGSID() {
        if ($this->cachedGSID === false) {
            if (isset($_GET[self::URL_PARAM])) {
                $token = $_GET[self::URL_PARAM];
                unset($_GET[self::URL_PARAM]);
                $this->cachedGSID = $this->getGSIDFromDB($token);
            }
            if (!$this->cachedGSID && isset($_SESSION[self::SESSION_KEY])) {
                $this->cachedGSID = $_SESSION[self::SESSION_KEY];
            }
            // no matter where the value comes from we re-store it to the session
            $this->setGSID($this->cachedGSID);
        }
        return $this->cachedGSID;
    }

    /**
     * @param mixed $gsid
     * @return void
     */
    public function setGSID($gsid): void {
        if ($gsid) {
            $_SESSION[self::SESSION_KEY] = $gsid;
            $this->cachedGSID = $gsid;
        } elseif (session_status() != PHP_SESSION_DISABLED) {
            unset($_SESSION[self::SESSION_KEY]);
            $this->cachedGSID = null;
        }
    }

    /**
     * @param string $token
     * @return null
     */
    private function getGSIDFromDB($token) {
        $sql = 'SELECT data FROM `' . self::TABLE . '`
            where token = ?
            and since <= now()
            and (until is null or until >= now())';

        $stmt = $this->connection->getPdo()->prepare($sql);

        $stmt->execute([$token]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        } else {
            return $row['data'];
        }
    }
}
