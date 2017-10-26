<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeLangCode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Maack <maack@men-at-work.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['langcode extends _simpleattribute_'] = array
(
    '+display' => array('langcodes after description')
);

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['langcodes'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['langcodes'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => array
    (
        'doNotSaveEmpty' => true,
        'alwaysSave'     => true,
        'multiple'       => true
    ),
    'options' => $this->getLanguages()
);
