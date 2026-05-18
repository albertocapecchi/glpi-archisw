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
 * Field group (accordion section) for organising SwComponent form fields.
 *
 * Records in this dropdown define named sections shown as collapsible Bootstrap
 * accordions in the SwComponent edit form.  A sort order and default visibility
 * flag control the display order and initial open/closed state.
 *
 * @package archisw
 */
class PluginArchiswConfigswFieldgroup extends CommonDropdown {

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

      return _n('Field group','Field groups',$nb);
   }
   
   /**
    * Return additional form fields for the field-group management screen.
    *
    * Adds a sortorder text field and an is_visible boolean field.
    *
    * @return array Field definition arrays understood by CommonDropdown.
    */
   public function getAdditionalFields() {
      return [
            [
                  'name'      => 'sortorder',
                  'type'      => 'text',
                  'label'     => __('Sort order', 'archisw'),
                  'list'      => false
            ],
            [
                  'name'      => 'is_visible',
                  'type'      => 'bool',
                  'label'     => __('Is visible on opening', 'archisw'),
                  'list'      => false
            ]
		];
   }
   
   /**
    * Return the search options for the field-group management list.
    *
    * Extends standard CommonDropdown options with sortorder and is_visible fields.
    *
    * @return array Search option entries.
    */
   function rawSearchOptions() {
	  $opt = CommonDropdown::rawSearchOptions();
//      $sopt['common'] = __("App structures", "archisw");

      $opt[2400]['id']          = 2400;
      $opt[2400]['table']       = $this->getTable();
      $opt[2400]['field']       = 'sortorder';
      $opt[2400]['name']        = __('Sort order', 'archisw');
      $opt[2400]['datatype']    = 'text';

      $opt[2401]['id']          = 2401;
      $opt[2401]['table']       = $this->getTable();
      $opt[2401]['field']       = 'is_visible';
      $opt[2401]['name']        = __('Is visible on opening', 'archisw');
      $opt[2401]['datatype']    = 'bool';

      return $opt;
   }
}

?>
