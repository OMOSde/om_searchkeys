<?php

/**
 * Contao module om_searchkeys
 * 
 * Autoload file
 * 
 * @copyright OMOS.de 2015 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @package   om_searchkeys
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'ModuleOmSearch'     => 'system/modules/om_searchkeys/modules/ModuleOmSearch.php',
    'ModuleOmSearchKeys' => 'system/modules/om_searchkeys/modules/ModuleOmSearchKeys.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'om_searchkeys' => 'system/modules/om_searchkeys/templates',
));
