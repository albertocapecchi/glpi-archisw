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

define('PLUGIN_ARCHISW_VERSION', '3.0.25');

// Minimal GLPI version, inclusive
define('PLUGIN_ARCHISW_MIN_GLPI', '10.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_ARCHISW_MAX_GLPI', '11.0.99');

/**
 * Initialise the Archisw plugin hooks, registrations, and profile hooks.
 *
 * Called by GLPI on every page load when the plugin is active.
 *
 * @return void
 */
function plugin_init_archisw() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $DB;

   $PLUGIN_HOOKS['csrf_compliant']['archisw'] = true;
   $PLUGIN_HOOKS['change_profile']['archisw'] = ['PluginArchiswProfile', 'initProfile'];
   $PLUGIN_HOOKS['assign_to_ticket']['archisw'] = true;
   
   $PLUGIN_HOOKS['assign_to_ticket_dropdown']['archisw'] = true;
   $PLUGIN_HOOKS['assign_to_ticket_itemtype']['archisw'] = ['PluginArchiswSwcomponent_Item'];
   
   $CFG_GLPI['impact_asset_types']['PluginArchiswSwcomponent'] = Plugin::getWebDir("archisw", false)."/swcomponent.png";

   Plugin::registerClass('PluginArchiswSwcomponent', [
         'linkgroup_tech_types'   => true,
         'linkuser_tech_types'    => true,
         'document_types'         => true,
         'ticket_types'           => true,
         'helpdesk_visible_types' => true,
         'addtabon'               => 'Supplier'
   ]);
   Plugin::registerClass('PluginArchiswProfile',
                         ['addtabon' => 'Profile']);
                         
   //Plugin::registerClass('PluginDatabasesDatabase_Item',
   //                      array('ticket_types' => true));

   // Register generic objects from genericobject plugin
   $plugin = new Plugin();
   if ($plugin->isActivated('genericobject')) {
      $query = "SELECT itemtype FROM `glpi_plugin_genericobject_types` WHERE `is_active` = TRUE";
      $result = $DB->doQuery($query);
      $rowcount = $DB->numrows($result);
      if ($rowcount > 0) {
         while ($data = $DB->fetchAssoc($result)) {
            PluginArchiswSwcomponent::registerType($data['itemtype']);
         }
      }
   }
   // Add links to other plugins
   $types = ['PluginArchimapGraph'];
   $associatedtypes = ['PluginAccountsAccount',
                     'PluginArchiswSwcomponent'];
  foreach ($types as $itemtype) {
      if (class_exists($itemtype)) {
         $itemtype::registerType('PluginArchiswSwcomponent');
         PluginArchiswSwcomponent::registerType($itemtype);
      }
   }
// Add other plugin associations
   if (class_exists('PluginArchiswSwcomponent'))
	  foreach ($associatedtypes as $itemtype) {
//		if (class_exists($itemtype)) {
//			$itemtype::registerType('PluginArchiswSwcomponent');
            PluginArchiswSwcomponent::registerType($itemtype);
//		}
	  }

   if (Session::getLoginUserID()) {

      // link to fields plugin
      if ($plugin->isActivated('fields')
      && Session::haveRight("plugin_archisw", READ))
      {
         $PLUGIN_HOOKS['plugin_fields']['archisw'] = 'PluginArchiswSwcomponent';
      }

      if (Session::haveRight("plugin_archisw", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['archisw']['assets'] = 'PluginArchiswMenu';
      }

      if (Session::haveRight("plugin_archisw_configuration", READ)) {

         $PLUGIN_HOOKS['menu_toadd']['archisw']["config"] = 'PluginArchiswConfigswMenu';
      }

      if (Session::haveRight("plugin_archisw", READ)
          || Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['archisw']        = 'front/configsw.php';
      }

      if (Session::haveRight("plugin_archisw", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['archisw']=1;
      }

      if (class_exists('PluginArchiswSwcomponent_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['plugin_datainjection_populate']['archisw'] = 'plugin_datainjection_populate_archisw';
      }
      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['archisw'] = 'plugin_archisw_postinit';

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['archisw'] = 'plugin_datainjection_migratetypes_archisw';
      
      $PLUGIN_HOOKS['pre_item_update']['archisw'] = ['PluginArchiswConfigsw' => 'hook_pre_item_update_archisw_configsw', 
                                                   'PluginArchiswConfigswLink' => 'hook_pre_item_update_archisw_configswlink'];
      $PLUGIN_HOOKS['pre_item_add']['archisw'] = ['PluginArchiswConfigsw' => 'hook_pre_item_add_archisw_configsw', 
                                                   'PluginArchiswConfigswLink' => 'hook_pre_item_add_archisw_configswlink'];
      $PLUGIN_HOOKS['pre_item_purge']['archisw'] = ['PluginArchiswConfigsw' => 'hook_pre_item_purge_archisw_configsw', 
                                                   'PluginArchiswConfigswLink' => 'hook_pre_item_purge_archisw_configswlink'];

   }
}

/**
 * Return the plugin metadata (name, version, author, licence, requirements).
 *
 * @return array Plugin metadata keyed by GLPI-expected fields.
 */
function plugin_version_archisw() {

   return array (
      'name' => _n('Apps structure', 'Apps structures', 2, 'archisw'),
      'version' => PLUGIN_ARCHISW_VERSION,
      'author'  => "Eric Feron",
      'license' => 'GPLv2+',
      'homepage'=> 'https://github.com/ericferon/glpi-archisw',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_ARCHISW_MIN_GLPI,
            'max' => PLUGIN_ARCHISW_MAX_GLPI,
//            'dev' => false
         ]
      ]
   );

}

/**
 * Check that the required prerequisites (statecheck plugin) are met before installation.
 *
 * Prints an error message when the prerequisite is not satisfied.
 *
 * @return bool True when prerequisites are satisfied, false otherwise.
 */
function plugin_archisw_check_prerequisites() {
   global $DB;
		$query = "select * from glpi_plugins where directory = 'statecheck' and state = 1";
		$result_query = $DB->doQuery($query);
		if($DB->numRows($result_query) == 1) {
			return true;
		} else {
			echo "the plugin 'statecheck' must be installed before using 'Apps structure (archisw)'";
			return false;
		}
}

/**
 * Verify the plugin configuration after activation.
 *
 * @return bool True if the configuration is valid.
 */
function plugin_archisw_check_config() {
   return true;
}

/*function plugin_datainjection_migratetypes_archisw($types) {
   $types[2400] = 'PluginArchiswSwcomponent';
   return $types;
}
*/
/**
 * Hook called before a PluginArchiswConfigsw record is added.
 *
 * Alters the swcomponents table to ADD the new DB column (and an index when the
 * field is a dropdown) that corresponds to the newly configured field.
 *
 * @param CommonDBTM $item The ConfigSw item being added.
 *
 * @return bool True on success, false when the DB field type cannot be loaded.
 */
function hook_pre_item_add_archisw_configsw(CommonDBTM $item) {
   global $DB;
   $fieldname = $item->fields['name'];
   $dbfield = new PluginArchiswConfigswDbfieldtype;
   if ($dbfield->getFromDB($item->fields['plugin_archisw_configswdbfieldtypes_id'])) {
      $fieldtype = $dbfield->fields['name'];
      $query = "ALTER TABLE `glpi_plugin_archisw_swcomponents` ADD COLUMN IF NOT EXISTS $fieldname $fieldtype";
      if($item->fields['plugin_archisw_configswdatatypes_id'] == 6) {// if dropdown, add key
         $query .= ", ADD KEY IF NOT EXISTS $fieldname ($fieldname)";
      }
      $result = $DB->doQuery($query);
      return true;
   }
   return false;
}
/**
 * Hook called before a PluginArchiswConfigsw record is updated.
 *
 * Issues an ALTER TABLE CHANGE (when the column is renamed) or MODIFY on the
 * swcomponents table to reflect the updated field definition.
 *
 * @param CommonDBTM $item The ConfigSw item being updated.
 *
 * @return bool True on success, false when the DB field type cannot be loaded.
 */
function hook_pre_item_update_archisw_configsw(CommonDBTM $item) {
   global $DB;
   $oldfieldname = $item->fields['name'];
   $newfieldname = $item->input['name'];
   $dbfield = new PluginArchiswConfigswDbfieldtype;
   if ($dbfield->getFromDB($item->fields['plugin_archisw_configswdbfieldtypes_id'])) {
      $fieldtype = $dbfield->fields['name'];
      if ($oldfieldname != $newfieldname) {
         $query = "ALTER TABLE `glpi_plugin_archisw_swcomponents` CHANGE COLUMN $oldfieldname $newfieldname $fieldtype";
      } else {
         $query = "ALTER TABLE `glpi_plugin_archisw_swcomponents` MODIFY $newfieldname $fieldtype";
      }
      if($item->input['plugin_archisw_configswdatatypes_id'] == 6) {// if dropdown, add key
         $query .= ", ADD KEY IF NOT EXISTS $newfieldname ($newfieldname)";
      }
      $result = $DB->doQuery($query);
      return true;
   }
   return false;
}
/**
 * Hook called before a PluginArchiswConfigsw record is permanently deleted.
 *
 * Removes the matching label translations and drops the corresponding column
 * from the swcomponents table.
 *
 * @param CommonDBTM $item The ConfigSw item being purged.
 *
 * @return bool Always returns true.
 */
function hook_pre_item_purge_archisw_configsw(CommonDBTM $item) {
   global $DB;
   $oldid = $item->fields['id'];
   $oldfieldname = $item->fields['name'];
   // suppress in glpi_plugin_archisw_labeltranslations
   $query = "DELETE FROM `glpi_plugin_archisw_labeltranslations` WHERE `items_id` = '".$oldid."'";
   $result = $DB->doQuery($query);
   // suppress column
   $query = "ALTER TABLE `glpi_plugin_archisw_swcomponents` DROP COLUMN IF EXISTS $oldfieldname";
   $result = $DB->doQuery($query);
   return true;
}
?>
