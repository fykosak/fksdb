<?php

namespace FKSDB\Mail;

use Exception;
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

    private IMailer $mailer;

    private string $logPath;

    private bool $logging = true;

    private int $sentMessages = 0;

    private Container $container;

    public function __construct(IMailer $mailer, Container $container) {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function getLogPath(): string {
        return $this->logPath;
    }

    public function setLogPath(string $logPath): void {
        $this->logPath = $logPath;
        @mkdir($this->logPath, 0770, true);
    }

    public function getLogging(): bool {
        return $this->logging;
    }

    public function setLogging(bool $logging): void {
        $this->logging = $logging;
    }

    /**
     * @param Message $mail
     * @return void
     * @throws Exception
     */
    public function send(Message $mail): void {
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

    private function logMessage(Message $mail, ?Exception $e = null): void {
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
