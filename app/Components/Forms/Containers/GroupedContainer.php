<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;

class GroupedContainer extends Container
{
    /**
     * @var ControlGroup[]
     */
    private array $groups = [];
    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->monitor(Form::class, function (Form $form) {
            foreach ($this->groups as $caption => $myGroup) {
                $formGroup = $form->addGroup($this->prefix . '-' . $caption, false);
                foreach ($myGroup->getControls() as $control) {
                    $formGroup->add($control);
                }
            }
        });
        $this->prefix = $prefix;
    }

    /**
     * @note Copy+paste from Nette\Forms\Form.
     */
    public function addGroup(string $caption, bool $setAsCurrent = true): ControlGroup
    {
        $group = new ControlGroup();
        $group->setOption('label', $caption);
        $group->setOption('visual', true);

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
}
