<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Bundle\PropelBundle\Tests\Form;

use Propel\Bundle\PropelBundle\Form\PropelTypeGuesser;
use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Propel\Bundle\PropelBundle\Tests\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;

class PropelTypeGuesserTest extends TestCase
{
    const CLASS_NAME = 'Propel\Bundle\PropelBundle\Tests\Fixtures\Item';
    const UNKNOWN_CLASS_NAME = 'Propel\Bundle\PropelBundle\Tests\Fixtures\UnknownItem';

    private $guesser;

    protected function setUp(): void
    {
        $this->guesser = new PropelTypeGuesser();
    }

    protected function tearDown(): void
    {
        $this->guesser = null;
    }

    public function testGuessMaxLengthWithText()
    {
        $value = $this->guesser->guessMaxLength(self::CLASS_NAME, 'value');

        self::assertNotNull($value);
        self::assertEquals(255, $value->getValue());
    }

    public function testGuessMaxLengthWithFloat()
    {
        $value = $this->guesser->guessMaxLength(self::CLASS_NAME, 'price');

        self::assertNotNull($value);
        self::assertNull($value->getValue());
    }

    public function testGuessMinLengthWithText()
    {
        $value = $this->guesser->guessPattern(self::CLASS_NAME, 'value');

        self::assertNull($value);
    }

    public function testGuessMinLengthWithFloat()
    {
        $value = $this->guesser->guessPattern(self::CLASS_NAME, 'price');

        self::assertNotNull($value);
        self::assertNull($value->getValue());
    }

    public function testGuessRequired()
    {
        $value = $this->guesser->guessRequired(self::CLASS_NAME, 'id');

        self::assertNotNull($value);
        self::assertTrue($value->getValue());
    }

    public function testGuessRequiredWithNullableColumn()
    {
        $value = $this->guesser->guessRequired(self::CLASS_NAME, 'value');

        self::assertNotNull($value);
        self::assertFalse($value->getValue());
    }

    public function testGuessTypeWithoutTable()
    {
        $value = $this->guesser->guessType(self::UNKNOWN_CLASS_NAME, 'property');

        self::assertNotNull($value);
        self::assertEquals(TextType::class, $value->getType());
        self::assertEquals(Guess::LOW_CONFIDENCE, $value->getConfidence());
    }

    public function testGuessTypeWithoutColumn()
    {
        $value = $this->guesser->guessType(self::CLASS_NAME, 'property');

        self::assertNotNull($value);
        self::assertEquals(TextType::class, $value->getType());
        self::assertEquals(Guess::LOW_CONFIDENCE, $value->getConfidence());
    }

    /**
     * @dataProvider dataProviderForGuessType
     */
    public function testGuessType($property, $type, $confidence, $multiple = null)
    {
        $value = $this->guesser->guessType(self::CLASS_NAME, $property);

        self::assertNotNull($value);
        self::assertEquals($type, $value->getType());
        self::assertEquals($confidence, $value->getConfidence());

        if (ModelType::class === $type) {
            $options = $value->getOptions();

            self::assertSame($multiple, $options['multiple']);
        }
    }

    public static function dataProviderForGuessType(): \Iterator
    {
        yield ['is_active',  CheckboxType::class, Guess::HIGH_CONFIDENCE];
        yield ['enabled',    CheckboxType::class, Guess::HIGH_CONFIDENCE];
        yield ['id',         IntegerType::class,  Guess::MEDIUM_CONFIDENCE];
        yield ['value',      TextType::class,     Guess::MEDIUM_CONFIDENCE];
        yield ['price',      NumberType::class,   Guess::MEDIUM_CONFIDENCE];
        yield ['updated_at', DateTimeType::class, Guess::HIGH_CONFIDENCE];
        yield ['isActive',   CheckboxType::class, Guess::HIGH_CONFIDENCE];
        yield ['updatedAt',  DateTimeType::class, Guess::HIGH_CONFIDENCE];
        yield ['Authors',    ModelType::class,    Guess::HIGH_CONFIDENCE,     true];
        yield ['Resellers',  ModelType::class,    Guess::HIGH_CONFIDENCE,     true];
        yield ['MainAuthor', ModelType::class,    Guess::HIGH_CONFIDENCE,     false];
    }
}
