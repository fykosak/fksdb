<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BreadcrumbsFactory {

    /**
     * @var string
     */
    private $expiration;
    /**
     * @var Container
     */
    private $container;

    /**
     * BreadcrumbsFactory constructor.
     * @param $expiration
     * @param Container $container
     */
    public function __construct($expiration, Container $container) {
        $this->expiration = $expiration;
        $this->container = $container;
    }

    public function create(): Breadcrumbs {
        return new Breadcrumbs($this->expiration, $this->container);
    }
}
