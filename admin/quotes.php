<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Manage Quotes");
$aInt->title = "Quotes";
$aInt->sidebar = "billing";
$aInt->icon = "quotes";
$aInt->requiredFiles(array("clientfunctions", "customfieldfunctions", "invoicefunctions", "quotefunctions", "configoptionsfunctions", "orderfunctions"));

if ($action == "getdesc") {
	$result = select_query("tblproducts", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$name = $data['name'];
	$description = $data['description'];
	echo $name . "\r\n" . $description;
	exit();
}


if ($action == "getprice") {
	$result = select_query("tblpricing", "", array("type" => "product", "currency" => $currency, "relid" => $id));
	$data = mysql_fetch_array($result);

	if (0 < $data['monthly']) {
		echo $data['monthly'];
	}
	else {
		if (0 < $data['quarterly']) {
			echo $data['quarterly'];
		}
		else {
			if (0 < $data['semiannually']) {
				echo $data['semiannually'];
			}
			else {
				if (0 < $data['annually']) {
					echo $data['annually'];
				}
				else {
					if (0 < $data['biennially']) {
						echo $data['biennially'];
					}
					else {
						if (0 < $data['triennially']) {
							echo $data['triennially'];
						}
						else {
							echo "0.00";
						}
					}
				}
			}
		}
	}

	exit();
}


if ($action == "getproddetails") {
	$currency = getCurrency("", $currency);
	$pricing = getPricingInfo($pid);

	if (!$billingcycle) {
		$billingcycle = $pricing['minprice']['cycle'];
	}

	echo "<input type=\"hidden\" name=\"billingcycle\" value=\"" . $billingcycle . "\" />";

	if ($pricing['type'] == "recurring") {
	}

	$configoptions = getCartConfigOptions($pid, "", $billingcycle);

	if (count($configoptions)) {
		echo "<p><b>Configurable Options</b></p>
<table>";
		foreach ($configoptions as $configoption) {
			$optionid = $configoption['id'];
			$optionhidden = $configoption['hidden'];
			$optionname = ($optionhidden ? $configoption['optionname'] . " <i>(" . $aInt->lang("global", "hidden") . ")</i>" : $configoption['optionname']);
			$optiontype = $configoption['optiontype'];
			$selectedvalue = $configoption['selectedvalue'];
			$selectedqty = $configoption['selectedqty'];
			echo "<tr><td class=\"fieldlabel\">" . $optionname . "</td><td class=\"fieldarea\">";

			if ($optiontype == "1") {
				echo ("<select name=\"configoption[" . $optionid . "]") . "\">";
				foreach ($configoption['options'] as $option) {
					echo "<option value=\"" . $option['id'] . "\"";

					if ($option['hidden']) {
						echo " style='color:#ccc;'";
					}


					if ($selectedvalue == $option['id']) {
						echo " selected";
					}

					echo ">" . $option['name'] . "</option>";
				}

				echo "</select>";
				continue;
			}


			if ($optiontype == "2") {
				foreach ($configoption['options'] as $option) {
					echo ("<input type=\"radio\" name=\"configoption[" . $optionid . "]") . "\" value=\"" . $option['id'] . "\"";

					if ($selectedvalue == $option['id']) {
						echo " checked";
					}


					if ($option['hidden']) {
						echo "> <span style='color:#ccc;'>" . $option['name'] . "</span><br />";
						continue;
					}

					echo "> " . $option['name'] . "<br />";
				}

				continue;
			}


			if ($optiontype == "3") {
				echo ("<input type=\"checkbox\" name=\"configoption[" . $optionid . "]") . "\" value=\"1\"";

				if ($selectedqty) {
					echo " checked";
				}

				echo "> " . $configoption['options'][0]['name'];
				continue;
			}


			if ($optiontype == "4") {
				echo ("<input type=\"text\" name=\"configoption[" . $optionid . "]") . "\" value=\"" . $selectedqty . "\" size=\"5\"> x " . $configoption['options'][0]['name'];
				continue;
			}
		}

		echo "</table>";
	}

	exit();
}


if ($action == "loadprod") {
	$result = select_query("tblquotes", "userid,currency", array("id" => $id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$currencyid = $data['currency'];
	$currency = getCurrency($userid, $currencyid);
	$aInt->title = "Load Product";
	$aInt->content = "<script>
$(document).ready(function(){
$(\"#addproduct\").change(function () {
    if (this.options[this.selectedIndex].value) {
        $(\"#add_desc\").val(this.options[this.selectedIndex].text);
        $.post(\"quotes.php\", { action: \"getproddetails\", currency: " . $currency['id'] . ", pid: this.options[this.selectedIndex].value },
        function(data){
            $(\"#configops\").html(data);
        });
    }
});
});
function selectproduct() {
    window.opener.location.href = \"quotes.php?action=addproduct&id=" . $id . "&\"+$(\"#addfrm\").serialize();
    window.close();
}
</script>
<form id=\"addfrm\" onsubmit=\"selectproduct();return false\">
<p><b>Product/Service</b></p><p><select name=\"pid\" id=\"addproduct\" style=\"width:95%;\"><option>Choose a product...</option>";
	$query = "SELECT tblproducts.id,tblproductgroups.name AS groupname,tblproducts.name AS productname FROM tblproducts INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid ORDER BY tblproductgroups.`order`,tblproducts.`order`,tblproducts.name ASC";
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		$productid = $data['id'];
		$groupname = $data['groupname'];
		$productname = $data['productname'];
		$aInt->content .= "<option value=\"" . $productid . "\">" . $groupname . " - " . $productname . "</option>";
	}

	$aInt->content .= "</select></p>
<div id=\"configops\"></div>
<p align=\"center\"><input type=\"submit\" value=\"Select\" /></p>
</form>";
	$aInt->displayPopUp();
	exit();
}


if ($action == "addproduct") {
	$result = select_query("tblquotes", "userid,currency", array("id" => $id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$currencyid = $data['currency'];
	$currency = getCurrency($userid, $currencyid);
	$result = select_query("tblproducts", "tblproducts.name,tblproducts.tax,tblproductgroups.name AS groupname", array("tblproducts.id" => $pid), "", "", "", "tblproductgroups ON tblproductgroups.id=tblproducts.gid");
	$data = mysql_fetch_array($result);
	$groupname = $data['groupname'];
	$prodname = $data['name'];
	$tax = $data['tax'];
	$desc = $groupname . " - " . $prodname;
	$pricing = getPricingInfo($pid);
	$billingcycle = $pricing['minprice']['cycle'];

	if ($billingcycle == "onetime") {
		$billingcycle = "monthly";
	}

	$amount = $pricing['rawpricing'][$billingcycle];
	$configoptions = getCartConfigOptions($pid, $configoption, $billingcycle);
	foreach ($configoptions as $option) {
		$desc .= "\r\n" . $option['optionname'] . ": " . $option['selectedname'];
		$amount += $option['selectedsetup'] + $option['selectedrecurring'];
	}

	insert_query("tblquoteitems", array("quoteid" => $id, "description" => $desc, "quantity" => "1", "unitprice" => $amount, "discount" => "0", "taxable" => $tax));
	saveQuote($id, "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", true);
	header("Location: quotes.php?action=manage&id=" . $id);
	exit();
}


if ($action == "save") {
	$lineitems = array();

	if ($desc) {
		foreach ($desc as $lid => $description) {
			$lineitems[] = array("id" => $lid, "desc" => $description, "qty" => $qty[$lid], "up" => $up[$lid], "discount" => $discount[$lid], "taxable" => $taxable[$lid]);
		}
	}


	if ($add_desc) {
		$lineitems[] = array("desc" => $add_desc, "qty" => $add_qty, "up" => $add_up, "discount" => $add_discount, "taxable" => $add_taxable);
	}

	$id = saveQuote($id, $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitems, $proposal, $customernotes, $adminnotes);
	logActivity("Modified Quote - Quote ID: " . $id, $userid);
	header("Location: quotes.php?action=manage&id=" . $id);
	exit();
}


if ($action == "duplicate") {
	$addstr = "";
	$result = select_query("tblquotes", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	foreach ($data as $key => $value) {

		if (is_numeric($key)) {
			if ($key == "0") {
				$value = "";
			}


			if ($key == "2") {
				$value = "Draft";
			}

			$addstr .= "'" . addslashes($value) . "',";
			continue;
		}
	}

	$addstr = substr($addstr, 0, 0 - 1);
	$query = "INSERT INTO tblquotes VALUES (" . $addstr . ")";
	full_query($query);
	$newquoteid = mysql_insert_id();
	$result = select_query("tblquoteitems", "", array("quoteid" => $id), "id", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$addstr = "";
		foreach ($data as $key => $value) {

			if (is_numeric($key)) {
				if ($key == "0") {
					$value = "";
				}


				if ($key == "1") {
					$value = $newquoteid;
				}

				$addstr .= "'" . addslashes($value) . "',";
				continue;
			}
		}

		$addstr = substr($addstr, 0, 0 - 1);
		$query = "INSERT INTO tblquoteitems VALUES (" . $addstr . ")";
		full_query($query);
	}

	header("Location: quotes.php?action=manage&id=" . $newquoteid . "&duplicated=true");
	exit();
}


if ($action == "delete") {
	delete_query("tblquotes", array("id" => $id));
	delete_query("tblquoteitems", array("quoteid" => $id));
	header("Location: quotes.php");
	exit();
}


if ($action == "deleteline") {
	delete_query("tblquoteitems", array("id" => $lid));
	saveQuote($id, $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitems, $proposal, $customernotes, $adminnotes, true);
	header("Location: quotes.php?action=manage&id=" . $id);
	exit();
}


if ($action == "dlpdf") {
	$pdfdata = genQuotePDF($id);
	header("Pragma: public");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
	header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"Quote" . $id . ".pdf\"");
	header("Content-Transfer-Encoding: binary");
	echo $pdfdata;
	exit();
}


if ($action == "sendpdf") {
	if (get_query_val("tblquotes", "datesent", array("id" => $id)) == "0000-00-00") {
		update_query("tblquotes", array("datesent" => "now()"), array("id" => $id));
	}

	sendQuotePDF($id);
	header("Location: quotes.php?action=manage&id=" . $id . "&sent=true");
	exit();
}


if ($action == "convert") {
	$invoiceid = convertQuotetoInvoice($id, $invoicetype, $invoiceduedate, $depositpercent, $depositduedate, $finalduedate, $sendemail);
	header("Location: invoices.php?action=edit&id=" . $invoiceid);
	exit();
}

ob_start();
$jscode = "function doDelete(id) {
if (confirm(\"Are you sure you want to delete this quote?\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id;
}}
function doDeleteLine(id) {
if (confirm(\"Are you sure you want to delete this line item?\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=deleteline&id=" . $id . "&lid='+id;
}}";

if (!$action) {
	echo $aInt->Tabs(array("Search/Filter"), true);
	echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
	echo $PHP_SELF;
	echo "\" method=\"get\"><input type=\"hidden\" name=\"filter\" value=\"true\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Subject</td><td class=\"fieldarea\"><input type=\"text\" name=\"subject\" size=\"50\" value=\"";
	echo $subject;
	echo "\"></td><td width=\"15%\" class=\"fieldlabel\">Client</td><td class=\"fieldarea\">";
	echo $aInt->clientsDropDown($userid, "", "", true);
	echo "</td></tr>
<tr><td class=\"fieldlabel\">Stage</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"stage\">
<option value=\"\">Any</option>
<option";

	if ($stage == "Draft") {
		echo " selected";
	}

	echo ">Draft</option>
<option";

	if ($stage == "Delivered") {
		echo " selected";
	}

	echo ">Delivered</option>
<option";

	if ($stage == "On Hold") {
		echo " selected";
	}

	echo ">On Hold</option>
<option";

	if ($stage == "Accepted") {
		echo " selected";
	}

	echo ">Accepted</option>
<option";

	if ($stage == "Lost") {
		echo " selected";
	}

	echo ">Lost</option>
<option";

	if ($stage == "Dead") {
		echo " selected";
	}

	echo ">Dead</option>
</select></td><td class=\"fieldlabel\">Validity Period</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"validity\"><option value=\"\">Any</option><option>Valid</option><option>Expired</option></select></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<DIV ALIGN=\"center\"><input type=\"submit\" value=\"Filter\" class=\"button\"></DIV>

</form>

  </div>
</div>

<br />

";
	$aInt->sortableTableInit("lastmodified", "DESC");
	$where = array();

	if ($stage) {
		$where['stage'] = $stage;
	}


	if ($validity == "Valid") {
		$where['validuntil'] = array("sqltype" => ">", "value" => date("Ymd"));
	}


	if ($validity == "Expired") {
		$where['validuntil'] = array("sqltype" => "<=", "value" => date("Ymd"));
	}


	if ($userid) {
		$where['userid'] = $userid;
	}


	if ($subject) {
		$where['subject'] = array("sqltype" => "LIKE", "value" => $subject);
	}

	$numresults = select_query("tblquotes", "", $where);
	$numrows = mysql_num_rows($numresults);
	$result = select_query("tblquotes", "", $where, $orderby, $order, $page * $limit . ("," . $limit));

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$subject = $data['subject'];
		$userid = $data['userid'];
		$firstname = $data['firstname'];
		$lastname = $data['lastname'];
		$companyname = $data['companyname'];
		$stage = $data['stage'];
		$total = $data['total'];
		$validuntil = $data['validuntil'];
		$lastmodified = $data['lastmodified'];
		$validuntil = fromMySQLDate($validuntil);
		$lastmodified = fromMySQLDate($lastmodified);

		if ($userid) {
			$clientlink = $aInt->outputClientLink($userid);
		}
		else {
			$clientlink = "" . $firstname . " " . $lastname;

			if ($companyname) {
				$clientlink .= " (" . $companyname . ")";
			}
		}

		$tabledata[] = array("<a href=\"quotes.php?action=manage&id=" . $id . "\">" . $id . "</a>", "<a href=\"quotes.php?action=manage&id=" . $id . "\">" . $subject . "</a>", $clientlink, $stage, $total, $validuntil, $lastmodified, "<a href=\"quotes.php?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	echo $aInt->sortableTable(array(array("id", "ID"), array("subject", "Subject"), "Client Name", array("stage", "Stage"), array("total", "Total"), array("validuntil", "Valid Until"), array("lastmodified", "Last Modified"), "", ""), $tabledata, $tableformurl, $tableformbuttons);
}
else {
	if ($action == "manage") {
		if ($id) {
			$addons_html = run_hook("AdminAreaViewQuotePage", array("quoteid" => $id));
			$result = select_query("tblquotes", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$subject = $data['subject'];
			$stage = $data['stage'];
			$datecreated = fromMySQLDate($data['datecreated']);
			$datesent = ($data['datesent'] != "0000-00-00" ? fromMySQLDate($data['datesent']) : "");
			$dateaccepted = ($data['dateaccepted'] != "0000-00-00" ? fromMySQLDate($data['dateaccepted']) : "");
			$validuntil = fromMySQLDate($data['validuntil']);
			$userid = $data['userid'];
			$proposal = $data['proposal'];
			$customernotes = $data['customernotes'];
			$adminnotes = $data['adminnotes'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$email = $data['email'];
			$address1 = $data['address1'];
			$address2 = $data['address2'];
			$city = $data['city'];
			$state = $data['state'];
			$postcode = $data['postcode'];
			$country = $data['country'];
			$phonenumber = $data['phonenumber'];
			$currencyid = $data['currency'];
			$currency = getCurrency($userid, $currencyid);
			$subtotal = $data['subtotal'];
			$tax1 = $data['tax1'];
			$tax2 = $data['tax2'];
			$total = $data['total'];

			if (!$userid) {
				$result = select_query("tblclients", "COUNT(*)", array("email" => $email));
				$data = mysql_fetch_array($result);
				$emailexists = $data[0];

				if ($emailexists) {
					infoBox("Email Address In Use", "The email address you have entered is already being used by another client so you should change it to an alternative");
				}
			}
		}
		else {
			$id = "";
			$datecreated = getTodaysDate();
			$validuntil = fromMySQLDate(date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"))));
			$clienttype = "existing";
		}


		if ($userid) {
			$clienttype = "existing";
			$clientsdetails = getClientsDetails($userid);
			$fortax_state = $clientsdetails['state'];
			$fortax_country = $clientsdetails['country'];
		}
		else {
			$clienttype = "new";
			$fortax_state = $state;
			$fortax_country = $country;
		}

		$taxlevel1 = getTaxRate(1, $fortax_state, $fortax_country);
		$taxlevel2 = getTaxRate(2, $fortax_state, $fortax_country);

		if ($duplicated) {
			infoBox("Quote Duplicated", "The quote was duplicated successfully - new quote #" . $id);
		}


		if ($sent) {
			infoBox("Quote Delivered", "The quote was successfully sent via email to the client");
		}

		echo $infobox;

		if (!$currency['id']) {
			$currency['id'] = 1;
		}

		$jquerycode = "$(\"#clienttypeexisting\").click(function () {
    $(\"#newclientform\").slideUp(\"slow\");
});
$(\"#clienttypenew\").click(function () {
    $(\"#newclientform\").slideDown(\"slow\");
});
$(\"#userdropdown\").change(function () {
    $(\"#clienttypeexisting\").click();
});
$(\"#addproduct\").change(function () {
    if (this.options[this.selectedIndex].value) {
        $.post(\"quotes.php\", { action: \"getdesc\", id: this.options[this.selectedIndex].value },
        function(data){
            $(\"#add_desc\").val(data);
        });
        $.post(\"quotes.php\", { action: \"getprice\", currency: " . $currency['id'] . ", id: this.options[this.selectedIndex].value },
        function(data){
            $(\"#add_up\").val(data);
        });
    }
});
$(\"textarea.expanding\").autogrow({
    minHeight: 16,
    lineHeight: 14
});";
		$jscode .= "function selectSingle() {
    $(\"#singleoptions\").slideToggle();
    $(\"#depositoptions\").slideToggle();
}
function selectDeposit() {
    $(\"#singleoptions\").slideToggle();
    $(\"#depositoptions\").slideToggle();
}";
		foreach ($addons_html as $addon_html) {
			echo "<div style=\"margin-bottom:15px;\">" . $addon_html . "</div>";
		}

		echo "
<form method=\"post\" action=\"";
		echo $_SERVER['PHP_SELF'];
		echo "\">
<input type=\"hidden\" name=\"action\" value=\"save\" />
";

		if ($id) {
			echo "<input type=\"hidden\" name=\"id\" value=\"";
			echo $id;
			echo "\" />";
		}

		echo "
<h2>General Information</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Subject</td><td class=\"fieldarea\"><input type=\"text\" name=\"subject\" size=\"70\" value=\"";
		echo $subject;
		echo "\"></td><td width=\"15%\" class=\"fieldlabel\">Stage</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"stage\">
<option";

		if ($stage == "Draft") {
			echo " selected";
		}

		echo ">Draft</option>
<option";

		if ($stage == "Delivered") {
			echo " selected";
		}

		echo ">Delivered</option>
<option";

		if ($stage == "On Hold") {
			echo " selected";
		}

		echo ">On Hold</option>
<option";

		if ($stage == "Accepted") {
			echo " selected";
		}

		echo ">Accepted</option>
<option";

		if ($stage == "Lost") {
			echo " selected";
		}

		echo ">Lost</option>
<option";

		if ($stage == "Dead") {
			echo " selected";
		}

		echo ">Dead</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">Date Created</td><td class=\"fieldarea\"><input type=\"text\" name=\"datecreated\" size=\"15\" value=\"";
		echo $datecreated;
		echo "\" class=\"datepick\"></td><td class=\"fieldlabel\">Valid Until</td><td class=\"fieldarea\"><input type=\"text\" name=\"validuntil\" size=\"15\" value=\"";
		echo $validuntil;
		echo "\" class=\"datepick\"></td></tr>
";

		if ($datesent || $dateaccepted) {
			echo "<tr>";

			if ($datesent) {
				echo "<td class=\"fieldlabel\">Date Sent</td><td class=\"fieldarea\">";
				echo $datesent;
				echo "</td>";
			}


			if ($dateaccepted) {
				echo "<td class=\"fieldlabel\">Date Accepted</td><td class=\"fieldarea\">";
				echo $dateaccepted;
				echo "</td>";
			}

			echo "</tr>
";
		}

		echo "</table>

<p align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"btn-primary\" /> <input type=\"button\" value=\"Duplicate\" class=\"button\" onclick=\"window.location='quotes.php?action=duplicate&id=";
		echo $id;
		echo "'\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Printable Version\" class=\"button\" onclick=\"window.open('../viewquote.php?id=";
		echo $id;
		echo "','windowfrm','menubar=yes,toolbar=yes,scrollbars=yes,resizable=yes,width=750,height=600')\" \"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"View PDF\" class=\"button\" onclick=\"window.open('../dl.php?type=q&id=";
		echo $id;
		echo "&viewpdf=1','pdfquote','')\" /> <input type=\"button\" value=\"Download PDF\" class=\"button\" onclick=\"window.location='";
		echo $_SERVER['PHP_SELF'];
		echo "?action=dlpdf&id=";
		echo $id;
		echo "';\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Email as PDF\" class=\"button\" onclick=\"window.location='quotes.php?action=sendpdf&id=";
		echo $id;
		echo "';\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Convert to Invoice\" onClick=\"showDialog('quoteconvert')\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Delete\" class=\"btn warn\" onclick=\"doDelete('";
		echo $id;
		echo "');\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /></p>

<h2>Client Information</h2>

<p><input type=\"radio\" name=\"clienttype\" value=\"existing\" id=\"clienttypeexisting\"";

		if ($clienttype == "existing") {
			echo " checked";
		}

		echo " /> <label for=\"clienttypeexisting\">Quote for existing client:</label> ";
		echo str_replace("<select", "<select id=\"userdropdown\"", $aInt->clientsDropDown($userid));
		echo " ";

		if ($clienttype == "existing") {
			echo " <a href=\"clientssummary.php?userid=" . $userid . "\" target=\"_blank\">View Client Profile</a>";
		}

		echo "<br /><input type=\"radio\" name=\"clienttype\" value=\"new\" id=\"clienttypenew\"";

		if ($clienttype == "new") {
			echo " checked";
		}

		echo " /> <label for=\"clienttypenew\">Quote for new client</label></p>

<div id=\"newclientform\"";

		if ($clienttype == "existing") {
			echo " style=\"display:none;\"";
		}

		echo ">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">First Name</td><td class=\"fieldarea\"><input type=\"text\" name=\"firstname\" size=\"30\" value=\"";
		echo $firstname;
		echo "\"></td><td width=\"15%\" class=\"fieldlabel\">Address 1</td><td class=\"fieldarea\"><input type=\"text\" name=\"address1\" size=\"30\" value=\"";
		echo $address1;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Last Name</td><td class=\"fieldarea\"><input type=\"text\" name=\"lastname\" size=\"30\" value=\"";
		echo $lastname;
		echo "\"></td><td class=\"fieldlabel\">Address 2</td><td class=\"fieldarea\"><input type=\"text\" name=\"address2\" size=\"30\" value=\"";
		echo $address2;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Company Name</td><td class=\"fieldarea\"><input type=\"text\" name=\"companyname\" size=\"30\" value=\"";
		echo $companyname;
		echo "\"></td><td class=\"fieldlabel\">City</td><td class=\"fieldarea\"><input type=\"text\" name=\"city\" size=\"30\" value=\"";
		echo $city;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Email Address</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"30\" value=\"";
		echo $email;
		echo "\"></td><td class=\"fieldlabel\">State</td><td class=\"fieldarea\"><input type=\"text\" name=\"state\" size=\"30\" value=\"";
		echo $state;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Phone Number</td><td class=\"fieldarea\"><input type=\"text\" name=\"phonenumber\" size=\"30\" value=\"";
		echo $phonenumber;
		echo "\"></td><td class=\"fieldlabel\">Postcode</td><td class=\"fieldarea\"><input type=\"text\" name=\"postcode\" size=\"30\" value=\"";
		echo $postcode;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Currency</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"currency\">";
		$result = select_query("tblcurrencies", "id,code,`default`", "", "code", "ASC");

		while ($data = mysql_fetch_array($result)) {
			echo "<option value=\"" . $data['id'] . "\"";

			if (($currencyid && $data['id'] == $currencyid) || (!$currencyid && $data['default'])) {
				echo " selected";
			}

			echo ">" . $data['code'] . "</option>";
		}

		echo "</select></td><td class=\"fieldlabel\">Country</td><td class=\"fieldarea\">";
		include "../includes/countries.php";
		echo getCountriesDropDown($country);
		echo "</td></tr>
</table>
</div>

<h2>Line Items</h2>

";
		echo "<s";
		echo "cript type=\"text/javascript\" src=\"../includes/jscript/jqueryag.js\"></script>

<table width=100% cellspacing=1 bgcolor=\"#cccccc\" align=center><tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td width=\"50\">Qty</td><td>Description</td><td width=90>Unit Price</td><td width=90>Discount %</td><td width=90>Total</td><td width=50>Taxed</td><td width=20></td></tr>
";

		if ($id) {
			$result = select_query("tblquoteitems", "", array("quoteid" => $id), "id", "ASC");
			$i = 0;

			while ($data = mysql_fetch_array($result)) {
				$line_id = $data['id'];
				$line_desc = $data['description'];
				$line_qty = $data['quantity'];
				$line_unitprice = $data['unitprice'];
				$line_discount = $data['discount'];
				$line_taxable = $data['taxable'];
				$line_total = formatCurrency($line_qty * $line_unitprice * (1 - $line_discount / 100));
				echo ((((("<tr bgcolor=#ffffff style=\"text-align:center;\"><td><input type=\"text\" name=\"qty[" . $line_id . "]") . "\" size=\"4\" value=\"" . $line_qty . "\"></td><td><textarea name=\"desc[" . $line_id . "]") . "\" class=\"expanding\" style=\"width:98%\">" . $line_desc . "</textarea></td><td><input type=\"text\" name=\"up[" . $line_id . "]") . "\" size=\"10\" value=\"" . $line_unitprice . "\"></td><td><input type=\"text\" name=\"discount[" . $line_id . "]") . "\" size=\"10\" value=\"" . $line_discount . "\"></td><td>" . $CONFIG['CurrencySymbol'] . $line_total . "</td><td><input type=\"checkbox\" name=\"taxable[" . $line_id . "]") . "\" value=\"1\"";

				if ($line_taxable) {
					echo " checked";
				}

				echo "></td><td width=20 align=center><a href=\"#\" onClick=\"doDeleteLine('" . $line_id . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></tr>";
				++$i;
			}
		}

		echo "<tr bgcolor=#ffffff style=\"text-align:center;\"><td><input type=\"text\" name=\"add_qty\" size=\"4\" value=\"1\"></td><td><textarea name=\"add_desc\" id=\"add_desc\" class=\"expanding\" style=\"width:98%\"></textarea></td><td><input type=\"text\" name=\"add_up\" id=\"add_up\" size=\"10\" value=\"0.00\"></td><td><input type=\"text\" name=\"add_discount\" size=\"10\" value=\"0.00\"></td><td></td><td><input type=\"checkbox\" name=\"add_taxable\" value=\"1\" ";
		echo "/></td><td></td></tr>
<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td colspan=\"4\"><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td style=\"text-align:left;font-weight:normal;\"><a href=\"#\" onclick=\"";
		$aInt->popupWindow($_SERVER['PHP_SELF'] . "?action=loadprod&id=" . $id);
		echo "\"><img src=\"images/icons/add.png\" border=\"0\" align=\"absmiddle\" /> Add a Predefined Product</a></td><td align=\"right\">Sub Total:&nbsp;</td></tr></table></td><td width=90>";
		echo formatCurrency($subtotal);
		echo "</td><td></td><td></td></tr>
";

		if ($CONFIG['TaxEnabled'] == "on") {
			if (0 < $taxlevel1['rate']) {
				echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td colspan=\"4\" align=\"right\">";
				echo $taxlevel1['name'];
				echo " @ ";
				echo $taxlevel1['rate'];
				echo "%:&nbsp;</td><td width=90>";
				echo formatCurrency($tax1);
				echo "</td><td></td><td></td></tr>";
			}


			if (0 < $taxlevel2['rate']) {
				echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td colspan=\"4\" align=\"right\">";
				echo $taxlevel2['name'];
				echo " @ ";
				echo $taxlevel2['rate'];
				echo "%:&nbsp;</td><td width=90>";
				echo formatCurrency($tax2);
				echo "</td><td></td><td></td></tr>";
			}
		}

		echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td colspan=\"4\" align=\"right\">Total Due:&nbsp;</td><td width=90>";
		echo formatCurrency($total);
		echo "</td><td></td><td></td></tr>
</table>

<h2>Notes</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">Proposal Text<br />(Displayed at the Top of the Quote)</td><td class=\"fieldarea\"><textarea name=\"proposal\" rows=\"5\" style=\"width:98%\">";
		echo $proposal;
		echo "</textarea></td></tr>
<tr><td width=\"15%\" class=\"fieldlabel\">Customer Notes<br />(Displayed as a Footer to the Quote)</td><td class=\"fieldarea\"><textarea name=\"customernotes\" rows=\"5\" style=\"width:98%\">";
		echo $customernotes;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">Admin Only Notes<br />(Private Notes)</td><td class=\"fieldarea\"><textarea name=\"adminnotes\" rows=\"5\" style=\"width:98%\">";
		echo $adminnotes;
		echo "</textarea></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"btn-primary\" /> <input type=\"button\" value=\"Duplicate\" class=\"button\" onclick=\"window.location='quotes.php?action=duplicate&id=";
		echo $id;
		echo "'\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Printable Version\" class=\"button\" onclick=\"window.open('../viewquote.php?id=";
		echo $id;
		echo "','windowfrm','menubar=yes,toolbar=yes,scrollbars=yes,resizable=yes,width=750,height=600')\" \"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"View PDF\" class=\"button\" onclick=\"window.open('../dl.php?type=q&id=";
		echo $id;
		echo "&viewpdf=1','pdfquote','')\" /> <input type=\"button\" value=\"Download PDF\" class=\"button\" onclick=\"window.location='";
		echo $_SERVER['PHP_SELF'];
		echo "?action=dlpdf&id=";
		echo $id;
		echo "';\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Email as PDF\" class=\"button\" onclick=\"window.location='quotes.php?action=sendpdf&id=";
		echo $id;
		echo "';\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Convert to Invoice\" onClick=\"showDialog('quoteconvert')\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /> <input type=\"button\" value=\"Delete\" class=\"btn warn\" onclick=\"doDelete('";
		echo $id;
		echo "');\"";

		if (!$id) {
			echo " disabled=\"true\"";
		}

		echo " /></p>

</form>

";
		$content = "<form id=\"convertquotefrm\">
<label><input type=\"radio\" name=\"invoicetype\" value=\"single\" onclick=\"selectSingle()\" checked /> Generate a single invoice for the entire amount</label><br />
<div id=\"singleoptions\" align=\"center\">
<br />
Due Date of Invoice: <input type=\"text\" name=\"invoiceduedate\" value=\"" . getTodaysDate() . "\" class=\"datepick\" />
<br /><br />
</div>
<label><input type=\"radio\" name=\"invoicetype\" value=\"deposit\" onclick=\"selectDeposit()\" /> Split into 2 invoices - a deposit and final payment</label><br />
<div id=\"depositoptions\" align=\"center\" style=\"display:none;\">
<br />
Enter Deposit Percentage: <input type=\"text\" name=\"depositpercent\" value=\"50\" size=\"5\" />%<br />
Due Date of Deposit: <input type=\"text\" name=\"depositduedate\" value=\"" . getTodaysDate() . "\" class=\"datepick\" /><br />
Due Date of Final Payment: <input type=\"text\" name=\"finalduedate\" value=\"" . fromMySQLDate(date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d"), date("Y")))) . "\" class=\"datepick\" />
</div>
<br />
<label><input type=\"checkbox\" name=\"sendemail\" checked /> Send Invoice Notification Email</label>
</form>";
		echo $aInt->jqueryDialog("quoteconvert", "Convert to Invoice", $content, array("Submit" => "window.location='" . $PHP_SELF . "?action=convert&id=" . $id . ("&'+$('#convertquotefrm').serialize();")), "", "500", "");
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>