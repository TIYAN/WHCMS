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

define("ADMINAREA", true);
require "../init.php";
include "../includes/additionaldomainfields.php";
$aInt = new WHMCS_Admin("Add New Order", false);
$aInt->title = $aInt->lang("orders", "addnew");
$aInt->sidebar = "orders";
$aInt->icon = "orders";
$aInt->requiredFiles(array("orderfunctions", "domainfunctions", "whoisfunctions", "configoptionsfunctions", "customfieldfunctions", "clientfunctions", "invoicefunctions", "processinvoices", "gatewayfunctions", "fraudfunctions", "modulefunctions", "cartfunctions"));
$action = $whmcs->get_req_var("action");
$userid = $whmcs->get_req_var("userid");
$currency = getCurrency($userid);

if ($action == "createpromo") {
	check_token("WHMCS.admin.default");

	if (!$code) {
		exit("Promotion Code is Required");
	}


	if ($pvalue <= 0) {
		exit("Promotion Value must be greater than zero");
	}

	$result = select_query("tblpromotions", "COUNT(*)", array("code" => $code));
	$data = mysql_fetch_array($result);
	$duplicates = $data[0];

	if ($duplicates) {
		exit("Promotion Code already exists. Please try another.");
	}

	$promoid = insert_query("tblpromotions", array("code" => $code, "type" => $type, "recurring" => $recurring, "value" => $pvalue, "maxuses" => "1", "recurfor" => $recurfor, "expirationdate" => "0000-00-00", "notes" => "Order Process One Off Custom Promo"));
	$promo_type = $type;
	$promo_value = $pvalue;
	$promo_recurring = $recurring;
	$promo_code = $code;

	if ($promo_type == "Percentage") {
		$promo_value .= "%";
	}
	else {
		$promo_value = formatCurrency($promo_value);
	}

	$promo_recurring = ($promo_recurring ? "Recurring" : "One Time");
	echo "<option value=\"" . $promo_code . "\">" . $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
	exit();
}


if ($action == "getconfigoptions") {
	check_token("WHMCS.admin.default");
	releaseSession();

	if (!trim($pid)) {
		exit();
	}

	$options = "";
	$configoptions = getCartConfigOptions($pid, "", $cycle);

	if (count($configoptions)) {
		$options .= "<p><b>" . $aInt->lang("setup", "configoptions") . "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">";
		foreach ($configoptions as $configoption) {
			$options .= "<tr><td width=\"130\" class=\"fieldlabel\">" . $configoption['optionname'] . "</td><td class=\"fieldarea\">";

			if ($configoption['optiontype'] == "1") {
				$options .= "<select onchange=\"updatesummary()\" name=\"configoption[" . $orderid . "][" . $configoption['id'] . "]\">";
				foreach ($configoption['options'] as $optiondata) {
					$options .= "<option value=\"" . $optiondata['id'] . "\"";

					if ($optiondata['id'] == $configoption['selectedvalue']) {
						$options .= " selected";
					}

					$options .= ">" . $optiondata['name'] . "</option>";
				}

				$options .= "</select>";
			}
			else {
				if ($configoption['optiontype'] == "2") {
					foreach ($configoption['options'] as $optiondata) {
						$options .= "<input type=\"radio\" onclick=\"updatesummary()\" name=\"configoption[" . $orderid . "][" . $configoption['id'] . "]\" value=\"" . $optiondata['id'] . "\"";

						if ($optiondata['id'] == $configoption['selectedvalue']) {
							$options .= " checked=\"checked\"";
						}

						$options .= "> " . $optiondata['name'] . "<br />";
					}
				}
				else {
					if ($configoption['optiontype'] == "3") {
						$options .= "<input type=\"checkbox\" onclick=\"updatesummary()\" name=\"configoption[" . $orderid . "][" . $configoption['id'] . "]\" value=\"1\"";

						if ($configoption['selectedqty']) {
							$options .= " checked=\"checked\"";
						}

						$options .= "> " . $configoption['options'][0]['name'];
					}
					else {
						if ($configoption['optiontype'] == "4") {
							$options .= "<input type=\"text\" onclick=\"updatesummary()\" name=\"configoption[" . $orderid . "][" . $configoption['id'] . "]\" value=\"" . $configoption['selectedqty'] . "\" size=\"5\"> x " . $configoption['options'][0]['name'];
						}
					}
				}
			}

			$options .= "</td></tr>";
		}

		$options .= "</table>";
	}

	$customfields = getCustomFields("product", $pid, "", "", "on", $customfields);

	if (count($customfields)) {
		$options .= "<p><b>" . $aInt->lang("setup", "customfields") . "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">";
		foreach ($customfields as $customfield) {
			$inputfield = str_replace("name=\"customfield", "name=\"customfield[" . $orderid . "]", $customfield['input']);
			$options .= "<tr><td width=\"130\" class=\"fieldlabel\">" . $customfield['name'] . "</td><td class=\"fieldarea\">" . $inputfield . "</td></tr>";
		}

		$options .= "</table>";
	}

	$addonshtml = "";
	$addonsarray = getAddons($pid);

	if (count($addonsarray)) {
		foreach ($addonsarray as $addon) {
			$addonshtml .= "<label>" . str_replace("<input type=\"checkbox\" name=\"addons", "<input type=\"checkbox\" onclick=\"updatesummary()\" name=\"addons[" . $orderid . "]", $addon['checkbox']) . " " . $addon['name'] . " (" . $addon['pricing'] . ")";

			if ($addon['description']) {
				$addonshtml .= " - " . $addon['description'];
			}

			$addonshtml .= "</label><br />";
		}
	}

	echo json_encode(array("options" => $options, "addons" => $addonshtml));
	exit();
}


if ($action == "getdomainaddlfields") {
	check_token("WHMCS.admin.default");
	$domainparts = explode(".", $domain, 2);
	$tempdomainfields = $additionaldomainfields["." . $domainparts[1]];
	$addlfieldscode = "";

	if ($tempdomainfields) {
		foreach ($tempdomainfields as $key => $values) {

			if ($values['Name']) {
				$addlfieldscode .= "<tr class=\"domainaddlfields" . $order . "\"><td width=\"130\" class=\"fieldlabel\">" . $values['Name'] . "</td><td class=\"fieldarea\">";

				if ($values['Type'] == "dropdown") {
					$addlfieldscode .= "<select name=\"domflds[" . $order . "][" . $key . "]\">";
					foreach (explode(",", $values['Options']) as $option) {
						$addlfieldscode .= "<option value=\"" . $option . "\">" . $option . "</option>";
					}

					$addlfieldscode .= "</select>";
				}


				if ($values['Type'] == "text") {
					$addlfieldscode .= "<input type=\"text\" name=\"domflds[" . $order . "][" . $key . "]\" />";
				}


				if ($values['Type'] == "tickbox") {
					$addlfieldscode .= "<input type=\"checkbox\" name=\"domflds[" . $order . "][" . $key . "]\" /> " . $values['Description'];
				}


				if ($values['Type'] == "radio") {
					foreach (explode(",", $values['Options']) as $option) {
						$addlfieldscode .= "<input type=\"radio\" name=\"domflds[" . $order . "][" . $key . "]\" value=\"" . $option . "\" /> " . $option . "<br />";
					}
				}

				$addlfieldscode .= "</td></tr>";
				continue;
			}
		}
	}

	echo $addlfieldscode;
	exit();
}


if ($whmcs->get_req_var("submitorder")) {
	check_token("WHMCS.admin.default");
	$userid = get_query_val("tblclients", "id", array("id" => $userid));

	if (!$userid && !$calconly) {
		infoBox("Invalid Client ID", "Please enter or select a valid client to add the order to");
	}
	else {
		$_SESSION['uid'] = $userid;
		getUsersLang($userid);
		$_SESSION['cart'] = array();
		$_SESSION['cart']['paymentmethod'] = $paymentmethod;
		foreach ($pid as $k => $prodid) {

			if ($prodid) {
				$addons[$k] = array_keys($addons[$k]);

				if (!$qty[$k]) {
					$qty[$k] = 1;
				}

				$productarray = array("pid" => $prodid, "domain" => $domain[$k], "billingcycle" => str_replace(array("-", " "), "", strtolower($billingcycle[$k])), "server" => "", "configoptions" => $configoption[$k], "customfields" => $customfield[$k], "addons" => $addons[$k]);

				if (strlen($_POST['priceoverride'][$k])) {
					$productarray['priceoverride'] = $_POST['priceoverride'][$k];
				}

				$count = 1;

				while ($count <= $qty[$k]) {
					$_SESSION['cart']['products'][] = $productarray;
					++$count;
				}

				continue;
			}
		}

		$validtlds = array();
		$result = select_query("tbldomainpricing", "extension", "");

		while ($data = mysql_fetch_array($result)) {
			$validtlds[] = $data[0];
		}

		foreach ($regaction as $k => $regact) {
			$domainparts = explode(".", $regdomain[$k], 2);

			if ($regact && in_array("." . $domainparts[1], $validtlds)) {
				$_SESSION['cart']['domains'][] = array("type" => $regact, "domain" => $regdomain[$k], "regperiod" => $regperiod[$k], "dnsmanagement" => $dnsmanagement[$k], "emailforwarding" => $emailforwarding[$k], "idprotection" => $idprotection[$k], "eppcode" => $eppcode[$k], "fields" => $domflds[$k]);
				continue;
			}
		}


		if ($promocode) {
			$_SESSION['cart']['promo'] = $promocode;
		}

		$_SESSION['cart']['orderconfdisabled'] = ($adminorderconf ? false : true);
		$_SESSION['cart']['geninvoicedisabled'] = ($admingenerateinvoice ? false : true);

		if (!$adminsendinvoice) {
			$CONFIG['NoInvoiceEmailOnOrder'] = true;
		}


		if ($calconly) {
			$ordervals = calcCartTotals();
			echo "<div class=\"ordersummarytitle\">Order Summary</div>
<div id=\"ordersummary\">
<table>
";

			if (is_array($ordervals['products'])) {
				foreach ($ordervals['products'] as $cartprod) {
					echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . $cartprod['productinfo']['groupname'] . " - " . $cartprod['productinfo']['name'] . "</div>";
					echo $aInt->lang("billingcycles", $cartprod['billingcycle']);

					if ($cartprod['domain']) {
						echo " - " . $cartprod['domain'];
					}

					echo "<div class=\"itempricing\">";

					if ($cartprod['priceoverride']) {
						echo formatCurrency($cartprod['priceoverride']) . "*";
					}
					else {
						echo $cartprod['pricingtext'];
					}

					echo "</div>";

					if ($cartprod['configoptions']) {
						foreach ($cartprod['configoptions'] as $cartcoption) {

							if ($cartcoption['type'] == "1" || $cartcoption['type'] == "2") {
								echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption['name'] . ": " . $cartcoption['value'];
								continue;
							}


							if ($cartcoption['type'] == "3") {
								echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption['name'] . ": ";

								if ($cartcoption['qty']) {
									echo $aInt->lang("global", "yes");
									continue;
								}

								echo $aInt->lang("global", "no");
								continue;
							}


							if ($cartcoption['type'] == "4") {
								echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption['name'] . ": " . $cartcoption['qty'] . " x " . $cartcoption['option'];
								continue;
							}
						}
					}

					echo "</td></tr>";

					if ($cartprod['addons']) {
						foreach ($cartprod['addons'] as $addondata) {
							echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . $addondata['name'] . "</div><div class=\"itempricing\">" . $addondata['pricingtext'] . "</div></td></tr>";
						}

						continue;
					}
				}
			}


			if (is_array($ordervals['domains'])) {
				foreach ($ordervals['domains'] as $cartdom) {
					echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . $aInt->lang("fields", "domain") . " " . $aInt->lang("domains", $cartdom['type']) . "</div>" . $cartdom['domain'] . " (" . $cartdom['regperiod'] . " " . $aInt->lang("domains", "years") . ")";

					if ($cartdom['dnsmanagement']) {
						echo "<br />&nbsp;&raquo;&nbsp;" . $aInt->lang("domains", "dnsmanagement");
					}


					if ($cartdom['emailforwarding']) {
						echo "<br />&nbsp;&raquo;&nbsp;" . $aInt->lang("domains", "emailforwarding");
					}


					if ($cartdom['idprotection']) {
						echo "<br />&nbsp;&raquo;&nbsp;" . $aInt->lang("domains", "idprotection");
					}

					echo "<div class=\"itempricing\">";

					if ($cartdom['priceoverride']) {
						echo formatCurrency($cartdom['priceoverride']) . "*";
					}
					else {
						echo $cartdom['price'];
					}

					echo "</div>";
				}
			}

			$cartitems = 0;
			foreach (array("products", "addons", "domains", "renewals") as $k) {

				if (array_key_exists($k, $ordervals)) {
					$cartitems += count($ordervals[$k]);
					continue;
				}
			}


			if (!$cartitems) {
				echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\" align=\"center\">No Items Selected</div></td></tr>";
			}

			echo "<tr class=\"subtotal\"><td>Subtotal</td><td class=\"alnright\">" . $ordervals['subtotal'] . "</td></tr>";

			if ($ordervals['promotype']) {
				echo "<tr class=\"promo\"><td>Promo Discount</td><td class=\"alnright\">" . $ordervals['discount'] . "</td></tr>";
			}


			if ($ordervals['taxrate']) {
				echo "<tr class=\"tax\"><td>" . $ordervals['taxname'] . " @ " . $ordervals['taxrate'] . "%</td><td class=\"alnright\">" . $ordervals['taxtotal'] . "</td></tr>";
			}


			if ($ordervals['taxrate2']) {
				echo "<tr class=\"tax\"><td>" . $ordervals['taxname2'] . " @ " . $ordervals['taxrate2'] . "%</td><td class=\"alnright\">" . $ordervals['taxtotal2'] . "</td></tr>";
			}

			echo "<tr class=\"total\"><td width=\"140\">Total</td><td class=\"alnright\">" . $ordervals['total'] . "</td></tr>";

			if ((((($ordervals['totalrecurringmonthly'] || $ordervals['totalrecurringquarterly']) || $ordervals['totalrecurringsemiannually']) || $ordervals['totalrecurringannually']) || $ordervals['totalrecurringbiennially']) || $ordervals['totalrecurringtriennially']) {
				echo "<tr class=\"recurring\"><td>Recurring</td><td class=\"alnright\">";

				if ($ordervals['totalrecurringmonthly']) {
					echo "" . $ordervals['totalrecurringmonthly'] . " Monthly<br />";
				}


				if ($ordervals['totalrecurringquarterly']) {
					echo "" . $ordervals['totalrecurringquarterly'] . " Quarterly<br />";
				}


				if ($ordervals['totalrecurringsemiannually']) {
					echo "" . $ordervals['totalrecurringsemiannually'] . " Semi-Annually<br />";
				}


				if ($ordervals['totalrecurringannually']) {
					echo "" . $ordervals['totalrecurringannually'] . " Annually<br />";
				}


				if ($ordervals['totalrecurringbiennially']) {
					echo "" . $ordervals['totalrecurringbiennially'] . " Biennially<br />";
				}


				if ($ordervals['totalrecurringtriennially']) {
					echo "" . $ordervals['totalrecurringtriennially'] . " Triennially<br />";
				}

				echo "</td></tr>";
			}

			echo "</table>
</div>";
			exit();
		}

		$cartitems = count($_SESSION['cart']['products']) + count($_SESSION['cart']['addons']) + count($_SESSION['cart']['domains']) + count($_SESSION['cart']['renewals']);

		if (!$cartitems) {
			redir("noselections=1");
		}

		calcCartTotals(true);
		unset($_SESSION['uid']);

		if ($orderstatus == "Active") {
			update_query("tblorders", array("status" => "Active"), array("id" => $_SESSION['orderdetails']['OrderID']));

			if (is_array($_SESSION['orderdetails']['Products'])) {
				foreach ($_SESSION['orderdetails']['Products'] as $productid) {
					update_query("tblhosting", array("domainstatus" => "Active"), array("id" => $productid));
				}
			}


			if (is_array($_SESSION['orderdetails']['Domains'])) {
				foreach ($_SESSION['orderdetails']['Domains'] as $domainid) {
					update_query("tbldomains", array("status" => "Active"), array("id" => $domainid));
				}
			}
		}

		getUsersLang(0);
		redir("action=view&id=" . $_SESSION['orderdetails']['OrderID'], "orders.php");
		exit();
	}
}

releaseSession();
$regperiods = $regperiodss = "";
$regperiod = 1;

while ($regperiod <= 10) {
	$regperiods .= "<option value=\"" . $regperiod . "\">" . $regperiod . " " . $aInt->lang("domains", "year" . $regperiodss) . "</option>";
	$regperiodss = "s";
	++$regperiod;
}

$jquerycode = "
$(function(){
    var prodtemplate = $(\"#products .product:first\").clone();
    var productsCount = 0;
    window.addProduct = function(){
        productsCount++;
        var order = prodtemplate.clone().find(\"*\").each(function(){
            var newId = this.id.substring(0, this.id.length-1) + productsCount;

            $(this).prev().attr(\"for\", newId); // update label for
            this.id = newId; // update id

        }).end()
        .attr(\"id\", \"ord\" + productsCount)
        .appendTo(\"#products\");
        return false;
    }
    $(\".addproduct\").click(addProduct);

    var domainsCount = 0;
    window.addDomain = function(){
        domainsCount++;
        $('<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" style=\"margin-top:10px;\"><tr><td width=\"130\" class=\"fieldlabel\">" . $aInt->lang("domains", "regtype", 1) . "</td><td class=\"fieldarea\"><input type=\"radio\" name=\"regaction['+domainsCount+']\" id=\"domnon'+domainsCount+'\" value=\"\" onclick=\"loaddomainoptions(this,0);updatesummary()\" checked /> <label for=\"domnon'+domainsCount+'\">" . $aInt->lang("global", "none", 1) . "</label> <input type=\"radio\" name=\"regaction['+domainsCount+']\" value=\"register\" id=\"domreg'+domainsCount+'\" onclick=\"loaddomainoptions(this,1);updatesummary()\" /> <label for=\"domreg'+domainsCount+'\">" . $aInt->lang("domains", "register", 1) . "</label> <input type=\"radio\" name=\"regaction['+domainsCount+']\" value=\"transfer\" id=\"domtrf'+domainsCount+'\" onclick=\"loaddomainoptions(this,2);updatesummary()\" /> <label for=\"domtrf'+domainsCount+'\">" . $aInt->lang("domains", "transfer", 1) . "</label></td></tr><tr class=\"hiddenrow\" id=\"domrowdn'+domainsCount+'\" style=\"display:none;\"><td class=\"fieldlabel\">" . $aInt->lang("fields", "domain", 1) . "</td><td class=\"fieldarea\"><input type=\"text\" class=\"regdomain\" id=\"regdomain'+domainsCount+'\" name=\"regdomain['+domainsCount+']\" size=\"40\" onkeyup=\"updatesummary()\" /></td></tr><tr class=\"hiddenrow\" id=\"domrowrp'+domainsCount+'\" style=\"display:none;\"><td class=\"fieldlabel\">" . $aInt->lang("domains", "regperiod", 1) . "</td><td class=\"fieldarea\"><select name=\"regperiod['+domainsCount+']\" onchange=\"updatesummary()\">" . $regperiods . "</select></td></tr><tr class=\"hiddentransrow\" id=\"domrowep'+domainsCount+'\" style=\"display:none;\"><td class=\"fieldlabel\">" . $aInt->lang("domains", "eppcode", 1) . "</td><td class=\"fieldarea\"><input type=\"text\" name=\"eppcode['+domainsCount+']\" size=\"20\" /></td></tr><tr class=\"hiddenrow\" id=\"domrowad'+domainsCount+'\" style=\"display:none;\"><td class=\"fieldlabel\">" . $aInt->lang("domains", "addons", 1) . "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"dnsmanagement['+domainsCount+']\" onclick=\"updatesummary()\" /> " . $aInt->lang("domains", "dnsmanagement", 1) . "</label> <label><input type=\"checkbox\" name=\"emailforwarding['+domainsCount+']\" onclick=\"updatesummary()\" /> " . $aInt->lang("domains", "emailforwarding", 1) . "</label> <label><input type=\"checkbox\" name=\"idprotection['+domainsCount+']\" onclick=\"updatesummary()\" /> " . $aInt->lang("domains", "idprotection", 1) . "</label></td></tr><tr id=\"domainaddlfieldserase'+domainsCount+'\" style=\"display:none\"></tr></table>').appendTo(\"#domains\");
        return false;
    }
    $(\".adddomain\").click(addDomain);

    $(\"#domain0\").keyup(function() {
      $(\"#regdomain0\").val($(\"#domain0\").val());
    });

	$(\".regdomain\").live(\"keyup\", function(){
    	var domainname = $(this).val();
		if(domainname.length >= 5){
			var ord = $(this).attr(\"id\").replace(\"regdomain\",\"\");
			$.post(\"ordersadd.php\", { action: \"getdomainaddlfields\", domain: domainname, order:ord, token: \"" . generate_token("plain") . "\" },
			function(data){
				$(\".domainaddlfields\"+ord).remove();
				$(\"#domainaddlfieldserase\"+ord).after(data);
			});
		}
    });

});
";
$jscode = "
function loadproductoptions(piddd) {
    var ord = piddd.id.substring(3);
    var pid = piddd.value;
    var billingcycle = $(\"#billingcycle option:selected\").val();
    if (pid==0) {
        $(\"#productconfigoptions\"+ord).html(\"\");
        $(\"#addonsrow\"+ord).hide();
        updatesummary();
    } else {
    $(\"#productconfigoptions\"+ord).html(\"<p align=\\\"center\\\">" . $aInt->lang("global", "loading") . "<br><img src=\\\"../images/loading.gif\\\"></p>\");
    $.post(\"ordersadd.php\", { action: \"getconfigoptions\", pid: pid, cycle: billingcycle, orderid: ord, token: \"" . generate_token("plain") . "\" },
    function(data){
        if (data.addons) {
            $(\"#addonsrow\"+ord).show();
            $(\"#addonscont\"+ord).html(data.addons);
        } else {
            $(\"#addonsrow\"+ord).hide();
        }
        $(\"#productconfigoptions\"+ord).html(data.options);
        updatesummary();
    },\"json\");
    }
}
function loaddomainoptions(domrd,type) {
    var ord = domrd.id.substring(6);
    if (type==1) {
        $(\"#domrowdn\"+ord).css(\"display\",\"\");
        $(\"#domrowrp\"+ord).css(\"display\",\"\");
        $(\"#domrowep\"+ord).css(\"display\",\"none\");
        $(\"#domrowad\"+ord).css(\"display\",\"\");
    } else if (type==2) {
        $(\"#domrowdn\"+ord).css(\"display\",\"\");
        $(\"#domrowrp\"+ord).css(\"display\",\"\");
        $(\"#domrowep\"+ord).css(\"display\",\"\");
        $(\"#domrowad\"+ord).css(\"display\",\"\");
    } else {
        $(\"#domrowdn\"+ord).css(\"display\",\"none\");
        $(\"#domrowrp\"+ord).css(\"display\",\"none\");
        $(\"#domrowep\"+ord).css(\"display\",\"none\");
        $(\"#domrowad\"+ord).css(\"display\",\"none\");
    }
}
function updatesummary() {
    jQuery.post(\"ordersadd.php\", \"submitorder=1&calconly=1&\"+jQuery(\"#orderfrm\").serialize(),
    function(data){
        jQuery(\"#ordersumm\").html(data);
    });
}
";
ob_start();

if (!checkActiveGateway()) {
	$aInt->gracefulExit($aInt->lang("gateways", "nonesetup"));
}


if ($userid && !$paymentmethod) {
	$paymentmethod = getClientsPaymentMethod($userid);
}


if ($whmcs->get_req_var("noselections")) {
	infoBox($aInt->lang("global", "validationerror"), $aInt->lang("orders", "noselections"));
}

echo $infobox;
echo "
<form method=\"post\" action=\"";
echo $_SERVER['PHP_SELF'];
echo "\" id=\"orderfrm\">
<input type=\"hidden\" name=\"submitorder\" value=\"true\" />

<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td valign=\"top\" class=\"ordersummaryleftcol\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"130\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "client");
echo "</td><td class=\"fieldarea\">";
echo $aInt->clientsDropDown($userid);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "paymentmethod");
echo "</td><td class=\"fieldarea\">";
echo paymentMethodsSelection();
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "promocode");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"promocode\" id=\"promodd\" onchange=\"updatesummary()\"><option value=\"\">";
echo $aInt->lang("global", "none");
echo "</option><optgroup label=\"Active Promotions\">";
$result = select_query("tblpromotions", "", "(maxuses<=0 OR uses<maxuses) AND (expirationdate='0000-00-00' OR expirationdate>='" . date("Ymd") . "')", "code", "ASC");

while ($data = mysql_fetch_array($result)) {
	$promo_id = $data['id'];
	$promo_code = $data['code'];
	$promo_type = $data['type'];
	$promo_recurring = $data['recurring'];
	$promo_value = $data['value'];

	if ($promo_type == "Percentage") {
		$promo_value .= "%";
	}
	else {
		$promo_value = formatCurrency($promo_value);
	}


	if ($promo_type == "Free Setup") {
		$promo_value = $aInt->lang("promos", "freesetup");
	}

	$promo_recurring = ($promo_recurring ? $aInt->lang("status", "recurring") : $aInt->lang("status", "onetime"));

	if ($promo_type == "Price Override") {
		$promo_recurring = $aInt->lang("promos", "priceoverride");
	}


	if ($promo_type == "Free Setup") {
		$promo_recurring = "";
	}

	echo "<option value=\"" . $promo_code . "\">" . $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
}

echo "</optgroup><optgroup label=\"Expired Promotions\">";
$result = select_query("tblpromotions", "", "(maxuses>0 AND uses>=maxuses) OR (expirationdate!='0000-00-00' AND expirationdate<'" . date("Ymd") . "')", "code", "ASC");

while ($data = mysql_fetch_array($result)) {
	$promo_id = $data['id'];
	$promo_code = $data['code'];
	$promo_type = $data['type'];
	$promo_recurring = $data['recurring'];
	$promo_value = $data['value'];

	if ($promo_type == "Percentage") {
		$promo_value .= "%";
	}
	else {
		$promo_value = formatCurrency($promo_value);
	}


	if ($promo_type == "Free Setup") {
		$promo_value = $aInt->lang("promos", "freesetup");
	}

	$promo_recurring = ($promo_recurring ? $aInt->lang("status", "recurring") : $aInt->lang("status", "onetime"));

	if ($promo_type == "Price Override") {
		$promo_recurring = $aInt->lang("promos", "priceoverride");
	}


	if ($promo_type == "Free Setup") {
		$promo_recurring = "";
	}

	echo "<option value=\"" . $promo_code . "\">" . $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
}

echo "</optgroup></select> <a href=\"#\" onclick=\"showDialog('createpromo');return false\"><img src=\"images/icons/add.png\" border=\"0\" align=\"absmiddle\" /> ";
echo $aInt->lang("orders", "createpromo");
echo "</a></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("orders", "status");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"orderstatus\">
<option value=\"Pending\">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Active\">";
echo $aInt->lang("status", "active");
echo "</option>
</select></td></tr>
<tr><td width=\"130\" class=\"fieldlabel\"></td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"adminorderconf\" id=\"adminorderconf\" checked /> <label for=\"adminorderconf\">";
echo $aInt->lang("orders", "orderconfirmation");
echo "</label> <input type=\"checkbox\" name=\"admingenerateinvoice\" id=\"admingenerateinvoice\" checked /> <label for=\"admingenerateinvoice\">";
echo $aInt->lang("orders", "geninvoice");
echo "</label> <input type=\"checkbox\" name=\"adminsendinvoice\" id=\"adminsendinvoice\" checked /> <label for=\"adminsendinvoice\">";
echo $aInt->lang("global", "sendemail");
echo "</label></td></tr>
</table>

<div id=\"products\">
<div id=\"ord0\" class=\"product\">

<p><b>";
echo $aInt->lang("fields", "product");
echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"130\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "product");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"pid[]\" id=\"pid0\" onchange=\"loadproductoptions(this)\">";
echo $aInt->productDropDown(0, true);
echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"domain[]\" size=\"40\" id=\"domain0\" onkeyup=\"updatesummary()\" /> ";
echo "<s";
echo "pan id=\"whoisresult0\"></span></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "billingcycle");
echo "</td><td class=\"fieldarea\">";

if (!$billingcycle) {
	$billingcycle = "Monthly";
}

echo $aInt->cyclesDropDown($billingcycle, "", "", "billingcycle[]", "updatesummary()");
echo "</td></tr>
<tr id=\"addonsrow0\" style=\"display:none;\"><td class=\"fieldlabel\">";
echo $aInt->lang("addons", "title");
echo "</td><td class=\"fieldarea\" id=\"addonscont0\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "quantity");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"qty[]\" value=\"1\" size=\"5\" onkeyup=\"updatesummary()\" /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "priceoverride");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"priceoverride[]\" size=\"10\" onkeyup=\"updatesummary()\" /> ";
echo $aInt->lang("orders", "priceoverridedesc");
echo "</td></tr>
</table>

<div id=\"productconfigoptions0\"></div>

</div>
</div>

<p style=\"padding-left:20px;\"><a href=\"#\" class=\"addproduct\"><img src=\"images/icons/add.png\" border=\"0\" align=\"absmiddle\" /> ";
echo $aInt->lang("orders", "anotherproduct");
echo "</a></p>

<p><b>";
echo $aInt->lang("domains", "domainreg");
echo "</b></p>

<div id=\"domains\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"130\" class=\"fieldlabel\">";
echo $aInt->lang("domains", "regtype");
echo "</td><td class=\"fieldarea\"><input type=\"radio\" name=\"regaction[0]\" id=\"domnon0\" value=\"\" onclick=\"loaddomainoptions(this,0);updatesummary()\" checked /> <label for=\"domnon0\">";
echo $aInt->lang("global", "none");
echo "</label> <input type=\"radio\" name=\"regaction[0]\" value=\"register\" id=\"domreg0\" onclick=\"loaddomainoptions(this,1);updatesummary()\" /> <label for=\"domreg0\">";
echo $aInt->lang("domains", "register");
echo "</label> <input type=\"radio\" name=\"regaction[0]\" value=\"transfer\" id=\"domtrf0\" onclick=\"loaddomainoptions(this,2);updatesummary()\" /> <label for=\"domtrf0\">";
echo $aInt->lang("domains", "transfer");
echo "</label></td></tr>
<tr class=\"hiddenrow\" id=\"domrowdn0\" style=\"display:none;\"><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\"><input type=\"text\" class=\"regdomain\" name=\"regdomain[0]\" size=\"40\" id=\"regdomain0\" onkeyup=\"updatesummary()\" /></td></tr>
<tr class=\"hiddenrow\" id=\"domrowrp0\" style=\"display:none;\"><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "regperiod");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect id=\"regperiod1\" name=\"regperiod[0]\" onchange=\"updatesummary()\">";
echo $regperiods;
echo "</select></td></tr>
<tr class=\"hiddentransrow\" id=\"domrowep0\" style=\"display:none;\"><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "eppcode");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"eppcode[0]\" size=\"20\" /></td></tr>
<tr class=\"hiddenrow\" id=\"domrowad0\" style=\"display:none;\"><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "addons");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"dnsmanagement[0]\" onclick=\"updatesummary()\" /> ";
echo $aInt->lang("domains", "dnsmanagement");
echo "</label> <label><input type=\"checkbox\" name=\"emailforwarding[0]\" onclick=\"updatesummary()\" /> ";
echo $aInt->lang("domains", "emailforwarding");
echo "</label> <label><input type=\"checkbox\" name=\"idprotection[0]\" onclick=\"updatesummary()\" /> ";
echo $aInt->lang("domains", "idprotection");
echo "</label></td></tr>
<tr id=\"domainaddlfieldserase0\" style=\"display:none;\"></tr>
</table>

</div>

<p style=\"padding-left:20px;\"><a href=\"#\" class=\"adddomain\"><img src=\"images/icons/add.png\" border=\"0\" align=\"absmiddle\" /> ";
echo $aInt->lang("orders", "anotherdomain");
echo "</a></p>

</td><td valign=\"top\">

<div id=\"ordersumm\" style=\"padding:15px;\"></div>

<div class=\"ordersummarytitle\"><input type=\"submit\" value=\"";
echo $aInt->lang("orders", "submit");
echo " &raquo;\" class=\"btn-primary\" style=\"font-size:20px;padding:12px 30px ;\" /></div>

</td></tr></table>

</form>

";
echo "<s";
echo "cript> updatesummary(); </script>

";
echo $aInt->jqueryDialog("createpromo", $aInt->lang("orders", "createpromo"), "<form id=\"createpromofrm\">
" . generate_token("form") . "
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\" width=\"110\">" . $aInt->lang("fields", "promocode") . "</td><td class=\"fieldarea\"><input type=\"text\" name=\"code\" id=\"promocode\" /></td></tr>
<tr><td class=\"fieldlabel\">" . $aInt->lang("fields", "type") . "</td><td class=\"fieldarea\"><select name=\"type\">
<option value=\"Percentage\">" . $aInt->lang("promos", "percentage") . "</option>
<option value=\"Fixed Amount\">" . $aInt->lang("promos", "fixedamount") . "</option>
<option value=\"Price Override\">" . $aInt->lang("promos", "priceoverride") . "</option>
<option value=\"Free Setup\">" . $aInt->lang("promos", "freesetup") . "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">" . $aInt->lang("promos", "value") . "</td><td class=\"fieldarea\"><input type=\"text\" name=\"pvalue\" size=\"10\" /></td></tr>
<tr><td class=\"fieldlabel\">" . $aInt->lang("promos", "recurring") . "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"recurring\" id=\"recurring\" value=\"1\" /> <label for=\"recurring\">" . $aInt->lang("promos", "recurenable") . "</label> <input type=\"text\" name=\"recurfor\" size=\"3\" value=\"0\" /> " . $aInt->lang("promos", "recurenable2") . "</td></tr>
</table>
<p>* " . $aInt->lang("orders", "createpromoinfo") . "</p>
</form>", array($aInt->lang("global", "ok") => "savePromo()", $aInt->lang("global", "cancel") => ""), "", "500", "");
$jscode .= "function savePromo() {
    jQuery.post(\"ordersadd.php\", \"action=createpromo&\"+jQuery(\"#createpromofrm\").serialize(),
    function(data){
        if (data.substr(0,1)==\"<\") {
            $(\"#promodd\").append(data);
            $(\"#promodd\").val($(\"#promocode\").val());
            $(\"#createpromo\").dialog(\"close\");
        } else {
            alert(data);
        }
    });
}";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>