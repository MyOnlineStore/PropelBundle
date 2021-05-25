<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\DependencyInjection;

use Propel\Bundle\PropelBundle\DependencyInjection\PropelExtension;
use Propel\Bundle\PropelBundle\Tests\TestCase;

class PropelExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();
        try {
            $loader->load([[]], $container);
            $this->fail();
        } catch (\Throwable $e) {
            self::assertInstanceOf(
                'InvalidArgumentException',
                $e,
                '->load() throws an \InvalidArgumentException if the Propel path is not set'
            );
        }

        $container = $this->getContainer();
        $loader = new PropelExtension();
        try {
            $loader->load([[
                'path' => '/propel',
                'dbal' => [],
            ]
            ], $container);
            $this->fail();
        } catch (\Throwable $e) {
            self::assertInstanceOf(
                'InvalidArgumentException',
                $e,
                '->load() throws an \InvalidArgumentException if the Phing path is not set.'
            );
        }

        $container = $this->getContainer();
        $loader = new PropelExtension();
        $loader->load([[
            'path'       => '/propel',
            'phing_path' => '/phing',
            'dbal'       => []
        ]
        ], $container);
        self::assertEquals('/propel',  $container->getParameter('propel.path'), '->load() requires the Propel path');
        self::assertEquals('/phing',   $container->getParameter('propel.phing_path'), '->load() requires the Phing path');
    }

    public function testDbalLoad()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $loader->load([[
            'path'       => '/propel',
            'phing_path' => '/phing',
            'dbal' => [
                'default_connection' => 'foo',
            ]
        ]
        ], $container);
        self::assertEquals('foo', $container->getParameter('propel.dbal.default_connection'), '->dbalLoad() overrides existing configuration options');

        $container = $this->getContainer();
        $loader = new PropelExtension();

        $loader->load([[
            'path'          => '/propel',
            'phing_path'    => '/phing',
            'dbal'          => [
                'password' => 'foo',
            ]
        ]
        ], $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];

        self::assertEquals('foo', $config['datasources']['default']['connection']['password']);
        self::assertEquals('root', $config['datasources']['default']['connection']['user']);

        $loader->load([[
            'path' => '/propel',
            'dbal' => [
                'user' => 'foo',
            ]
        ]
        ], $container);
        self::assertEquals('foo', $config['datasources']['default']['connection']['password']);
        self::assertEquals('root', $config['datasources']['default']['connection']['user']);

    }

    public function testDbalLoadCascade()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/propel',
        ];

        $config_prod = ['dbal' => [
            'user'      => 'toto',
            'password'  => 'titi',
            'dsn'       => 'foobar',
            'driver'    => 'my_driver',
            'options'   => ['o1', 'o2']
        ]
        ];

        $config_dev = ['dbal' => [
            'user'      => 'toto_dev',
            'password'  => 'titi_dev',
            'dsn'       => 'foobar',
        ]
        ];

        $configs = [$config_base, $config_prod, $config_dev];

        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];
        self::assertEquals('toto_dev',  $config['datasources']['default']['connection']['user']);
        self::assertEquals('titi_dev',  $config['datasources']['default']['connection']['password']);
        self::assertEquals('foobar',    $config['datasources']['default']['connection']['dsn']);
        self::assertEquals('my_driver', $config['datasources']['default']['adapter']);
        self::assertEquals('o1',        $config['datasources']['default']['connection']['options'][0]);
        self::assertEquals('o2',        $config['datasources']['default']['connection']['options'][1]);
    }

    public function testDbalLoadMultipleConnections()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/phing',
        ];

        $config_mysql = [
            'user'      => 'mysql_usr',
            'password'  => 'mysql_pwd',
            'dsn'       => 'mysql_dsn',
            'driver'    => 'mysql',
        ];

        $config_sqlite = [
            'user'      => 'sqlite_usr',
            'password'  => 'sqlite_pwd',
            'dsn'       => 'sqlite_dsn',
            'driver'    => 'sqlite',
        ];

        $config_connections = [
            'default_connection' => 'sqlite',
            'connections' => ['mysql' => $config_mysql, 'sqlite' => $config_sqlite,
            ]
        ];

        $configs = [$config_base, ['dbal' => $config_connections]];

        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];
        self::assertEquals('sqlite', $container->getParameter('propel.dbal.default_connection'));
        self::assertEquals('sqlite_usr',  $config['datasources']['sqlite']['connection']['user']);
        self::assertEquals('sqlite_pwd',  $config['datasources']['sqlite']['connection']['password']);
        self::assertEquals('sqlite_dsn',  $config['datasources']['sqlite']['connection']['dsn']);
        self::assertEquals('sqlite',      $config['datasources']['sqlite']['adapter']);

        $config_connections = [
            'default_connection' => 'mysql',
            'connections' => ['mysql' => $config_mysql, 'sqlite' => $config_sqlite,
            ]
        ];

        $configs = [$config_base, ['dbal' => $config_connections]];

        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];
        self::assertEquals('mysql', $container->getParameter('propel.dbal.default_connection'));
        self::assertEquals('mysql_usr',  $config['datasources']['mysql']['connection']['user']);
        self::assertEquals('mysql_pwd',  $config['datasources']['mysql']['connection']['password']);
        self::assertEquals('mysql_dsn',  $config['datasources']['mysql']['connection']['dsn']);
        self::assertEquals('mysql',      $config['datasources']['mysql']['adapter']);
    }

    public function testDbalWithMultipleConnectionsAndSettings()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/phing',
        ];

        $config_mysql = [
            'user'      => 'mysql_usr',
            'password'  => 'mysql_pwd',
            'dsn'       => 'mysql_dsn',
            'driver'    => 'mysql',
            'settings'  => [
                'charset' => ['value' => 'UTF8'],
            ],
        ];

        $config_connections = [
            'default_connection'    => 'mysql',
            'connections'           => [
                'mysql' => $config_mysql,
            ]
        ];

        $configs = [$config_base, ['dbal' => $config_connections]];

        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];
        self::assertEquals('mysql', $container->getParameter('propel.dbal.default_connection'));
        self::assertEquals('mysql_usr',  $config['datasources']['mysql']['connection']['user']);
        self::assertEquals('mysql_pwd',  $config['datasources']['mysql']['connection']['password']);
        self::assertEquals('mysql_dsn',  $config['datasources']['mysql']['connection']['dsn']);

        self::assertArrayHasKey('settings', $config['datasources']['mysql']['connection']);
        self::assertArrayHasKey('charset',  $config['datasources']['mysql']['connection']['settings']);
        self::assertArrayHasKey('value',    $config['datasources']['mysql']['connection']['settings']['charset']);
        self::assertEquals('UTF8', $config['datasources']['mysql']['connection']['settings']['charset']['value']);
    }

    public function testDbalWithSettings()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/phing',
        ];

        $config_mysql = [
            'user'      => 'mysql_usr',
            'password'  => 'mysql_pwd',
            'dsn'       => 'mysql_dsn',
            'driver'    => 'mysql',
            'settings'  => [
                'charset' => ['value' => 'UTF8'],
                'queries' => ['query' => 'SET NAMES UTF8']
            ],
        ];

        $configs = [$config_base, ['dbal' => $config_mysql]];

        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];

        self::assertArrayHasKey('settings', $config['datasources']['default']['connection']);
        self::assertArrayHasKey('charset',  $config['datasources']['default']['connection']['settings']);
        self::assertArrayHasKey('value',    $config['datasources']['default']['connection']['settings']['charset']);
        self::assertEquals('UTF8', $config['datasources']['default']['connection']['settings']['charset']['value']);

        self::assertArrayHasKey('settings', $config['datasources']['default']['connection']);
        self::assertArrayHasKey('queries',  $config['datasources']['default']['connection']['settings']);
        self::assertArrayHasKey('query',    $config['datasources']['default']['connection']['settings']['queries']);
        self::assertEquals('SET NAMES UTF8', $config['datasources']['default']['connection']['settings']['queries']['query']);
    }

    public function testDbalWithSlaves()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/phing',
        ];

        $config_mysql = [
            'user'      => 'mysql_usr',
            'password'  => 'mysql_pwd',
            'dsn'       => 'mysql_dsn',
            'driver'    => 'mysql',
            'slaves'  => [
                'mysql_slave1' => [
                    'user' => 'mysql_usrs1',
                    'password' => 'mysql_pwds1',
                    'dsn' => 'mysql_dsns1',
                ],
                'mysql_slave2' => [
                    'user' => 'mysql_usrs2',
                    'password' => 'mysql_pwds2',
                    'dsn' => 'mysql_dsns2',
                ],
            ],
        ];

        $configs = [$config_base, [
            'dbal' => [
                'default_connection' => 'master',
                'connections'        => ['master' => $config_mysql]
            ]
        ]
        ];
        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];

        self::assertArrayHasKey('slaves', $config['datasources']['master']);
        self::assertArrayHasKey('connection', $config['datasources']['master']['slaves']);
        self::assertArrayHasKey('mysql_slave1', $config['datasources']['master']['slaves']['connection']);
        self::assertArrayHasKey('user', $config['datasources']['master']['slaves']['connection']['mysql_slave1']);
        self::assertArrayHasKey('password', $config['datasources']['master']['slaves']['connection']['mysql_slave1']);
        self::assertArrayHasKey('dsn', $config['datasources']['master']['slaves']['connection']['mysql_slave1']);
        self::assertArrayHasKey('mysql_slave2', $config['datasources']['master']['slaves']['connection']);
        self::assertArrayHasKey('user', $config['datasources']['master']['slaves']['connection']['mysql_slave2']);
        self::assertArrayHasKey('password', $config['datasources']['master']['slaves']['connection']['mysql_slave2']);
        self::assertArrayHasKey('dsn', $config['datasources']['master']['slaves']['connection']['mysql_slave2']);

        self::assertEquals('mysql_usrs1', $config['datasources']['master']['slaves']['connection']['mysql_slave1']['user']);
        self::assertEquals('mysql_pwds1', $config['datasources']['master']['slaves']['connection']['mysql_slave1']['password']);
        self::assertEquals('mysql_dsns1', $config['datasources']['master']['slaves']['connection']['mysql_slave1']['dsn']);

        self::assertEquals('mysql_usrs2', $config['datasources']['master']['slaves']['connection']['mysql_slave2']['user']);
        self::assertEquals('mysql_pwds2', $config['datasources']['master']['slaves']['connection']['mysql_slave2']['password']);
        self::assertEquals('mysql_dsns2', $config['datasources']['master']['slaves']['connection']['mysql_slave2']['dsn']);
    }

    public function testDbalWithNoSlaves()
    {
        $container = $this->getContainer();
        $loader = new PropelExtension();

        $config_base = [
            'path'       => '/propel',
            'phing_path' => '/phing',
        ];

        $config_mysql = [
            'user'      => 'mysql_usr',
            'password'  => 'mysql_pwd',
            'dsn'       => 'mysql_dsn',
            'driver'    => 'mysql'
        ];

        $configs = [$config_base, ['dbal' => $config_mysql]];
        $loader->load($configs, $container);

        $arguments = $container->getDefinition('propel.configuration')->getArguments();
        $config = $arguments[0];

        self::assertArrayNotHasKey('slaves', $config['datasources']['default']);
    }
}
