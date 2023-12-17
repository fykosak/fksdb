<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

class TestLogger
{
    /** @var TestMessage[] */
    private array $messages = [];

    /**
     * @return TestMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    public function log(TestMessage $message): void
    {
        $this->messages[] = $message;
    }
}
