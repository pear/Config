<?php
/**
 *
 */

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass() : void
    {
    }

    public function setUp() : void
    {
        echo( get_called_class() . '::' . $this->getName() ."\n" );
    }

    /**/
}
