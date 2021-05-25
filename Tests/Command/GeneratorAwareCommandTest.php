<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Command;

use Propel\Bundle\PropelBundle\Command\GeneratorAwareCommand;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function count;
use function is_array;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GeneratorAwareCommandTest extends TestCase
{
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->container->setParameter('propel.path',  __DIR__ . '/../../vendor/propel/propel1');
    }

    public function testGetDatabasesFromSchema()
    {
        $command = new GeneratorAwareCommandTestable('testable-command');
        $command->setContainer($this->container);
        $databases = $command->getDatabasesFromSchema(new \SplFileInfo(__DIR__ . '/../Fixtures/schema.xml'));

        self::assertTrue(\is_array($databases));

        foreach ($databases as $database) {
            self::assertInstanceOf('\Database', $database);
        }

        $bookstore = $databases[0];
        self::assertEquals(1, \count($bookstore->getTables()));

        foreach ($bookstore->getTables() as $table) {
            self::assertInstanceOf('\Table', $table);
        }
    }
}

class GeneratorAwareCommandTestable extends GeneratorAwareCommand
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    public function getDatabasesFromSchema(\SplFileInfo $file, \XmlToAppData $transformer = null)
    {
        $this->loadPropelGenerator();

        return parent::getDatabasesFromSchema($file, $transformer);
    }
}
