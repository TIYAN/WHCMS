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

function uwd_xml2array($contents, $get_attributes = 1, $priority = "tag") {
	$parser = xml_parser_create("");
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);

	if (!$xml_values) {
		return null;
	}

	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;

	$repeated_tag_index = array();
	foreach ($xml_values as $data) {
		unset($attributes);
		unset($value);
		extract($data);
		$result = array();
		$attributes_data = array();

		if (isset($value)) {
			if ($priority == "tag") {
				$result = $value;
			}
			else {
				$result['value'] = $value;
			}
		}


		if (isset($attributes) && $get_attributes) {
			foreach ($attributes as $attr => $val) {

				if ($priority == "tag") {
					$attributes_data[$attr] = $val;
					continue;
				}

				$result['attr'][$attr] = $val;
			}
		}


		if ($type == "open") {
			$parent[$level - 1] = &$current;

			if (!is_array($current) || !in_array($tag, array_keys($current))) {
				$current[$tag] = $result;

				if ($attributes_data) {
					$current[$tag . "_attr"] = $attributes_data;
				}

				$repeated_tag_index[$tag . "_" . $level] = 1;
				$current = &$current[$tag];

				continue;
			}


			if (isset($current[$tag][0])) {
				$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;
				++$repeated_tag_index[$tag . "_" . $level];
			}
			else {
				$current[$tag] = array($current[$tag], $result);
				$repeated_tag_index[$tag . "_" . $level] = 2;

				if (isset($current[$tag . "_attr"])) {
					$current[$tag]['0_attr'] = $current[$tag . "_attr"];
					unset($current[$tag . "_attr"]);
				}
			}

			$last_item_index = $repeated_tag_index[$tag . "_" . $level] - 1;
			$current = &$current[$tag][$last_item_index];

			continue;
		}


		if ($type == "complete") {
			if (!isset($current[$tag])) {
				$current[$tag] = $result;
				$repeated_tag_index[$tag . "_" . $level] = 1;

				if ($priority == "tag" && $attributes_data) {
					$current[$tag . "_attr"] = $attributes_data;
					continue;
				}

				continue;
			}


			if (isset($current[$tag][0]) && is_array($current[$tag])) {
				$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;

				if (($priority == "tag" && $get_attributes) && $attributes_data) {
					$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
				}

				++$repeated_tag_index[$tag . "_" . $level];
				continue;
			}

			$current[$tag] = array($current[$tag], $result);
			$repeated_tag_index[$tag . "_" . $level] = 1;

			if ($priority == "tag" && $get_attributes) {
				if (isset($current[$tag . "_attr"])) {
					$current[$tag]['0_attr'] = $current[$tag . "_attr"];
					unset($current[$tag . "_attr"]);
				}


				if ($attributes_data) {
					$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
				}
			}

			++$repeated_tag_index[$tag . "_" . $level];
			continue;
		}


		if ($type == "close") {
			$current = &$parent[$level - 1];

			continue;
		}
	}

	return $xml_array;
}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("RegSaveContactDetails")) {
	require ROOTDIR . "/includes/registrarfunctions.php";
}

$result = select_query("tbldomains", "id,domain,registrar,registrationperiod", array("id" => $domainid));
$data = mysql_fetch_array($result);
$domainid = $data[0];

if (!$domainid) {
	$apiresults = array("result" => "error", "message" => "Domain ID Not Found");
	return false;
}


if (!$xml) {
	$apiresults = array("result" => "error", "message" => "XML Required");
	return false;
}

$xml = html_entity_decode($xml);
$xmlarray = uwd_xml2array($xml);
foreach ($xmlarray as $type => $value) {

	if (is_array($value)) {
		foreach ($value as $type2 => $value2) {

			if (is_array($value2)) {
				foreach ($value2 as $type3 => $value3) {

					if (is_array($value3)) {
						foreach ($value3 as $type4 => $value4) {
							$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)][str_replace("_", " ", $type3)][str_replace("_", " ", $type4)] = $value4;
						}

						continue;
					}

					$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)][str_replace("_", " ", $type3)] = $value3;
				}

				continue;
			}

			$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)] = $value2;
		}

		continue;
	}

	$contact[str_replace("_", " ", $type)] = $value;
}

$id = $data['id'];
$domain = $data['domain'];
$registrar = $data['registrar'];
$regperiod = $data['registrationperiod'];
$domainparts = explode(".", $domain, 2);
$params = array();
$params['domainid'] = $id;
$params['sld'] = $domainparts[0];
$params['tld'] = $domainparts[1];
$params['regperiod'] = $regperiod;
$params['registrar'] = $registrar;
$params = array_merge($params, $contact);
$values = RegSaveContactDetails($params);

if ($values['error']) {
	$apiresults = array("result" => "error", "message" => "Registrar Error Message", "error" => $values['error']);
	return false;
}

$apiresults = array("result" => "success");
?>