<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

function getCustomFields($type, $relid, $relid2, $admin = "", $order = "", $ordervalues = "", $hidepw = "") {
	$customfields = $where = array();
	$where['type'] = $type;

	if ($relid) {
		$where['relid'] = $relid;
	}


	if (!$admin) {
		$where['adminonly'] = "";
	}


	if ($order) {
		$where['showorder'] = "on";
	}

	$result = select_query("tblcustomfields", "", $where, "sortorder` ASC,`id", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$fieldname = $data['fieldname'];

		if (strpos($fieldname, "|")) {
			$fieldname = explode("|", $fieldname);
			$fieldname = trim($fieldname[1]);
		}

		$fieldtype = $data['fieldtype'];
		$description = $data['description'];
		$fieldoptions = $data['fieldoptions'];
		$required = $data['required'];
		$adminonly = $data['adminonly'];
		$customfieldval = (is_array($ordervalues) ? $ordervalues[$id] : "");

		if ($relid2) {
			$customfieldval = get_query_val("tblcustomfieldsvalues", "value", array("fieldid" => $id, "relid" => $relid2));
		}

		$rawvalue = $customfieldval;

		if ($required == "on") {
			$required = "*";
		}


		if ($fieldtype == "text" || ($fieldtype == "password" && $admin)) {
			$input = ("<input type=\"text\" name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"30\" />";
		}
		else {
			if ($fieldtype == "link") {
				$webaddr = trim($customfieldval);

				if (substr($webaddr, 0, 4) == "www.") {
					$webaddr = "http://" . $webaddr;
				}

				$input = ("<input type=\"text\" name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"40\" /> " . ($customfieldval ? "<a href=\"" . $webaddr . "\" target=\"_blank\">www</a>" : "");
				$customfieldval = "<a href=\"" . $webaddr . "\" target=\"_blank\">" . $customfieldval . "</a>";
			}
			else {
				if ($fieldtype == "password") {
					$input = ("<input type=\"password\" name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"30\" />";

					if ($hidepw) {
						$pwlen = strlen($customfieldval);
						$customfieldval = "";
						$i = 1;

						while ($i <= $pwlen) {
							$customfieldval .= "*";
							++$i;
						}
					}
				}
				else {
					if ($fieldtype == "textarea") {
						$input = ("<textarea name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\" rows=\"3\" style=\"width:90%;\">" . $customfieldval . "</textarea>";
					}
					else {
						if ($fieldtype == "dropdown") {
							$input = ("<select name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\">";
							$fieldoptions = explode(",", $fieldoptions);
							foreach ($fieldoptions as $optionvalue) {
								$input .= ("<option value=\"" . $optionvalue . "\"");

								if ($customfieldval == $optionvalue) {
									$input .= " selected";
								}


								if (strpos($optionvalue, "|")) {
									$optionvalue = explode("|", $optionvalue);
									$optionvalue = trim($optionvalue[1]);
								}

								$input .= ">" . $optionvalue . "</option>";
							}

							$input .= "</select>";
						}
						else {
							if ($fieldtype == "tickbox") {
								$input = (("<input type=\"checkbox\" name=\"customfield[" . $id . "]") . "\" id=\"customfield" . $id . "\"");

								if ($customfieldval == "on") {
									$input .= " checked";
								}

								$input .= " />";
							}
						}
					}
				}
			}
		}


		if ($fieldtype != "link" && strpos($customfieldval, "|")) {
			$customfieldval = explode("|", $customfieldval);
			$customfieldval = trim($customfieldval[1]);
		}

		$customfields[] = array("id" => $id, "name" => $fieldname, "description" => $description, "type" => $fieldtype, "input" => $input, "value" => $customfieldval, "rawvalue" => $rawvalue, "required" => $required, "adminonly" => $adminonly);
	}

	return $customfields;
}

function saveCustomFields($relid, $customfields, $type = "") {
	if (is_array($customfields)) {
		foreach ($customfields as $id => $value) {

			if ($type) {
				$where = array("id" => $id, "type" => $type);
				$result = select_query("tblcustomfields", "", $where);
				$data = mysql_fetch_array($result);

				if (!$data['id']) {
					continue;
				}
			}

			$result = select_query("tblcustomfieldsvalues", "", array("fieldid" => $id, "relid" => $relid));
			$num_rows = mysql_num_rows($result);

			if ($num_rows == "0") {
				insert_query("tblcustomfieldsvalues", array("fieldid" => $id, "relid" => $relid, "value" => $value));
				continue;
			}

			update_query("tblcustomfieldsvalues", array("value" => $value), array("fieldid" => $id, "relid" => $relid));
		}
	}

}

function migrateCustomFieldsBetweenProducts($serviceid, $newpid, $save = false) {
	$customfieldsarray = array();
	$result = select_query("tblhosting", "packageid", array("id" => $serviceid));
	$data = mysql_fetch_array($result);
	$existingpid = $data[0];

	if ($save) {
		$customfields = getCustomFields("product", $existingpid, $serviceid, true);
		foreach ($customfields as $v) {
			$k = $v['id'];
			$customfieldsarray[$k] = $_POST['customfield'][$k];
		}

		saveCustomFields($serviceid, $customfieldsarray);
	}


	if ($existingpid != $newpid) {
		$customfields = getCustomFields("product", $existingpid, $serviceid, true);
		foreach ($customfields as $v) {
			$cfid = $v['id'];
			$cfname = $v['name'];
			$cfval = $v['rawvalue'];
			$customfieldsarray[$cfname] = $cfval;
			delete_query("tblcustomfieldsvalues", array("fieldid" => $cfid, "relid" => $serviceid));
		}

		$customfields = getCustomFields("product", $newpid, "", true);
		foreach ($customfields as $v) {
			$cfid = $v['id'];
			$cfname = $v['name'];

			if ($customfieldsarray[$cfname]) {
				insert_query("tblcustomfieldsvalues", array("fieldid" => $cfid, "relid" => $serviceid, "value" => $customfieldsarray[$cfname]));
				continue;
			}
		}
	}

}

?>