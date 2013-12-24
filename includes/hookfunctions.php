<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

function sort_array_by_priority($a, $b) {
	return $a['priority'] < $b['priority'] ? 0 - 1 : 1;
}

function run_hook($hook_name, $args) {
	global $hooks;

	if (!array_key_exists($hook_name, $hooks)) {
		return array();
	}

	unset($rollbacks);
	$rollbacks = array();
	reset($hooks[$hook_name]);
	$results = array();

	while (list($key, $hook) = each($hooks[$hook_name])) {
		array_push($rollbacks, $hook['rollback_function']);

		if (function_exists($hook['hook_function'])) {
			$res = call_user_func($hook['hook_function'], $args);

			if ($res) {
				$results[] = $res;
			}
		}
	}

	return $results;
}

function add_hook($hook_name, $priority, $hook_function, $rollback_function = "") {
	global $hooks;

	if (!array_key_exists($hook_name, $hooks)) {
		$hooks[$hook_name] = array();
	}

	array_push($hooks[$hook_name], array("priority" => $priority, "hook_function" => $hook_function, "rollback_function" => $rollback_function));
	uasort($hooks[$hook_name], "sort_array_by_priority");
}

function remove_hook($hook_name, $priority, $hook_function, $rollback_function) {
	global $hooks;

	if (array_key_exists($hook_name, $hooks)) {
		reset($hooks[$hook_name]);

		while (list($key, $hook) = each($hooks[$hook_name])) {
			if (((0 <= $priority && $priority == $hook['priority']) || ($hook_function && $hook_function == $hook['hook_function'])) || ($rollback_function && $rollback_function == $hook['rollback_function'])) {
				unset($hooks[$hook_name][$key]);
			}
		}
	}

}

function clear_hooks($hook_name) {
	global $hooks;

	if (array_key_exists($hook_name, $hooks)) {
		unset($hooks[$hook_name]);
	}

}

function run_validate_hook($validate, $hook_name, $args) {
	$hookerrors = run_hook($hook_name, $args);
	$errormessage = "";

	if (count($hookerrors)) {
		foreach ($hookerrors as $hookerrors2) {

			if (is_array($hookerrors2)) {
				$validate->addErrors($hookerrors2);
				continue;
			}

			$validate->addError($hookerrors2);
		}
	}

}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

$hooks = array();
$hooksdir = ROOTDIR . "/includes/hooks/";
$dh = opendir($hooksdir);

while (false !== $hookfile = readdir($dh)) {
	if (is_file($hooksdir . $hookfile)) {
		$extension = end(explode(".", $hookfile));

		if ($extension == "php") {
			include $hooksdir . $hookfile;
		}
	}
}

closedir($dh);
$moduleshooks = explode(",", $CONFIG['ModuleHooks']);
foreach ($moduleshooks as $moduleshook) {
	$moduleshook = ROOTDIR . "/modules/servers/" . $moduleshook . "/hooks.php";

	if (file_exists($moduleshook)) {
		include $moduleshook;
		continue;
	}
}

$moduleshooks = explode(",", $CONFIG['RegistrarModuleHooks']);
foreach ($moduleshooks as $moduleshook) {
	$moduleshook = ROOTDIR . "/modules/registrars/" . $moduleshook . "/hooks.php";

	if (file_exists($moduleshook)) {
		include $moduleshook;
		continue;
	}
}

$addonmoduleshooks = explode(",", $CONFIG['AddonModulesHooks']);
foreach ($addonmoduleshooks as $addonmoduleshook) {
	$addonmoduleshook = ROOTDIR . "/modules/addons/" . $addonmoduleshook . "/hooks.php";

	if (file_exists($addonmoduleshook)) {
		include $addonmoduleshook;
		continue;
	}
}

?>