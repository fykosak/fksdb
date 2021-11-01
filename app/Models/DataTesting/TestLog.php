<?php

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\Html;

class TestLog extends Message {

    public const LVL_SKIP = 'secondary';

    public ?Html $detail;

    public string $testName;

    public function __construct(string $testName, string $message, string $level, ?Html $detail = null) {
        parent::__construct($message, $level);
        $this->detail = $detail;
        $this->testName = $testName;
    }

    /**
     * @return string[]
     */
    public static function getAvailableLevels(): array {
        return [self::LVL_ERROR, self::LVL_WARNING, self::LVL_SUCCESS, self::LVL_INFO, self::LVL_SKIP];
    }

    /**
     * @throws NotImplementedException
     */
    public function mapLevelToIcon(): string {
        switch ($this->level) {
            case self::LVL_ERROR:
                return 'fas fa-times';
            case self::LVL_WARNING:
                return 'fa fa-warning';
            case self::LVL_INFO:
                return 'fas fa-info';
            case self::LVL_SUCCESS:
                return 'fa fa-check';
            case self::LVL_SKIP:
                return 'fa fa-minus';
            default:
                throw new NotImplementedException(\sprintf('Level "%s" is not supported', $this->level));
        }
    }

    /**
     * @throws NotImplementedException
     */
    public function createHtmlIcon(): Html {
        $icon = Html::el('span');
        $icon->addAttributes([
            'class' => $this->mapLevelToIcon(),
        ]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $this->level,
            'title' => $this->text,
        ])->addHtml($icon);
    }
}
