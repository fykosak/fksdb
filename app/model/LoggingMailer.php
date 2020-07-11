<?php

use FKSDB\Utils\Utils;
use Nette\DI\Container;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\SmartObject;

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
     * @var string
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
    /** @var Container */
    private $container;

    /**
     * LoggingMailer constructor.
     * @param IMailer $mailer
     * @param Container $container
     */
    public function __construct(IMailer $mailer,Container $container) {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function getLogPath(): string {
        return $this->logPath;
    }

    /**
     * @param string $logPath
     * @return void
     */
    public function setLogPath(string $logPath) {
        $this->logPath = $logPath;
        @mkdir($this->logPath, 0770, true);
    }

    public function getLogging(): bool {
        return $this->logging;
    }

    /**
     * @param bool $logging
     * @return void
     */
    public function setLogging(bool $logging) {
        $this->logging = $logging;
    }

    /**
     * @param Message $mail
     * @return void
     * @throws Exception
     */
    public function send(Message $mail) {
        try {
            if (!$this->container->getParameters()['email']['disabled'] ?? false) {// do not really send emails when debugging
                $this->mailer->send($mail);
            }
            $this->logMessage($mail);
        } catch (Exception $exception) {
            $this->logMessage($mail, $exception);
            throw $exception;
        }
    }

    public function getSentMessages(): int {
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
