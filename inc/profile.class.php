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
 * Profile and rights management for the Archisw plugin.
 *
 * Extends GLPI's Profile class to add plugin-specific rights
 * (plugin_archisw, plugin_archisw_configuration, plugin_archisw_open_ticket)
 * and handles migration from the legacy rights table.
 *
 * @package archisw
 */
class PluginArchiswProfile extends Profile {

   static $rightname = "profile";

   /**
    * Return the tab label shown on the Profile form.
    *
    * @param CommonGLPI $item         The item whose tabs are being rendered.
    * @param int        $withtemplate Whether a template is being edited (default 0).
    *
    * @return string Tab label, or empty string when not applicable.
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile') {
            return PluginArchiswSwcomponent::getTypeName(2);
      }
      return '';
   }


   /**
    * Render the plugin rights tab content within the Profile form.
    *
    * @param CommonGLPI $item         The Profile item being displayed.
    * @param int        $tabnum       Tab number (default 1).
    * @param int        $withtemplate Whether a template is being edited (default 0).
    *
    * @return bool Always returns true.
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID,
                                    ['plugin_archisw'               => 0,
                                          'plugin_archisw_open_ticket'   => 0]);
         $prof->showForm($ID);
      }
      return true;
   }

   /**
    * Grant full rights on the plugin to the first (super-admin) profile.
    *
    * @param int $ID Profile ID to which rights are granted.
    *
    * @return void
    */
   static function createFirstAccess($ID) {
      //85
      self::addDefaultProfileInfos($ID,
                                   ['plugin_archisw'             => 127,
                                         'plugin_archisw_open_ticket' => 1], true);
   }

    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {

      $dbu = new DbUtils();
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
//                                   "`profiles_id`='$profiles_id' AND `name`='$right'") 
                                         ['profiles_id' => $profiles_id,
                                          'name'        => $right])
            && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
//                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
                                         ['profiles_id' => $profiles_id,
                                          'name'        => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }


   /**
    * Render the plugin rights form inside the Profile edit page.
    *
    * @param int  $profiles_id Profile record ID (default 0).
    * @param bool $openform    Whether to output the opening <form> tag (default true).
    * @param bool $closeform   Whether to output the closing form/submit markup (default true).
    *
    * @return void
    */
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')]);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>".__('Helpdesk')."</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_archisw_open_ticket']);
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Associable items to a ticket')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_archisw_open_ticket',
                               'checked' => $effective_rights['plugin_archisw_open_ticket']]);
      echo "</td></tr>\n";
      echo "</table>";

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   /**
    * Return all plugin right definitions.
    *
    * @param bool $all When true, also include the open_ticket right (default false).
    *
    * @return array Array of right definition arrays.
    */
   static function getAllRights($all = false) {
      $rights = [
          ['itemtype'  => 'PluginArchiswSwcomponent',
                'label'     => _n('Apps structure', 'Apps structures', 2, 'archisw'),
                'field'     => 'plugin_archisw'
          ],
          ['rights'    => [READ => __('Read'), CREATE => __('Create'), UPDATE => __('Update'), DELETE => __('Put in trashbin'), PURGE => __('Delete permanently')],
                'itemtype'  => 'PluginArchiswConfigsw',
                'label'     => __('Configuration', 'archisw'),
                'field'     => 'plugin_archisw_configuration']
      ];

      if ($all) {
         $rights[] = ['itemtype' => 'PluginArchiswSwcomponent',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_archisw_open_ticket'];
      }

      return $rights;
   }

   /**
    * Translate a legacy right string to the new GLPI constant.
    *
    * @param string $old_right Legacy right value ('r', 'w', '0', '1', or '').
    *
    * @return int GLPI right constant.
    */
   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   /**
    * Migrate rights from the legacy table to the new ProfileRight system for one profile.
    *
    * @param int $profiles_id The profile ID to migrate.
    *
    * @return bool True when the legacy table does not exist (nothing to migrate).
    *
    * @since 0.85
    */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->TableExists('glpi_plugin_archisw_profiles')) {
      return true;
      }

      foreach ($DB->request('glpi_plugin_archisw_profiles',
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching = ['archisw'    => 'plugin_archisw',
                           'open_ticket' => 'plugin_archisw_open_ticket'];
         $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
         foreach ($matching as $old => $new) {
            if (!isset($current_rights[$old])) {
               $query = "UPDATE `glpi_profilerights`
                         SET `rights`='".self::translateARight($profile_data[$old])."'
                         WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
               $DB->doQuery($query);
            }
         }
      }
   }

   /**
    * Initialise plugin rights for all profiles and migrate legacy rights.
    *
    * Ensures glpi_profilerights rows exist for every plugin right field,
    * then runs migrateOneProfile() for each existing profile.
    *
    * @return void
    */
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights",
                                         ['name'        => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      //Migration old rights in new ones
      foreach ($DB->request(['SELECT'=> 'id',
                              'FROM' => 'glpi_profiles']
               ) as $prof) {
         self::migrateOneProfile($prof['id']);
      }
      foreach ($DB->request(['FROM' =>  'glpi_profilerights',
                              'WHERE' =>  ['profiles_id' => $_SESSION['glpiactiveprofile']['id'], 
                                          'name' => ['LIKE', '%plugin_archisw%']]]
                              ) as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }


   /**
    * Remove all plugin rights from the current session.
    *
    * Called during plugin uninstall to clean up the session immediately.
    *
    * @return void
    */
   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}

?>
