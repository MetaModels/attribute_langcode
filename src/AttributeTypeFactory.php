<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeLangCode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\LangCode;

use MetaModels\Attribute\AbstractAttributeTypeFactory;

/**
 * Attribute type factory for langcode attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->typeName  = 'langcode';
        $this->typeIcon  = 'system/modules/metamodelsattribute_langcode/html/langcode.png';
        $this->typeClass = 'MetaModels\Attribute\LangCode\LangCode';
    }
}
