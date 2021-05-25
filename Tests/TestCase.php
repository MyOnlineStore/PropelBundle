<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use function file_exists;

/**
 * TestCase
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\file_exists($file = __DIR__ . '/../vendor/propel/propel1/runtime/lib/Propel.php')) {
            self::markTestSkipped('Propel is not available.');
        }

        require_once $file;
    }

    public function getContainer()
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug'      => false,
            'kernel.project_dir'   => __DIR__ . '/',
        ]));
    }

    protected function loadPropelQuickBuilder()
    {
        require_once __DIR__ . '/../vendor/propel/propel1/runtime/lib/Propel.php';
        require_once __DIR__ . '/../vendor/propel/propel1/runtime/lib/adapter/DBAdapter.php';
        require_once __DIR__ . '/../vendor/propel/propel1/runtime/lib/adapter/DBSQLite.php';
        require_once __DIR__ . '/../vendor/propel/propel1/runtime/lib/connection/PropelPDO.php';
        require_once __DIR__ . '/../vendor/propel/propel1/generator/lib/util/PropelQuickBuilder.php';
    }
}
