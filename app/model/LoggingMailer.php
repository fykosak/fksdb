<?php

use FKSDB\Config\GlobalParameters;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LoggingMailer implements IMailer {
    use SmartObject;

    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @var GlobalParameters
     */
    private $parameters;
    /**
     * @var mixed
     */
    private $logPath;
    /**
     * @var bool
     */
    private $logging = true;
    /**
     * @var int
     */
    private $sentMessages = 0;

    /**
     * LoggingMailer constructor.
     * @param IMailer $mailer
     * @param GlobalParameters $parameters
     */
    public function __construct(IMailer $mailer, GlobalParameters $parameters) {
        $this->mailer = $mailer;
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getLogPath() {
        return $this->logPath;
    }

    /**
     * @param $logPath
     * @return void
     */
    public function setLogPath($logPath) {
        $this->logPath = $logPath;
        @mkdir($this->logPath, 0770, true);
    }

    /**
     * @return bool
     */
    public function getLogging() {
        return $this->logging;
    }

    /**
     * @param $logging
     * @return void
     */
    public function setLogging($logging) {
        $this->logging = $logging;
    }

    /**
     * @param Message $mail
     * @throws Exception
     */
    public function send(Message $mail) {
        try {
            if (!Arrays::get($this->parameters['email'], 'disabled', false)) {// do not really send emails when debugging
                $this->mailer->send($mail);
            }
            $this->logMessage($mail);
        } catch (Exception $exception) {
            $this->logMessage($mail, $exception);
            throw $exception;
        }
    }

    /**
     * @return int
     */
    public function getSentMessages() {
        return $this->sentMessages;
    }

    /**
     * @param Message $mail
     * @param Exception|null $e
     */
    private function logMessage(Message $mail, Exception $e = null) {
        if (!$this->logging) {
            return;
        }
        $fingerprint = Utils::getFingerprint($mail->getHeaders());
        $filename = 'mail-' . @date('Y-m-d-H-i-s') . '-' . $fingerprint . '.txt';
        $f = fopen($this->logPath . DIRECTORY_SEPARATOR . $filename, 'w');

        if ($e) {
            fprintf($f, "FAILED %s\n", $e->getMessage());
        }
        fwrite($f, $mail->generateMessage());

        fclose($f);

        $this->sentMessages += 1;
    }

}
