<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Attribute\LangCode;

use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\Attribute\LangCode\AttributeTypeFactory;
use MetaModels\IMetaModel;
use MetaModels\Test\Attribute\AttributeTypeFactoryTest;
use MetaModels\MetaModel;
use MetaModels\Attribute\LangCode\LangCode;

/**
 * Test the attribute factory.
 */
class LangCodeAttributeTypeFactoryTest extends AttributeTypeFactoryTest
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
        $metaModel = $this->getMock(MetaModel::class, [], [[]]);

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue($tableName));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        return $metaModel;
    }

    /**
     * Override the method to run the tests on the attribute factories to be tested.
     *
     * @return IAttributeTypeFactory[]
     */
    protected function getAttributeFactories()
    {
        return [new AttributeTypeFactory()];
    }

    /**
     * Test creation of a decimal attribute.
     *
     * @return void
     */
    public function testCreateSelect()
    {
        $factory   = new AttributeTypeFactory();
        $values    = [];
        $attribute = $factory->createInstance(
            $values,
            $this->mockMetaModel('mm_test', 'de', 'en')
        );

        $this->assertInstanceOf(LangCode::class, $attribute);

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $attribute->get($key), $key);
        }
    }
}