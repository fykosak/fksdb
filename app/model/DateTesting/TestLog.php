<?php

namespace FKSDB\DataTesting;

use FKSDB\Messages\Message;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class TestLog
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TestLog extends Message {

    public const LVL_SKIP = 'secondary';

    public ?Html $detail;

    public string $testName;

    /**
     * TestLog constructor.
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
     * @return string[]
     */
    public static function getAvailableLevels(): array {
        return [self::LVL_DANGER, self::LVL_WARNING, self::LVL_SUCCESS, self::LVL_INFO, self::LVL_SKIP];
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
            case self::LVL_SKIP:
                return 'fa fa-minus';
            default:
                throw new NotImplementedException(\sprintf('Level "%s" is not supported', $this->getLevel()));
        }
    }

    /**
     * @return Html
     * @throws NotImplementedException
     */
    public function createHtmlIcon(): Html {
        $icon = Html::el('span');
        $icon->addAttributes([
            'class' => $this->mapLevelToIcon(),
        ]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $this->getLevel(),
            'title' => $this->getMessage(),
        ])->addHtml($icon);
    }
}
