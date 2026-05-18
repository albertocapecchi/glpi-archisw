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
 * Link-class reference for dropdown and tree-dropdown configuration fields.
 *
 * Stores metadata about which external or plugin-generated class backs a
 * PluginArchiswConfigsw field of type Dropdown (6) or TreeDropdown (9).
 * When the class name starts with "PluginArchisw", a backing DB table/view and
 * PHP class files are generated automatically.
 *
 * @package archisw
 */
class PluginArchiswConfigswLink extends CommonDropdown {

   static $rightname = "plugin_archisw_configuration";
   var $can_be_translated  = true;
   
   /**
    * Return the localised type name for this class.
    *
    * @param int $nb Number of items (used for pluralisation).
    *
    * @return string Translated type name.
    */
   static function getTypeName($nb=0) {

      return _n('Link class','Link classes',$nb);
   }

   /**
    * Return additional form fields for the dropdown management screen.
    *
    * Adds has_dropdown, is_entity_limited, is_tree_dropdown, as_view_on, and
    * viewlimit fields.
    *
    * @return array Field definition arrays understood by CommonDropdown.
    */
   public function getAdditionalFields() {
      return [
            [
                  'name'      => 'has_dropdown',
                  'type'      => 'bool',
                  'label'     => __('Has own dropdown', 'archisw'),
                  'list'      => false
            ],
            [
                  'name'      => 'is_entity_limited',
                  'type'      => 'bool',
                  'label'     => __('Is limited by entity', 'archisw'),
                  'list'      => false
            ],
            [
                  'name'      => 'is_tree_dropdown',
                  'type'      => 'bool',
                  'label'     => __('Is hierarchical dropdown', 'archisw'),
                  'list'      => false
            ],
            [
                  'name'      => 'as_view_on',
                  'type'      => 'text',
                  'label'     => __('As view on table', 'archisw'),
                  'list'      => false
            ],
            [
                  'name'      => 'viewlimit',
                  'type'      => 'text',
                  'label'     => __('View limited by WHERE clause', 'archisw'),
                  'list'      => false
            ]
		];
   }
   /**
    * Return the search options for the ConfigSwLink management list.
    *
    * Extends standard CommonDropdown options with link-specific fields.
    *
    * @return array Search option entries.
    */
   function getSearchOptions() {
	  $opt = CommonDropdown::getSearchOptions();
//      $sopt['common'] = __("App structures", "archisw");

      $opt[2400]['id']          = 2400;
      $opt[2400]['table']       = $this->getTable();
      $opt[2400]['field']       = 'has_dropdown';
      $opt[2400]['name']        = __('Has own dropdown', 'archisw');
      $opt[2400]['datatype']    = 'boolean';

      $opt[2401]['id']          = 2401;
      $opt[2401]['table']       = $this->getTable();
      $opt[2401]['field']       = 'is_entity_limited';
      $opt[2401]['name']        = __('Is limited by entity', 'archisw');
      $opt[2401]['datatype']    = 'boolean';

      $opt[2402]['id']          = 2402;
      $opt[2402]['table']       = $this->getTable();
      $opt[2402]['field']       = 'is_tree_dropdown';
      $opt[2402]['name']        = __('Is hierarchical dropdown', 'archisw');
      $opt[2402]['datatype']    = 'boolean';

      $opt[2403]['id']          = 2403;
      $opt[2403]['table']       = $this->getTable();
      $opt[2403]['field']       = 'as_view_on';
      $opt[2403]['name']        = __('As view on table', 'archisw');
      $opt[2403]['datatype']    = 'text';

      return $opt;
   }
}

?>
