<?php

/**
 * This file is part of MetaModels/attribute_langcode.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_langcode
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeLangCodeBundle\Test\DependencyInjection;

use MetaModels\AttributeLangCodeBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeLangCodeBundle\DependencyInjection\MetaModelsAttributeLangCodeExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 */
class MetaModelsAttributeLangCodeExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $extension = new MetaModelsAttributeLangCodeExtension();

        $this->assertInstanceOf(MetaModelsAttributeLangCodeExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testFactoryIsRegistered()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects($this->once())
            ->method('setDefinition')
            ->with(
                'metamodels.attribute_langcode.factory',
                $this->callback(
                    function ($value) {
                        /** @var Definition $value */
                        $this->assertInstanceOf(Definition::class, $value);
                        $this->assertEquals(AttributeTypeFactory::class, $value->getClass());
                        $this->assertCount(1, $value->getTag('metamodels.attribute_factory'));

                        return true;
                    }
                )
            );

        $extension = new MetaModelsAttributeLangCodeExtension();
        $extension->load([], $container);
    }
}
