<?php

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use Nette\Http\Session;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\Components\Controls\BaseControl;
use FKSDB\UI\Title;

/**
 * Class Chooser
 * @package FKSDB\Components\Controls\Choosers
 */
abstract class Chooser extends BaseControl {

    protected function beforeRender() {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render() {
        $this->beforeRender();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chooser.latte');
        $this->template->render();
    }

    /**
     * @return Title
     */
    abstract protected function getTitle(): Title;

    /**
     * @return array|iterable
     */
    abstract protected function getItems();

    /**
     * @param $item
     * @return bool
     */
    abstract public function isItemActive($item): bool;

    /**
     * @param $item
     * @return string
     */
    abstract public function getItemLabel($item): string;

    /**
     * @param $item
     * @return string
     */
    abstract public function getItemLink($item): string;

    const SESSION_PREFIX = 'contestPreset';

    /**
     * @var ServiceContest
     */
    protected $serviceContest;
    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $role = null;

    /**
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param \stdClass $params
     * @return bool
     */
    abstract function syncRedirect(&$params);


    /**
     * @return ModelLogin
     */
    protected function getLogin() {
        /** @var \OrgModule\BasePresenter|\PublicModule\BasePresenter $presenter */
        $presenter = $this->getPresenter();
        /**@var ModelLogin $model */
        $model = $presenter->getUser()->getIdentity();
        return $model;
    }

    /**
     * @param \stdClass $params
     * @return void
     */
    abstract protected function init($params);
}
