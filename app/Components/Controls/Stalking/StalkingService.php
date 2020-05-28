<?php

namespace FKSDB\Components\Controls\Stalking;

use Nette\Application\BadRequestException;

/**
 * Class StalkingService
 * *
 */
class StalkingService {
    /**
     * @var array[]
     */
    private $definition;

    /**
     * @param array $definition
     * @return void
     */
    public function setSections(array $definition) {
        $this->definition = $definition;
    }

    /**
     * @param string $name
     * @return array
     * @throws BadRequestException
     */
    public function getSection(string $name): array {
        if (!$this->definition[$name]) {
            throw new BadRequestException('Section' . $name . 'does not exist');
        }
        return $this->definition[$name];
    }
}
