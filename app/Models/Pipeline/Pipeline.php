<?php

declare(strict_types=1);

namespace FKSDB\Models\Pipeline;

use Fykosak\Utils\Logging\MemoryLogger;

/**
 * Represents a simple pipeline where each stage has its input and output and they
 * comprise a linear chain.
 */
class Pipeline
{

    /** @var Stage[] */
    public array $stages = [];

    public ?MemoryLogger $logger;

    public function __construct()
    {
        $this->logger = new MemoryLogger();
    }

    /**
     * Starts the pipeline.
     * @param mixed $data
     * @return mixed    output of the last stage
     */
    public function __invoke($data)
    {
        foreach ($this->stages as $stage) {
            $data = $stage($this->logger, $data);
        }
        return $data;
    }
}
