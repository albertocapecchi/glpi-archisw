<?php
/*
 -------------------------------------------------------------------------
 Archisw plugin for GLPI
 Copyright (C) 2009-2018 by Eric Feron.
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Archisw.

 Archisw is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 at your option any later version.

 Archisw is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archisw. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Data Injection adapter for PluginArchiswSwcomponent.
 *
 * Implements PluginDatainjectionInjectionInterface so that SwComponent records
 * can be imported via the GLPI Data Injection plugin.  Delegates storage to the
 * parent PluginArchiswSwcomponent table through PluginDatainjectionCommonInjectionLib.
 *
 * @package archisw
 */
class PluginArchiswSwcomponentInjection extends PluginArchiswSwcomponent
   implements PluginDatainjectionInjectionInterface {

   /**
    * Return the DB table name for this class (delegates to the parent class).
    *
    * @param string|null $classname Unused; kept for interface compatibility.
    *
    * @return string Table name.
    */
   static function getTable($classname = null) {

      $parenttype = get_parent_class();
      return $parenttype::getTable();

   }

   /**
    * Indicate whether this is a primary injectable type.
    *
    * @return bool Always returns true.
    */
   function isPrimaryType() {
      return true;
   }

   /**
    * Return the list of item types this class can be connected to for injection.
    *
    * @return array Empty array (no connected types).
    */
   function connectedTo() {
      return [];
   }

   /**
    * Return the injection options and field mappings for this type.
    *
    * Builds on the parent class search options and adds display-type hints and
    * a list of non-importable fields for the Data Injection plugin.
    *
    * @param string $primary_type The primary item type being injected (default '').
    *
    * @return array Processed injection options array.
    */
   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      $tab[8]['linkfield'] = 'locations_id';

      //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable            = [13, 30, 80];
      $options['ignore_fields'] = $notimportable;
      $options['displaytype']   = ["dropdown"       => [2, 4, 6, 7, 8, 9, 10, 11, 12],
                                   "user"           => [11],
                                   "multiline_text" => [3, 5],
                                   "date"           => [16],
                                   "bool"           => [14, 15]];

      return PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

   }

   /**
    * Delete a SwComponent record via the Data Injection library.
    *
    * @param array $values  Field values identifying the record to delete.
    * @param array $options Injection options.
    *
    * @return array Injection result array from PluginDatainjectionCommonInjectionLib.
    */
   function deleteObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }

   /**
    * Add or update a SwComponent record via the Data Injection library.
    *
    * @param array $values  Field values for the record to add or update.
    * @param array $options Injection options.
    *
    * @return array Injection result array from PluginDatainjectionCommonInjectionLib.
    */
   function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

}
