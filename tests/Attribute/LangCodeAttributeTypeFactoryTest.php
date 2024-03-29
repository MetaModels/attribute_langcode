<?php

/**
 * This file is part of MetaModels/attribute_langcode.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_langcode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeLangCodeBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\AttributeLangCodeBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeLangCodeBundle\Attribute\LangCode;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the attribute factory.
 *
 * @covers \MetaModels\AttributeLangCodeBundle\Attribute\AttributeTypeFactory
 */
class LangCodeAttributeTypeFactoryTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string $tableName        The table name.
     *
     * @param string $language         The language.
     *
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($tableName, $language, $fallbackLanguage)
    {
        $metaModel = $this->getMockBuilder(IMetaModel::class)->getMock();

        $metaModel
            ->expects(self::any())
            ->method('getTableName')
            ->willReturn($tableName);

        $metaModel
            ->expects(self::any())
            ->method('getActiveLanguage')
            ->willReturn($language);

        $metaModel
            ->expects(self::any())
            ->method('getFallbackLanguage')
            ->willReturn($fallbackLanguage);

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock the table manipulator.
     *
     * @param Connection $connection The database connection mock.
     *
     * @return TableManipulator|MockObject
     */
    private function mockTableManipulator(Connection $connection)
    {
        return $this->getMockBuilder(TableManipulator::class)
            ->setConstructorArgs([$connection, []])
            ->getMock();
    }

    /**
     * Override the method to run the tests on the attribute factories to be tested.
     *
     * @return IAttributeTypeFactory[]
     */
    protected function getAttributeFactories()
    {
        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        return [new AttributeTypeFactory($connection, $manipulator, $dispatcher)];
    }

    /**
     * Test creation of a decimal attribute.
     *
     * @return void
     */
    public function testCreateAttribute()
    {
        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $factory   = new AttributeTypeFactory($connection, $manipulator, $dispatcher);
        $values    = [];
        $attribute = $factory->createInstance(
            $values,
            $this->mockMetaModel('mm_test', 'de', 'en')
        );

        self::assertInstanceOf(LangCode::class, $attribute);

        foreach ($values as $key => $value) {
            self::assertEquals($value, $attribute->get($key), $key);
        }
    }
}
