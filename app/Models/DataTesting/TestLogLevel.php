<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\Logging\MessageLevel;

enum TestLogLevel: string
{
    case ERROR = 'danger';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case INFO = 'info';
    case PRIMARY = 'primary';
    case SKIP = 'skip';

    /**
     * @throws NotImplementedException
     */
    public function mapLevelToIcon(): string
    {
        return match ($this) {
            self::ERROR => 'fas fa-times',
            self::WARNING => 'fa fa-warning',
            self::INFO => 'fas fa-info',
            self::SUCCESS => 'fa fa-check',
            self::SKIP => 'fa fa-minus',
            default => throw new NotImplementedException(\sprintf('Level "%s" is not supported', $this)),
        };
    }
}
