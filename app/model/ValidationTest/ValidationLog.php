<?php


namespace FKSDB\ValidationTest;

use FKSDB\Messages\Message;
use Nette\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class ValidationLog
 * @package FKSDB\ValidationTest
 */
class ValidationLog extends Message {
    /**
     * @var Html
     */
    public $detail;
    /**
     * @var string
     */
    public $testName;

    /**
     * ValidationLog constructor.
     * @param string $testName
     * @param string $message
     * @param string $level
     * @param Html|null $detail
     */
    public function __construct(string $testName, string $message, string $level, Html $detail = null) {
        parent::__construct($message, $level);
        $this->detail = $detail;
        $this->testName = $testName;
    }

    /**
     * @return array
     */
    public static function getAvailableLevels(): array {
        return [self::LVL_DANGER, self::LVL_WARNING, self::LVL_SUCCESS, self::LVL_INFO];
    }


    /**
     * @return string
     * @throws NotImplementedException
     */
    public function mapLevelToIcon(): string {
        switch ($this->getLevel()) {
            case self::LVL_DANGER:
                return 'fa fa-close';
            case self::LVL_WARNING:
                return 'fa fa-warning';
            case self::LVL_INFO:
                return 'fa fa-info';
            case self::LVL_SUCCESS:
                return 'fa fa-check';
            default:
                throw new NotImplementedException(\sprintf('%s is not supported', $this->getLevel()));
        }
    }

    /**
     * @return Html
     * @throws NotImplementedException
     */
    public function createHtmlIcon(): Html {
        $icon = Html::el('span');
        $icon->addAttributes(['class' => $this->mapLevelToIcon()]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $this->getLevel(),
            'title' => $this->getMessage(),
        ])->addHtml($icon);
    }
}
