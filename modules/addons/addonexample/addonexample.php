<?php
/**
 * Addon Module Sample File
 *
 * This example addon module demonstrates all the functions an addon module can contain.
 * Please refer to the full documentation @ http://docs.whmcs.com/Addon_Modules for more details.
 *
 * @package    WHMCS
 * @author     WHMCS Limited <development@whmcs.com>
 * @copyright  Copyright (c) WHMCS Limited 2005-2013
 * @license    http://www.whmcs.com/license/ WHMCS Eula
 * @version    $Id$
 * @link       http://www.whmcs.com/
 */

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function addonexample_config() {
    $configarray = array(
    "name" => "Addon Example",
    "description" => "This is an open source addon module sample that can be used as a starting point for custom modules - has no function by default",
    "version" => "1.0",
    "author" => "WHMCS",
    "language" => "english",
    "fields" => array(
        "option1" => array ("FriendlyName" => "Option1", "Type" => "text", "Size" => "25", "Description" => "Textbox", "Default" => "Example", ),
        "option2" => array ("FriendlyName" => "Option2", "Type" => "password", "Size" => "25", "Description" => "Password", ),
        "option3" => array ("FriendlyName" => "Option3", "Type" => "yesno", "Size" => "25", "Description" => "Sample Check Box", ),
        "option4" => array ("FriendlyName" => "Option4", "Type" => "dropdown", "Options" => "1,2,3,4,5", "Description" => "Sample Dropdown", "Default" => "3", ),
        "option5" => array ("FriendlyName" => "Option5", "Type" => "radio", "Options" => "Demo1,Demo2,Demo3", "Description" => "Radio Options Demo", ),
        "option6" => array ("FriendlyName" => "Option6", "Type" => "textarea", "Rows" => "3", "Cols" => "50", "Description" => "Description goes here", "Default" => "Test", ),
    ));
    return $configarray;
}

function addonexample_activate() {

    # Create Custom DB Table
    $query = "CREATE TABLE `mod_addonexample` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`demo` TEXT NOT NULL )";
	$result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'This is an demo module only. In a real module you might instruct a user how to get started with it here...');
    return array('status'=>'error','description'=>'You can use the error status return to indicate there was a problem activating the module');
    return array('status'=>'info','description'=>'You can use the info status return to display a message to the user');

}

function addonexample_deactivate() {

    # Remove Custom DB Table
    $query = "DROP TABLE `mod_addonexample`";
	$result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'If successful, you can return a message to show the user here');
    return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function addonexample_upgrade($vars) {

    $version = $vars['version'];

    # Run SQL Updates for V1.0 to V1.1
    if ($version < 1.1) {
        $query = "ALTER `mod_addonexample` ADD `demo2` TEXT NOT NULL ";
    	$result = full_query($query);
    }

    # Run SQL Updates for V1.1 to V1.2
    if ($version < 1.2) {
        $query = "ALTER `mod_addonexample` ADD `demo3` TEXT NOT NULL ";
    	$result = full_query($query);
    }

}

function addonexample_output($vars) {

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $option3 = $vars['option3'];
    $option4 = $vars['option4'];
    $option5 = $vars['option5'];
    $LANG = $vars['_lang'];

    echo '<p>'.$LANG['intro'].'</p>
<p>'.$LANG['description'].'</p>
<p>'.$LANG['documentation'].'</p>';

}

function addonexample_sidebar($vars) {

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $option3 = $vars['option3'];
    $option4 = $vars['option4'];
    $option5 = $vars['option5'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> Example</span>
<ul class="menu">
        <li><a href="#">Demo Sidebar Content</a></li>
        <li><a href="#">Version: '.$version.'</a></li>
    </ul>';
    return $sidebar;

}