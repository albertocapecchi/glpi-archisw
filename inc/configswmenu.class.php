<?php
/*
 -------------------------------------------------------------------------
 Archisw plugin for GLPI
 Copyright (C) 2020-2022 by Eric Feron.
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
/**
 * GLPI Configuration menu entry for the Archisw plugin.
 *
 * Registers the "Apps structures configuration" entry under the GLPI Config
 * menu and provides search/add links to the ConfigSw front-end pages.
 *
 * @package archisw
 */
class PluginArchiswConfigswMenu extends CommonGLPI {
   static $rightname = 'plugin_archisw_configuration';

   /**
    * Return the localised configuration menu name.
    *
    * @return string Translated menu title.
    */
   static function getMenuName() {
      return _n('Apps structure configuration', 'Apps structures configuration', 2, 'archisw');
   }

   /**
    * Build and return the menu descriptor array for the Config section.
    *
    * @return array GLPI menu descriptor with title, page, links, and icon.
    */
   static function getMenuContent() {
      global $CFG_GLPI;

		$menu                                           = [];
		$menu['title']                                  = self::getMenuName();
		$menu['page']                                   = "/".Plugin::getWebDir('archisw', false)."/front/configsw.php";
		$menu['links']['search']                        = PluginArchiswConfigsw::getSearchURL(false);
		if (PluginArchiswConfigsw::canCreate()) {
			$menu['links']['add']                        = PluginArchiswConfigsw::getFormURL(false);
		}
		$menu['icon'] = self::getIcon();

		return $menu;
	}

	/**
	 * Return the Font Awesome icon class for this configuration menu entry.
	 *
	 * @return string CSS class string.
	 */
	static function getIcon() {
		return "fas fa-cog";
	}

   /**
    * Remove the configuration menu entry from the current session cache.
    *
    * Called during plugin uninstall to clean up the session immediately.
    *
    * @return void
    */
   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['configsw']['types']['PluginArchiswConfigswMenu'])) {
         unset($_SESSION['glpimenu']['configsw']['types']['PluginArchiswConfigswMenu']); 
      }
      if (isset($_SESSION['glpimenu']['configsw']['content']['pluginarchiswconfigswmenu'])) {
         unset($_SESSION['glpimenu']['configsw']['content']['pluginarchiswconfigswmenu']); 
      }
   }
}
