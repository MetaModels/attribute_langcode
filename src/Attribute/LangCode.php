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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Maack <maack@men-at-work.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Benedict Zinke <bz@presentprogressive.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_langcode/blob/master/LICENSE LGPL-3.0-or-later
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
use Patchwork\Utf8;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling langcodes.
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
     * Holds the result of the function getLangauge.
     *
     * @var null|array
     */
    private $languageCache = null;

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
        return 'varchar(5) NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'langcodes',
                'filterable',
                'searchable',
                'mandatory',
                'includeBlankOption'
            ]
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
        // Check if we have the data in the cache.
        if (null !== $this->languageCache) {
            return $this->languageCache;
        }

        $loadedLanguage = $this->getMetaModel()->getActiveLanguage();
        $languageValues = $this->getLanguageNames($loadedLanguage);
        $languages      = $this->getRealLanguages();
        $keys           = \array_keys($languages);
        $aux            = [];
        $real           = [];

        // Fetch real language values.
        foreach ($keys as $key) {
            if (isset($languageValues[$key])) {
                $aux[$key]  = Utf8::toAscii($languageValues[$key]);
                $real[$key] = $languageValues[$key];
            }
        }

        // Add needed fallback values.
        $keys = \array_diff($keys, \array_keys($aux));
        if ($keys) {
            $this->addNeededFallbackLanguages($keys, $aux, $real);
        }

        $keys = \array_diff($keys, \array_keys($aux));
        if ($keys) {
            foreach ($keys as $key) {
                $aux[$key]  = Utf8::toAscii($languages[$key]);
                $real[$key] = $languages[$key];
            }
        }

        \asort($aux);
        $return = [];
        foreach (\array_keys($aux) as $key) {
            $return[$key] = $real[$key];
        }

        // Switch back to the original FE language to not disturb the frontend.
        if ($loadedLanguage != $GLOBALS['TL_LANGUAGE']) {
            $event = new LoadLanguageFileEvent('languages', null, true);
            $this->eventDispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);
        }

        return $this->languageCache = $return;
    }

    /**
     * Add the fallback languages to the array.
     *
     * @param array $keys The lang keys.
     *
     * @param array $aux  The formatted current values, as references.
     *
     * @param array $real The real current values, as references.
     *
     * @return void
     */
    private function addNeededFallbackLanguages($keys, &$aux, &$real)
    {
        $loadedLanguage = $this->getMetaModel()->getFallbackLanguage();
        $fallbackValues = $this->getLanguageNames($loadedLanguage);
        foreach ($keys as $key) {
            if (isset($fallbackValues[$key])) {
                $aux[$key]  = Utf8::toAscii($fallbackValues[$key]);
                $real[$key] = $fallbackValues[$key];
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        // If empty list, return empty result. See also MM-Core #379 for discussion.
        if ($idList === array()) {
            return array();
        }

        $languages = $this->getLanguages();
        $strCol    = $this->getColName();
        if ($idList) {
            $statement = $this
                ->connection
                ->createQueryBuilder()
                ->select('t.' . $strCol . ', COUNT(t.' . $strCol . ') as mm_count')
                ->from($this->getMetaModel()->getTableName(), 't')
                ->where('t.id IN (:ids)')
                ->groupBy('t.' . $strCol)
                ->orderBy('FIELD(t.id, :ids)')
                ->setParameter('ids', $idList, Connection::PARAM_STR_ARRAY)
                ->execute();
        } elseif ($usedOnly) {
            $statement = $this
                ->connection
                ->createQueryBuilder()
                ->select('t.' . $strCol . ', COUNT(t.' . $strCol . ') as mm_count')
                ->from($this->getMetaModel()->getTableName(), 't')
                ->groupBy('t.' . $strCol)
                ->orderBy('t.' . $strCol)
                ->execute();
        } else {
            return \array_intersect_key(
                $this->getLanguageNames(),
                \array_flip((array) $this->get('langcodes'))
            );
        }

        $arrResult = array();
        while ($objRow = $statement->fetch(\PDO::FETCH_OBJ)) {
            if (is_array($arrCount)) {
                $arrCount[$objRow->$strCol] = $objRow->mm_count;
            }
            $arrResult[$objRow->$strCol] = ($languages[$objRow->$strCol]) ?: $objRow->$strCol;
        }

        return $arrResult;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef                   = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']      = 'select';
        $arrFieldDef['eval']['chosen'] = true;
        $arrFieldDef['options']        = \array_intersect_key(
            $this->getLanguageNames(),
            \array_flip((array) $this->get('langcodes'))
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

    /**
     * {@inheritDoc}
     *
     * This is needed for compatibility with MySQL strict mode.
     */
    public function serializeData($value)
    {
        return $value === '' ? null : $value;
    }
}
