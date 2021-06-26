<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\WebService\AESOP\AESOPFormat;
use Nette\Application\BadRequestException;
use Nette\Application\Response;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\SmartObject;

abstract class AESOPModel {

    use SmartObject;

    protected const ID_SCOPE = 'fksdb.person_id';

    protected const END_YEAR = 'end-year';
    protected const RANK = 'rank';
    protected const POINTS = 'points';

    protected ModelContestYear $contestYear;

    protected Explorer $explorer;

    public function __construct(Container $container, ModelContestYear $contestYear) {
        $this->contestYear = $contestYear;
        $container->callInjects($this);
    }

    public function injectExplorer(Explorer $explorer): void {
        $this->explorer = $explorer;
    }

    /**
     * @return Response
     * @throws BadRequestException
     */
    public function createResponse(): Response {
        $response = $this->createFormat()->createResponse();
        $response->setName($this->getMask() . '.txt');
        return $response;
    }

    /**
     * @return AESOPFormat
     * @throws BadRequestException
     */
    abstract protected function createFormat(): AESOPFormat;

    abstract protected function getMask(): string;

}
