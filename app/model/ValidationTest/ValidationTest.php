<?php

abstract class ValidationTest {
    /**
     * @return void
     */
    abstract function run();

    /**
     * @return string
     */
    abstract function getTitle();

    /**
     * @return string
     */
    abstract function getAction();

    /**
     * @return \Nette\Forms\IControl
     */
    abstract function getComponent();

}
