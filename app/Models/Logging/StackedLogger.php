<?php

declare(strict_types=1);

namespace FKSDB\Models\Logging;

use FKSDB\Models\Messages\Message;

abstract class StackedLogger implements Logger
{

    private ?Logger $child = null;

    final public function log(Message $message): void
    {
        $this->doLog($message);
        if ($this->getChild()) {
            $this->getChild()->log($message);
        }
    }

    abstract protected function doLog(Message $message): void;

    public function getChild(): ?Logger
    {
        return $this->child;
    }

    public function setChild(Logger $child): void
    {
        $this->child = $child;
    }
}
