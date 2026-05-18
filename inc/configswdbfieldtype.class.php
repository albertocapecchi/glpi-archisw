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
 * Database field type options for SwComponent custom fields.
 *
 * Stores the MySQL column type string (e.g. "VARCHAR(255)", "INT", "DATE")
 * used when ALTERing the swcomponents table to add or modify a configured field.
 *
 * @package archisw
 */
class PluginArchiswConfigswDbfieldtype extends CommonDropdown {

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

      return _n('DB field type','DB field types',$nb);
   }
}

?>
