<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Bundle\PropelBundle\Tests\Form\DataTransformer;

use Propel\Bundle\PropelBundle\Form\DataTransformer\CollectionToArrayTransformer;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function is_array;

class CollectionToArrayTransformerTest extends TestCase
{
    private $transformer;

    protected function setUp(): void
    {
        $this->transformer = new CollectionToArrayTransformer();
    }

    public function testTransform()
    {
        $result = $this->transformer->transform(new \PropelObjectCollection());

        self::assertTrue(\is_array($result));
        self::assertCount(0, $result);
    }

    public function testTransformWithNull()
    {
        $result = $this->transformer->transform(null);

        self::assertTrue(\is_array($result));
        self::assertCount(0, $result);
    }

    public function testTransformThrowsExceptionIfNotPropelObjectCollection()
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->transform(new DummyObject());
    }

    public function testTransformWithData()
    {
        $coll = new \PropelObjectCollection();
        $coll->setData(['foo', 'bar']);

        $result = $this->transformer->transform($coll);

        self::assertTrue(\is_array($result));
        self::assertCount(2, $result);
        self::assertEquals('foo', $result[0]);
        self::assertEquals('bar', $result[1]);
    }

    public function testReverseTransformWithNull()
    {
        $result = $this->transformer->reverseTransform(null);

        self::assertInstanceOf('\PropelObjectCollection', $result);
        self::assertCount(0, $result->getData());
    }

    public function testReverseTransformWithEmptyString()
    {
        $result = $this->transformer->reverseTransform('');

        self::assertInstanceOf('\PropelObjectCollection', $result);
        self::assertCount(0, $result->getData());
    }

    public function testReverseTransformThrowsExceptionIfNotArray()
    {
        $this->expectException(TransformationFailedException::class);
        $this->transformer->reverseTransform(new DummyObject());
    }

    public function testReverseTransformWithData()
    {
        $inputData = ['foo', 'bar'];

        $result = $this->transformer->reverseTransform($inputData);
        $data = $result->getData();

        self::assertInstanceOf('\PropelObjectCollection', $result);

        self::assertTrue(\is_array($data));
        self::assertCount(2, $data);
        self::assertEquals('foo', $data[0]);
        self::assertEquals('bar', $data[1]);
        self::assertsame($inputData, $data);
    }
}

class DummyObject
{
}
