<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\Command;

use Propel\Bundle\PropelBundle\Command\AbstractCommand;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use function is_array;
use function realpath;

use const DIRECTORY_SEPARATOR;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractCommandTest extends TestCase
{
    /**
     * @var TestableAbstractCommand
     */
    protected $command;

    public function setUp(): void
    {
        $this->command = new TestableAbstractCommand('testable-command');
    }

    public function testParseDbName()
    {
        $dsn = 'mydsn#dbname=foo';
        self::assertEquals('foo', $this->command->parseDbName($dsn));
    }

    public function testParseDbNameWithoutDbName()
    {
        self::assertNull($this->command->parseDbName('foo'));
    }

    public function testTransformToLogicalName()
    {
        $bundleDir = \realpath(__DIR__ . '/../Fixtures/src/My/SuperBundle');
        $filename = 'Resources' . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'a-schema.xml';

        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MySuperBundle');
        $bundle
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($bundleDir);

        $schema = new \SplFileInfo($bundleDir . \DIRECTORY_SEPARATOR . $filename);
        $expected = '@MySuperBundle/Resources/config/a-schema.xml';
        self::assertEquals($expected, $this->command->transformToLogicalName($schema, $bundle));
    }

    public function testTransformToLogicalNameWithSubDir()
    {
        $bundleDir = \realpath(__DIR__ . '/../Fixtures/src/My/ThirdBundle');
        $filename = 'Resources' . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'propel' . \DIRECTORY_SEPARATOR . 'schema.xml';

        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MyThirdBundle');
        $bundle
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($bundleDir);

        $schema = new \SplFileInfo($bundleDir . \DIRECTORY_SEPARATOR . $filename);
        $expected = '@MyThirdBundle/Resources/config/propel/schema.xml';
        self::assertEquals($expected, $this->command->transformToLogicalName($schema, $bundle));
    }

    public function testGetSchemasFromBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MySuperBundle');
        $bundle
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn(__DIR__ . '/../Fixtures/src/My/SuperBundle');

        $aSchema = \realpath(__DIR__ . '/../Fixtures/src/My/SuperBundle/Resources/config/a-schema.xml');

        // hack to by pass the file locator
        $this->command->setLocateResponse($aSchema);

        $schemas = $this->command->getSchemasFromBundle($bundle);

        self::assertNotNull($schemas);
        self::assertTrue(\is_array($schemas));
        self::assertCount(1, $schemas);
        self::assertArrayHasKey($aSchema, $schemas);
        self::assertSame($bundle, $schemas[$aSchema][0]);
        self::assertEquals(new \SplFileInfo($aSchema), $schemas[$aSchema][1]);
    }

    public function testGetSchemasFromBundleWithNoSchema()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle
            ->expects($this->once())
            ->method('getPath')
            ->willReturn(__DIR__ . '/../Fixtures/src/My/SecondBundle');

        $schemas = $this->command->getSchemasFromBundle($bundle);

        self::assertNotNull($schemas);
        self::assertTrue(\is_array($schemas));
        self::assertCount(0, $schemas);
    }

    public function testGetFinalSchemasWithNoSchemaInBundles()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();

        $bundle
            ->expects($this->once())
            ->method('getPath')
            ->willReturn(__DIR__ . '/../Fixtures/src/My/SecondBundle');

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle]);

        $schemas = $this->command->getFinalSchemas($kernel);

        self::assertNotNull($schemas);
        self::assertTrue(\is_array($schemas));
        self::assertCount(0, $schemas);
    }

    public function testGetFinalSchemas()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();

        $bundle
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MySuperBundle');
        $bundle
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn(__DIR__ . '/../Fixtures/src/My/SuperBundle');

        $aSchema = \realpath(__DIR__ . '/../Fixtures/src/My/SuperBundle/Resources/config/a-schema.xml');

        // hack to by pass the file locator
        $this->command->setLocateResponse($aSchema);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle]);

        $schemas = $this->command->getFinalSchemas($kernel);

        self::assertNotNull($schemas);
        self::assertTrue(\is_array($schemas));
        self::assertCount(1, $schemas);
        self::assertArrayHasKey($aSchema, $schemas);
        self::assertSame($bundle, $schemas[$aSchema][0]);
        self::assertEquals(new \SplFileInfo($aSchema), $schemas[$aSchema][1]);
    }

    public function testGetFinalSchemasWithGivenBundle()
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();

        $bundle
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MySuperBundle');
        $bundle
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn(__DIR__ . '/../Fixtures/src/My/SuperBundle');

        $aSchema = \realpath(__DIR__ . '/../Fixtures/src/My/SuperBundle/Resources/config/a-schema.xml');

        // hack to by pass the file locator
        $this->command->setLocateResponse($aSchema);

        $kernel
            ->expects($this->never())
            ->method('getBundles');

        $schemas = $this->command->getFinalSchemas($kernel, $bundle);

        self::assertNotNull($schemas);
        self::assertTrue(\is_array($schemas));
        self::assertCount(1, $schemas);
        self::assertArrayHasKey($aSchema, $schemas);
        self::assertSame($bundle, $schemas[$aSchema][0]);
        self::assertEquals(new \SplFileInfo($aSchema), $schemas[$aSchema][1]);
    }
}

class TestableAbstractCommand extends AbstractCommand
{
    private $locate;

    public function setLocateResponse($locate)
    {
        $this->locate = $locate;
    }

    public function getContainer()
    {
        return $this;
    }

    public function get($service)
    {
        return $this;
    }

    public function locate($file)
    {
        return $this->locate;
    }

    public function parseDbName($dsn)
    {
        return parent::parseDbName($dsn);
    }

    public function transformToLogicalName(\SplFileInfo $schema, BundleInterface $bundle)
    {
        return parent::transformToLogicalName($schema, $bundle);
    }

    public function getSchemasFromBundle(BundleInterface $bundle)
    {
        return parent::getSchemasFromBundle($bundle);
    }

    public function getFinalSchemas(KernelInterface $kernel, BundleInterface $bundle = null)
    {
        return parent::getFinalSchemas($kernel, $bundle);
    }
}
