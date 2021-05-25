<?php

namespace Propel\Bundle\PropelBundle\Tests;

use PHPUnit\Framework\TestCase;
use Propel\Bundle\PropelBundle\Util\PropelInflector;

class AutoloadAliasTest extends TestCase
{
    public function testOldNamespaceWorks()
    {
        $inflector = new PropelInflector();

        static::assertInstanceOf('Propel\PropelBundle\Util\PropelInflector', $inflector);
        static::assertInstanceOf('Propel\Bundle\PropelBundle\Util\PropelInflector', $inflector);
    }
}
