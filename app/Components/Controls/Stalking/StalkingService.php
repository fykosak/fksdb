<?php

namespace FKSDB\Components\Controls\Stalking;

use Nette\Application\BadRequestException;

/**
 * Class StalkingService
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StalkingService {
    /**
     * @var array[]
     */
    private array $definition;

    public function setSections(array $definition): void {
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
