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
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeLangCodeBundle\Attribute;

use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling langcodes.
 *
 * @package    MetaModels
 * @subpackage AttributeLangCode
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class LangCode extends BaseSimple
{
    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel               $objMetaModel     The MetaModel instance this attribute belongs to.
     *
     * @param array                    $arrData          The information array, for attribute information, refer to
     *                                                   documentation of table tl_metamodel_attribute and documentation
     *                                                   of the certain attribute classes for information what values
     *                                                   are understood.
     *
     * @param Connection               $connection       The database connection.
     *
     * @param TableManipulator         $tableManipulator Table manipulator instance.
     *
     * @param EventDispatcherInterface $eventDispatcher  The event disatcher.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        TableManipulator $tableManipulator = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection, $tableManipulator);

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error(
                'Event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            $eventDispatcher = System::getContainer()->get('event_dispatcher');
            // @codingStandardsIgnoreEnd
        }

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
    {
        parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);
        $objTemplate->value = $this->resolveValue($arrRowData[$this->getColName()]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'varchar(5) NOT NULL default \'\'';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(
            parent::getAttributeSettingNames(),
            array(
                'langcodes',
                'filterable',
                'searchable',
                'mandatory',
                'includeBlankOption'
            )
        );
    }

    /**
     * Get all real languages available in Contao.
     *
     * Include the TL_ROOT/vendor/contao/core-bundle/src/Resources/contao/config/languages.php file and return the
     * contained language variable.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getRealLanguages()
    {
        // @codingStandardsIgnoreStart - Include is required here, can not switch to require_once.
        include(TL_ROOT . '/vendor/contao/core-bundle/src/Resources/contao/config/languages.php');
        // @codingStandardsIgnoreEnd

        /** @var string[] $languages */
        return $languages;
    }

    /**
     * Retrieve all language names in the given language.
     *
     * @param string $language The language key.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLanguageNames($language = null)
    {
        $event = new LoadLanguageFileEvent('languages', $language, true);
        $this->eventDispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);

        return $GLOBALS['TL_LANG']['LNG'];
    }

    /**
     * Retrieve all language names.
     *
     * This method takes the fallback language into account.
     *
     * @return string[]
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLanguages()
    {
        $loadedLanguage = $this->getMetaModel()->getActiveLanguage();
        $languageValues = $this->getLanguageNames($loadedLanguage);
        $languages      = $this->getRealLanguages();
        $keys           = array_keys($languages);
        $aux            = array();
        $real           = array();

        // Fetch real language values.
        foreach ($keys as $key) {
            if (isset($languageValues[$key])) {
                $aux[$key]  = utf8_romanize($languageValues[$key]);
                $real[$key] = $languageValues[$key];
            }
        }

        // Add needed fallback values.
        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            $loadedLanguage = $this->getMetaModel()->getFallbackLanguage();
            $fallbackValues = $this->getLanguageNames($loadedLanguage);
            foreach ($keys as $key) {
                if (isset($fallbackValues[$key])) {
                    $aux[$key]  = utf8_romanize($fallbackValues[$key]);
                    $real[$key] = $fallbackValues[$key];
                }
            }
        }

        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            foreach ($keys as $key) {
                $aux[$key]  = utf8_romanize($languages[$key]);
                $real[$key] = $languages[$key];
            }
        }

        asort($aux);
        $return = array();
        foreach (array_keys($aux) as $key) {
            $return[$key] = $real[$key];
        }

        // Switch back to the original FE language to not disturb the frontend.
        if ($loadedLanguage != $GLOBALS['TL_LANGUAGE']) {
            $event = new LoadLanguageFileEvent('languages', null, true);
            $this->eventDispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef                   = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']      = 'select';
        $arrFieldDef['eval']['chosen'] = true;
        $arrFieldDef['options']        = array_intersect_key(
            $this->getLanguageNames(),
            array_flip((array) $this->get('langcodes'))
        );

        return $arrFieldDef;
    }

    /**
     * Resolve a language code to the real language name in either the currently active language or the fallback.
     *
     * @param string $strLangValue The language code to resolve.
     *
     * @return string
     */
    protected function resolveValue($strLangValue)
    {
        $countries = $this->getLanguages();

        return $countries[$strLangValue];
    }
}
