<?php

/**
 * This file is part of MetaModels/attribute_langcode.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_langcode
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeLangCodeBundle\Test\DependencyInjection;

use MetaModels\AttributeLangCodeBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeLangCodeBundle\DependencyInjection\MetaModelsAttributeLangCodeExtension;
use MetaModels\AttributeLangCodeBundle\Migration\AllowNullMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeLangCodeBundle\DependencyInjection\MetaModelsAttributeLangCodeExtension
 */
class MetaModelsAttributeLangCodeExtensionTest extends TestCase
{
    public function testInstantiation(): void
    {
        $extension = new MetaModelsAttributeLangCodeExtension();

        self::assertInstanceOf(MetaModelsAttributeLangCodeExtension::class, $extension);
        self::assertInstanceOf(ExtensionInterface::class, $extension);
    }

    public function testRegistersServices(): void
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeLangCodeExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_langcode.factory'));
        $definition = $container->getDefinition('metamodels.attribute_langcode.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));

        self::assertTrue($container->hasDefinition(AllowNullMigration::class));
        $definition = $container->getDefinition(AllowNullMigration::class);
        self::assertCount(1, $definition->getTag('contao.migration'));
    }
}
