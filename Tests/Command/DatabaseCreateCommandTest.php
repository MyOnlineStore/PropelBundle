<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Command;

use Propel\Bundle\PropelBundle\Command\DatabaseCreateCommand;
use Propel\Bundle\PropelBundle\Tests\TestCase;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DatabaseCreateCommandTest extends TestCase
{
    /** @var TestableDatabaseCreateCommand */
    protected $command;

    public function setUp(): void
    {
        $this->command = new TestableDatabaseCreateCommand();
    }

    public function tearDown(): void
    {
        $this->command = null;
    }

    /**
     * @dataProvider dataTemporaryConfiguration
     */
    public function testTemporaryConfiguration($name, $config, $expectedDsn)
    {
        $datasource = $this->command->getTemporaryConfiguration($name, $config);

        self::assertArrayHasKey('datasources', $datasource);
        self::assertArrayHasKey($name, $datasource['datasources']);
        self::assertArrayHasKey('connection', $datasource['datasources'][$name]);
        self::assertArrayHasKey('dsn', $datasource['datasources'][$name]['connection']);
        self::assertEquals($expectedDsn, $datasource['datasources'][$name]['connection']['dsn']);
    }

    public function dataTemporaryConfiguration()
    {
        return [
            [
                'dbname',
                ['connection' => ['dsn' => 'mydsn:host=localhost;dbname=test_db;']],
                'mydsn:host=localhost;'
            ],
            [
                'dbname_first',
                ['connection' => ['dsn' => 'mydsn:dbname=test_db;host=localhost']],
                'mydsn:host=localhost'
            ],
            [
                'dbname_no_semicolon',
                ['connection' => ['dsn' => 'mydsn:host=localhost;dbname=test_db']],
                'mydsn:host=localhost;'
            ],
            [
                'no_dbname',
                ['connection' => ['dsn' => 'mydsn:host=localhost;']],
                'mydsn:host=localhost;'
            ],
        ];
    }
}

class TestableDatabaseCreateCommand extends DatabaseCreateCommand
{
    public function getTemporaryConfiguration($name, $config)
    {
        return parent::getTemporaryConfiguration($name, $config);
    }
}
