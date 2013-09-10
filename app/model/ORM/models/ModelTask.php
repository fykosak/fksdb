<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelTask extends AbstractModelSingle {

    /**
     * (Fully qualified) task name for use in GUI.
     * 
     * @return string
     */
    public function getFQName() {
        return sprintf('%s.%s %s', Utils::toRoman($this->series), $this->label, $this->name_cs); //TODO i18n
    }

}
