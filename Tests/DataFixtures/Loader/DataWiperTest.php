<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\DataFixtures\Loader;

use Propel\Bundle\PropelBundle\Tests\DataFixtures\TestCase;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\Book;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookPeer;

/**
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class DataWiperTest extends TestCase
{
    public function testWipesExistingData()
    {
        $author = new BookAuthor();
        $author->setName('Some famous author');

        $book = new Book();
        $book
            ->setName('Armageddon is near')
            ->setBookAuthor($author)
            ->save($this->con)
        ;

        $savedBook = BookPeer::doSelectOne(new \Criteria(), $this->con);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\Book', $savedBook, 'The fixture has been saved correctly.');

        $builder = $this->getMockBuilder('Propel\Bundle\PropelBundle\DataFixtures\Loader\DataWiper');
        $wipeout = $builder
            ->setMethods(['loadMapBuilders'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $dbMap = new \DatabaseMap('default');
        $dbMap->addTableFromMapClass('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\map\BookTableMap');
        $reflection = new \ReflectionObject($wipeout);
        $property = $reflection->getProperty('dbMap');
        $property->setAccessible(true);
        $property->setValue($wipeout, $dbMap);

        $wipeout
            ->expects($this->once())
            ->method('loadMapBuilders')
        ;

        $wipeout->load([], 'default');

        self::assertCount(0, BookPeer::doSelect(new \Criteria(), $this->con));
    }
}
