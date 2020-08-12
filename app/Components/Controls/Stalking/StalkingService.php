<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class StalkingService
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StalkingService {
    /** @var array[] */
    private $definition;

    public function setSections(array $definition): void {
        $this->definition = $definition;
    }

    /**
     * @param string $name
     * @return array
     * @throws \OutOfRangeException
     */
    public function getSection(string $name): array {
        if (!$this->definition[$name]) {
            throw new \OutOfRangeException('Section' . $name . 'does not exist');
        }
        return $this->definition[$name];
    }
}
