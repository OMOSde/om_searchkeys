<?php

/**
 * Contao module om_searchkeys
 * 
 * Config file
 * 
 * @copyright OMOS.de <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @package   om_searchkeys
 */


/**
 * Backend modules
 */
 
$GLOBALS['BE_MOD']['system']['om_searchkeys'] = array (
    'callback'   => 'ModuleOmSearchKeys',
    'icon'       => 'system/modules/om_searchkeys/assets/icons/find.png',
    'stylesheet' => 'system/modules/om_searchkeys/assets/css/om_searchkeys.css',
); 
 
 
/**
 * Applications
 */
$GLOBALS['FE_MOD']['application']['search'] = 'ModuleOmSearch';
