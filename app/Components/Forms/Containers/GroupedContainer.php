<?php

namespace FKSDB\Components\Forms\Containers;

use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GroupedContainer extends Container {

    /**
     *
     * @var ControlGroup[]
     */
    private $groups = [];
    private $prefix;

    /**
     * GroupedContainer constructor.
     * @param $prefix
     */
    public function __construct($prefix) {
        parent::__construct();
        $this->monitor(Form::class);
        $this->prefix = $prefix;
    }

    /**
     * @note Copy+paste from Nette\Forms\Form.
     * @param string $caption
     * @param bool $setAsCurrent
     * @return ControlGroup
     */
    public function addGroup($caption, $setAsCurrent = true): ControlGroup {
        $group = new ControlGroup;
        $group->setOption('label', $caption);
        $group->setOption('visual', TRUE);

        if ($setAsCurrent) {
            $this->setCurrentGroup($group);
        }

        if (isset($this->groups[$caption])) {
            return $this->groups[] = $group;
        } else {
            return $this->groups[$caption] = $group;
        }
    }

//    public function getControls($withoutGroup = false) {
//        if ($withoutGroup) {
//            return new ArrayIterator($this->withoutGroup);
//        } else {
//            return parent::getControls();
//        }
//    }
//
//    public function addComponent(IComponent $component, $name, $insertBefore = NULL) {
//        if (!$this->getCurrentGroup()) {
//            $this->withoutGroup[] = $component;
//        }
//        parent::addComponent($component, $name, $insertBefore);
//    }

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        if ($obj instanceof Form) {
            $this->getName();
            foreach ($this->groups as $caption => $myGroup) {
                $formGroup = $obj->addGroup($this->prefix . '-' . $caption, false);
                foreach ($myGroup->getControls() as $control) {
                    $formGroup->add($control);
                }
            }
        }
    }

}
