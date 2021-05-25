<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Tests\DataFixtures\Loader;

use Faker\Factory;
use Propel\Bundle\PropelBundle\DataFixtures\Loader\YamlDataLoader;
use Propel\Bundle\PropelBundle\Tests\DataFixtures\TestCase;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlBookWithObjectQuery;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlDelegateOnPrimaryKeyAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedM2MRelationshipNobelizedAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedRelationshipBookPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBookAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBookPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBookAuthorPeer;
use Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBookPeer;

use function class_exists;
use function strtolower;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class YamlDataLoaderTest extends TestCase
{
    public function testYamlLoadOneToMany()
    {
        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor:
    BookAuthor_1:
        id: '1'
        name: 'A famous one'
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\Book:
    Book_1:
        id: '1'
        name: 'An important one'
        author_id: BookAuthor_1

YAML;
        $filename = $this->getTempFile($fixtures);

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $books);

        $book = $books[0];
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor', $book->getBookAuthor());
    }

    public function testYamlLoadManyToMany()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">
    <table name="table_book" phpName="YamlManyToManyBook">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_author" phpName="YamlManyToManyAuthor">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_book_author" phpName="YamlManyToManyBookAuthor" isCrossRef="true">
        <column name="book_id" type="integer" required="true" primaryKey="true" />
        <column name="author_id" type="integer" required="true" primaryKey="true" />

        <foreign-key foreignTable="table_book" phpName="Book" onDelete="CASCADE" onUpdate="CASCADE">
            <reference local="book_id" foreign="id" />
        </foreign-key>
        <foreign-key foreignTable="table_author" phpName="Author" onDelete="CASCADE" onUpdate="CASCADE">
            <reference local="author_id" foreign="id" />
        </foreign-key>
    </table>
</database>
XML;

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBook:
    Book_1:
        id: 1
        name: 'An important one'
    Book_2:
        id: 2
        name: 'Les misérables'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyAuthor:
    Author_1:
        id: 1
        name: 'A famous one'
    Author_2:
        id: 2
        name: 'Victor Hugo'
        table_book_authors: [ Book_2 ]

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBookAuthor:
    BookAuthor_1:
        book_id: Book_1
        author_id: Author_1
YAML;

        $filename = $this->getTempFile($fixtures);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = YamlManyToManyBookPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $books);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBook', $books[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBook', $books[1]);

        $authors = YamlManyToManyAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $authors);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyAuthor', $authors[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyAuthor', $authors[1]);

        $bookAuthors = YamlManyToManyBookAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $bookAuthors);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBookAuthor', $bookAuthors[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyBookAuthor', $bookAuthors[1]);

        self::assertEquals('Victor Hugo', $authors[1]->getName());
        self::assertTrue($authors[1]->getBooks()->contains($books[1]));
        self::assertEquals('Les misérables', $authors[1]->getBooks()->get(0)->getName());
    }

    public function testYamlLoadManyToManyMultipleFiles()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">
    <table name="table_book_multiple" phpName="YamlManyToManyMultipleFilesBook">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_author_multiple" phpName="YamlManyToManyMultipleFilesAuthor">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_book_author_multiple" phpName="YamlManyToManyMultipleFilesBookAuthor" isCrossRef="true">
        <column name="book_id" type="integer" required="true" primaryKey="true" />
        <column name="author_id" type="integer" required="true" primaryKey="true" />

        <foreign-key foreignTable="table_book_multiple" phpName="Book" onDelete="CASCADE" onUpdate="CASCADE">
            <reference local="book_id" foreign="id" />
        </foreign-key>
        <foreign-key foreignTable="table_author_multiple" phpName="Author" onDelete="CASCADE" onUpdate="CASCADE">
            <reference local="author_id" foreign="id" />
        </foreign-key>
    </table>
</database>
XML;

        $fixtures1 = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBook:
    Book_2:
        id: 2
        name: 'Les misérables'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesAuthor:
    Author_1:
        id: 1
        name: 'A famous one'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBookAuthor:
    BookAuthor_1:
        book_id: Book_1
        author_id: Author_1
YAML;

        $fixtures2 = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBook:
    Book_1:
        id: 1
        name: 'An important one'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesAuthor:
    Author_2:
        id: 2
        name: 'Victor Hugo'
        table_book_author_multiples: [ Book_2 ]
YAML;

        $filename1 = $this->getTempFile($fixtures1);
        $filename2 = $this->getTempFile($fixtures2);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename1, $filename2], 'default');

        $books = YamlManyToManyMultipleFilesBookPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $books);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBook', $books[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBook', $books[1]);

        $authors = YamlManyToManyMultipleFilesAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $authors);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesAuthor', $authors[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesAuthor', $authors[1]);

        $bookAuthors = YamlManyToManyMultipleFilesBookAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(2, $bookAuthors);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBookAuthor', $bookAuthors[0]);
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlManyToManyMultipleFilesBookAuthor', $bookAuthors[1]);

        self::assertEquals('Victor Hugo', $authors[1]->getName());
        self::assertTrue($authors[1]->getBooks()->contains($books[1]));
        self::assertEquals('Les misérables', $authors[1]->getBooks()->get(0)->getName());
    }

    public function testLoadSelfReferencing()
    {
        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor:
    BookAuthor_1:
        id: '1'
        name: 'to be announced'
    BookAuthor_2:
        id: BookAuthor_1
        name: 'A famous one'

YAML;
        $filename = $this->getTempFile($fixtures);

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(0, $books);

        $authors = BookAuthorPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $authors);

        $author = $authors[0];
        self::assertEquals('A famous one', $author->getName());
    }

    public function testLoaderWithPhp()
    {
        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor:
    BookAuthor_1:
        id: '1'
        name: <?php echo "to be announced"; ?>

YAML;
        $filename = $this->getTempFile($fixtures);

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(0, $books);

        $authors = BookAuthorPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $authors);

        $author = $authors[0];
        self::assertEquals('to be announced', $author->getName());
    }

    public function testLoadWithoutFaker()
    {
        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\BookAuthor:
    BookAuthor_1:
        id: '1'
        name: <?php echo \$faker('word'); ?>

YAML;
        $filename = $this->getTempFile($fixtures);

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(0, $books);

        $authors = BookAuthorPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $authors);

        $author = $authors[0];
        self::assertEquals('word', $author->getName());
    }

    public function testLoadWithFaker()
    {
        if (!\class_exists('Faker\Factory')) {
            $this->markTestSkipped('Faker is mandatory');
        }

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\Book:
    Book_1:
        id: '1'
        name: <?php \$faker('word'); ?>
        description: <?php \$faker('sentence'); ?>

YAML;
        $filename  = $this->getTempFile($fixtures);
        $container = $this->getContainer();
        $container->set('faker.generator', Factory::create());

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader', $container);
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $books);

        $book = $books[0];
        self::assertNotNull($book->getName());
        self::assertNotEquals('null', \strtolower($book->getName()));
        self::assertRegexp('#[a-z]+#', $book->getName());
        self::assertNotNull($book->getDescription());
        self::assertNotEquals('null', \strtolower($book->getDescription()));
        self::assertRegexp('#[\w ]+#', $book->getDescription());
    }

    public function testLoadWithFakerDateTime()
    {
        if (!\class_exists('Faker\Factory')) {
            $this->markTestSkipped('Faker is mandatory');
        }

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\Book:
    Book_1:
        id: '1'
        name: <?php \$faker('dateTimeThisMonth'); ?>
        description: <?php \$faker('sentence'); ?>

YAML;
        $filename  = $this->getTempFile($fixtures);
        $container = $this->getContainer();
        $container->set('faker.generator', Factory::create());

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader', $container);
        $loader->load([$filename], 'default');

        $books = BookPeer::doSelect(new \Criteria(), $this->con);
        self::assertCount(1, $books);

        $book = $books[0];
        self::assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $book->getName());

        $datetime = new \DateTime($book->getName());
        self::assertInstanceOf('DateTime', $datetime);
    }

    public function testLoadWithInheritedRelationship()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">

    <table name="table_book_inherited_relationship" phpName="YamlInheritedRelationshipBook">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" />
        <column name="author_id" type="integer" required="true" />
        <foreign-key foreignTable="table_author_inherited_relationship" phpName="Author">
            <reference local="author_id" foreign="id" />
        </foreign-key>
    </table>

    <table name="table_author_inherited_relationship" phpName="YamlInheritedRelationshipAuthor">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_nobelized_author_inherited_relationship" phpName="YamlInheritedRelationshipNobelizedAuthor">
        <column name="nobel_year" type="integer" />
        <behavior name="concrete_inheritance">
            <parameter name="extends" value="table_author_inherited_relationship" />
        </behavior>
    </table>

</database>
XML;

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedRelationshipNobelizedAuthor:
    NobelizedAuthor_1:
        nobel_year: 2012

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedRelationshipBook:
    Book_1:
        name: 'Supplice du santal'
        author_id: NobelizedAuthor_1
YAML;

        $filename = $this->getTempFile($fixtures);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $books = YamlInheritedRelationshipBookPeer::doSelect(new \Criteria(), $con);
        self::assertCount(1, $books);

        $book = $books[0];
        $author = $book->getAuthor();
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedRelationshipAuthor', $author);
    }

    public function testLoadWithInheritedManyToManyRelationship()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">

    <table name="table_book_inherited_m2m_relationship" phpName="YamlInheritedM2MRelationshipBook">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_history_book_inherited_m2m_relationship" phpName="YamlInheritedM2MRelationshipHistoryBook">
        <behavior name="concrete_inheritance">
            <parameter name="extends" value="table_book_inherited_m2m_relationship" />
        </behavior>
    </table>

    <table name="table_author_inherited_m2m_relationship" phpName="YamlInheritedM2MRelationshipAuthor">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="table_nobelized_author_inherited_m2m_relationship" phpName="YamlInheritedM2MRelationshipNobelizedAuthor">
        <column name="nobel_year" type="integer" />
        <behavior name="concrete_inheritance">
            <parameter name="extends" value="table_author_inherited_m2m_relationship" />
        </behavior>
    </table>

    <table name="table_book_author_inherited_m2m_relationship" phpName="YamlInheritedM2MRelationshipBookAuthor" isCrossRef="true">
        <column name="author_id" type="integer" primaryKey="true" />
        <column name="book_id" type="integer" primaryKey="true" />
        <foreign-key foreignTable="table_author_inherited_m2m_relationship" phpName="Author">
            <reference local="author_id" foreign="id" />
        </foreign-key>
        <foreign-key foreignTable="table_book_inherited_m2m_relationship" phpName="Book">
            <reference local="book_id" foreign="id" />
        </foreign-key>
    </table>

</database>
XML;

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedM2MRelationshipBook:
    Book_1:
        name: 'Supplice du santal'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedM2MRelationshipHistoryBook:
    Book_2:
        name: 'Qiushui'

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlInheritedM2MRelationshipNobelizedAuthor:
    NobelizedAuthor_1:
        nobel_year: 2012
        table_book_author_inherited_m2m_relationships: [Book_1, Book_2]

YAML;

        $filename = $this->getTempFile($fixtures);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $authors = YamlInheritedM2MRelationshipNobelizedAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(1, $authors);

        $author = $authors[0];
        $books = $author->getBooks();
        self::assertCount(2, $books);
    }

    public function testLoadArrayToObjectType()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">
    <table name="table_book_with_object" phpName="YamlBookWithObject">
        <column name="id" type="integer" primaryKey="true" />
        <column name="name" type="varchar" size="255" />
        <column name="options" type="object" />
    </table>
</database>
XML;
        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlBookWithObject:
    book1:
        name: my book
        options: {opt1: 2012, opt2: 140, inner: {subOpt: 123}}
YAML;

        $filename = $this->getTempFile($fixtures);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $book = YamlBookWithObjectQuery::create(null, $con)->findOne();

        self::assertInstanceOf('\Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlBookWithObject', $book);
        self::assertEquals(['opt1' => 2012, 'opt2' => 140, 'inner' => ['subOpt' => 123]], $book->getOptions());
    }

    public function testLoadDelegatedOnPrimaryKey()
    {
        $schema = <<<XML
<database name="default" package="vendor.bundles.Propel.PropelBundle.Tests.Fixtures.DataFixtures.Loader" namespace="Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader" defaultIdMethod="native">
    <table name="yaml_delegate_on_primary_key_person" phpName="YamlDelegateOnPrimaryKeyPerson">
        <column name="id" type="integer" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" />
    </table>

    <table name="yaml_delegate_on_primary_key_author" phpName="YamlDelegateOnPrimaryKeyAuthor">
        <column name="id" type="integer" primaryKey="true" autoIncrement="false" />
        <column name="count_books" type="integer" defaultValue="0" required="true" />

        <behavior name="delegate">
            <parameter name="to" value="yaml_delegate_on_primary_key_person" />
        </behavior>

        <foreign-key foreignTable="yaml_delegate_on_primary_key_person" onDelete="RESTRICT" onUpdate="CASCADE">
            <reference local="id" foreign="id" />
        </foreign-key>
    </table>
</database>
XML;

        $fixtures = <<<YAML
Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlDelegateOnPrimaryKeyPerson:
    yaml_delegate_on_primary_key_person_1:
        name: "Some Persons Name"

Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlDelegateOnPrimaryKeyAuthor:
    yaml_delegate_on_primary_key_author_1:
        id: yaml_delegate_on_primary_key_person_1
        count_books: 7
YAML;

        $filename = $this->getTempFile($fixtures);

        $builder = new \PropelQuickBuilder();
        $builder->setSchema($schema);
        $con = $builder->build();

        $loader = new YamlDataLoader(__DIR__ . '/../../Fixtures/DataFixtures/Loader');
        $loader->load([$filename], 'default');

        $authors = YamlDelegateOnPrimaryKeyAuthorPeer::doSelect(new \Criteria(), $con);
        self::assertCount(1, $authors);

        $author = $authors[0];
        $person = $author->getYamlDelegateOnPrimaryKeyPerson();
        self::assertInstanceOf('Propel\Bundle\PropelBundle\Tests\Fixtures\DataFixtures\Loader\YamlDelegateOnPrimaryKeyPerson', $person);
    }
}
