<?php
/**
 * Addon Module Sample Hook File
 *
 * This is a demo hook file for an addon module. Addon Modules can utilise all of the WHMCS
 * hooks in exactly the same way as a normal hook file would, and can contain multiple hooks.
 *
 * For more info, please refer to the hooks documentation @ http://docs.whmcs.com/Hooks
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

function addonexample_hook_login($vars) {
    // Your code goes here
}

// Define Client Login Hook Call
add_hook("ClientLogin",1,"addonexample_hook_login");

function addonexample_hook_logout($vars) {
    // Your code goes here
}

// Define Client Logout Hook Call
add_hook("ClientLogout",1,"addonexample_hook_logout");