<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Util;

use Propel\Bundle\PropelBundle\Tests\TestCase;
use Propel\Bundle\PropelBundle\Util\PropelInflector;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class PropelInflectorTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestCamelize
     */
    public function testCamelize($word, $expected)
    {
        self::assertEquals($expected, PropelInflector::camelize($word));
    }

    public static function dataProviderForTestCamelize(): \Iterator
    {
        yield ['', ''];
        yield [null, null];
        yield ['foo', 'foo'];
        yield ['Foo', 'foo'];
        yield ['fooBar', 'fooBar'];
        yield ['FooBar', 'fooBar'];
        yield ['Foo_bar', 'fooBar'];
        yield ['Foo_Bar', 'fooBar'];
        yield ['Foo Bar', 'fooBar'];
        yield ['Foo bar Baz', 'fooBarBaz'];
        yield ['Foo_Bar_Baz', 'fooBarBaz'];
        yield ['foo_bar_baz', 'fooBarBaz'];
    }
}
