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

function getSSLWebServerTypes() {
	$t = array();
	$t['1001'] = "AOL";
	$t['1002'] = "Apache +ModSSL";
	$t['1003'] = "Apache-SSL (Ben-SSL, not Stronghold)";
	$t['1004'] = "C2Net Stronghold";
	$t['1005'] = "Cobalt Raq";
	$t['1006'] = "Covalent Server Software";
	$t['1031'] = "cPanel / WHM";
	$t['1029'] = "Ensim";
	$t['1032'] = "H-Sphere";
	$t['1007'] = "IBM HTTP Server";
	$t['1008'] = "IBM Internet Connection Server";
	$t['1009'] = "iPlanet";
	$t['1010'] = "Java Web Server (Javasoft / Sun)";
	$t['1011'] = "Lotus Domino";
	$t['1012'] = "Lotus Domino Go!";
	$t['1013'] = "Microsoft IIS 1.x to 4.x";
	$t['1014'] = "Microsoft IIS 5.x and later";
	$t['1015'] = "Netscape Enterprise Server";
	$t['1016'] = "Netscape FastTrack";
	$t['1017'] = "Novell Web Server";
	$t['1018'] = "Oracle";
	$t['1030'] = "Plesk";
	$t['1019'] = "Quid Pro Quo";
	$t['1020'] = "R3 SSL Server";
	$t['1021'] = "Raven SSL";
	$t['1022'] = "RedHat Linux";
	$t['1023'] = "SAP Web Application Server";
	$t['1024'] = "Tomcat";
	$t['1025'] = "Website Professional";
	$t['1026'] = "WebStar 4.x and later";
	$t['1027'] = "WebTen (from Tenon)";
	$t['1028'] = "Zeus Web Server";
	$t['1000'] = "Other (not listed)";
	return $t;
}

define("CLIENTAREA", true);
require "init.php";
require "includes/modulefunctions.php";
$pagetitle = $_LANG['sslconfsslcertificate'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"clientarea.php?action=products\">" . $_LANG['clientareaproducts'] . "</a> > <a href=\"#\">" . $_LANG['clientareaproductdetails'] . ("</a> > <a href=\"configuressl.php?cert=" . $cert . "\">") . $_LANG['sslconfsslcertificate'] . "</a>";
$templatefile = "configuressl-stepone";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);

if (isset($_SESSION['uid'])) {
	$step = (in_array($_GET['step'], array(2, 3)) ? $_GET['step'] : "");
	$result = select_query("tblsslorders", "", array("userid" => $_SESSION['uid'], "MD5(id)" => $cert));
	$data = mysql_fetch_array($result);
	$id = $data['id'];

	if (!$id) {
		$templatefile = "configuressl-stepone";
		$smartyvalues['status'] = "";
		outputClientArea($templatefile);
	}

	$orderid = $data['orderid'];
	$serviceid = $data['serviceid'];
	$remoteid = $data['remoteid'];
	$module = $data['module'];
	$certtype = $data['certtype'];
	$domain = $data['domain'];
	$configdata = $data['configdata'];
	$configdata = unserialize($configdata);
	$provisiondate = $data['provisiondate'];
	$completiondate = $data['completiondate'];
	$status = $data['status'];
	$result = select_query("tblhosting", "packageid,regdate,domain,firstpaymentamount", array("id" => $serviceid));
	$data = mysql_fetch_array($result);
	$productid = $data['packageid'];
	$regdate = $data['regdate'];
	$domain = $data['domain'];
	$firstpaymentamount = $data['firstpaymentamount'];
	$regdate = fromMySQLDate($regdate);
	$result = select_query("tblproducts", "name", array("id" => $productid));
	$data = mysql_fetch_array($result);
	$certificatename = $data['name'];
	$smartyvalues['cert'] = $cert;
	$smartyvalues['serviceid'] = $serviceid;
	$smartyvalues['certtype'] = $certificatename;
	$smartyvalues['date'] = $regdate;
	$smartyvalues['domain'] = $domain;
	$smartyvalues['price'] = formatCurrency($firstpaymentamount);
	$smartyvalues['status'] = $status;

	if (!isValidforPath($module)) {
		exit("Invalid Module Name");
	}

	$modulepath = "modules/servers/" . $module . "/" . $module . ".php";

	if (file_exists($modulepath)) {
		include $modulepath;
	}

	$params = array();
	$params = ModuleBuildParams($serviceid);
	$params['remoteid'] = $remoteid;
	$params['certtype'] = $certtype;
	$params['domain'] = $domain;
	$params['configdata'] = $configdata;

	if (!$_POST) {
		$result = select_query("tblclients", "", array("id" => $_SESSION['uid']));
		$data = mysql_fetch_array($result);
		$firstname = $data['firstname'];
		$lastname = $data['lastname'];
		$orgname = $data['companyname'];
		$email = $data['email'];
		$address1 = $data['address1'];
		$address2 = $data['address2'];
		$city = $data['city'];
		$state = $data['state'];
		$postcode = $data['postcode'];
		$country = $data['country'];
		$phonenumber = $data['phonenumber'];
	}


	if ($step == "2") {
		check_token();
		$errormessage = "";

		if (!$servertype) {
			$errormessage .= "<li>" . $_LANG['sslerrorselectserver'];
		}


		if ((!$csr || nl2br($csr) == "-----BEGIN CERTIFICATE REQUEST-----<br />
<br />
-----END CERTIFICATE REQUEST-----")) {
			$errormessage .= "<li>" . $_LANG['sslerrorentercsr'];
		}

		$result = call_user_func($module . "_SSLStepOne", $params);

		if (is_array($result['additionalfields'])) {
			foreach ($result['additionalfields'] as $heading => $fieldsconfig) {
				foreach ($fieldsconfig as $key => $configoption) {
					$fieldvalue = $_POST['fields'][$key];

					if ($configoption['Required'] && !$fieldvalue) {
						$errormessage .= "<li>" . $configoption['FriendlyName'] . " " . $_LANG['clientareaerrorisrequired'];
						continue;
					}
				}
			}
		}


		if (!$firstname) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorfirstname'];
		}


		if (!$lastname) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorlastname'];
		}


		if (!$email) {
			$errormessage .= "<li>" . $_LANG['clientareaerroremail'];
		}


		if (!$address1) {
			$errormessage .= "<li>" . $_LANG['clientareaerroraddress1'];
		}


		if (!$city) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorcity'];
		}


		if (!$state) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorstate'];
		}


		if (!$postcode) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorpostcode'];
		}


		if (!$phonenumber) {
			$errormessage .= "<li>" . $_LANG['clientareaerrorphonenumber'];
		}


		if (!$errormessage) {
			$configdata = array("servertype" => $servertype, "csr" => $csr, "firstname" => $firstname, "lastname" => $lastname, "orgname" => $orgname, "jobtitle" => $jobtitle, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber);

			if (is_array($fields)) {
				$configdata['fields'] = $fields;
			}

			update_query("tblsslorders", array("configdata" => serialize($configdata)), array("userid" => $_SESSION['uid'], "MD5(id)" => $cert));
			$params = array_merge($params, $configdata);

			if (function_exists($module . "_SSLStepTwo")) {
				$result = call_user_func($module . "_SSLStepTwo", $params);

				if ($result['error']) {
					$errormessage .= "<li>" . $result['error'];
				}


				if ($result['remoteid']) {
					update_query("tblsslorders", array("remoteid" => $result['remoteid']), array("id" => $id));
				}


				if ($result['domain']) {
					update_query("tblhosting", array("domain" => $result['domain']), array("id" => $serviceid));
				}


				if ($result['provisioned']) {
					update_query("tblsslorders", array("provisiondate" => "now()"), array("id" => $id));
				}


				if ($result['expirydate']) {
					update_query("tblsslorders", array("expirydate" => $expirydate), array("id" => $id));
				}
			}
		}


		if ($errormessage) {
			$smartyvalues['errormessage'] = $errormessage;
			$step = "";
		}
	}


	if ($step == "3") {
		check_token();
		$errormessage = "";

		if (((is_array($_POST) && count($_POST)) && function_exists($module . "_SSLStepTwo")) && !$approveremail) {
			$errormessage .= "<li>" . $_LANG['sslerrorapproveremail'];
		}


		if (!$errormessage && function_exists($module . "_SSLStepThree")) {
			$configdata['approveremail'] = $approveremail;
			update_query("tblsslorders", array("configdata" => serialize($configdata)), array("userid" => $_SESSION['uid'], "MD5(id)" => $cert));
			$params = array_merge($params, $configdata);
			$result = call_user_func($module . "_SSLStepThree", $params);

			if ($result['error']) {
				$errormessage .= "<li>" . $result['error'];
			}


			if ($result['remoteid']) {
				update_query("tblsslorders", array("remoteid" => $result['remoteid']), array("id" => $id));
			}


			if ($result['domain']) {
				update_query("tblhosting", array("domain" => $result['domain']), array("id" => $serviceid));
			}


			if ($result['provisioned']) {
				update_query("tblsslorders", array("provisiondate" => "now()"), array("id" => $id));
			}


			if ($result['expirydate']) {
				update_query("tblsslorders", array("expirydate" => $expirydate), array("id" => $id));
			}
		}


		if ($errormessage) {
			$smartyvalues['errormessage'] = $errormessage;
		}
		else {
			update_query("tblsslorders", array("completiondate" => "now()", "status" => "Completed"), array("id" => $id));
		}
	}


	if (!$step) {
		$result = call_user_func($module . "_SSLStepOne", $params);
		include "includes/countries.php";
		$additionalfields = array();

		if (is_array($result['additionalfields'])) {
			foreach ($result['additionalfields'] as $heading => $fieldsconfig) {
				$tempfields = array();
				foreach ($fieldsconfig as $key => $configoption) {
					$fieldvalue = $_POST['fields'][$key];

					if ($configoption['Type'] == "text") {
						$input = ("<input type=\"text\" name=\"fields[" . $key . "]") . "\" size=\"" . $configoption['Size'] . ("\" value=\"" . $fieldvalue . "\" />");
					}
					else {
						if ($configoption['Type'] == "password") {
							$input = ("<input type=\"password\" name=\"fields[" . $key . "]") . "\" size=\"" . $configoption['Size'] . ("\" value=\"" . $fieldvalue . "\" />");
						}
						else {
							if ($configoption['Type'] == "yesno") {
								$input = (("<input type=\"checkbox\" name=\"fields[" . $key . "]") . "\"");

								if ($fieldvalue) {
									$input .= " checked";
								}

								$input .= " />";
							}
							else {
								if ($configoption['Type'] == "textarea") {
									$input = ("<textarea name=\"fields[" . $key . "]") . "\" cols=\"60\" rows=\"" . $configoption['Rows'] . ("\">" . $fieldvalue . "</textarea>");
								}
								else {
									if ($configoption['Type'] == "dropdown") {
										$input = ("<select name=\"fields[" . $key . "]") . "\">";
										$options = explode(",", $configoption['Options']);
										foreach ($options as $value) {
											$input .= "<option";

											if ($value == $fieldvalue) {
												$input .= " selected";
											}

											$input .= ">" . $value . "</option>";
										}

										$input .= "</select>";
									}
									else {
										if ($configoption['Type'] == "country") {
											$input = getCountriesDropDown($fieldvalue, ("fields[" . $key . "]"));
										}
									}
								}
							}
						}
					}

					$tempfields[] = array("name" => $configoption['FriendlyName'], "input" => $input, "description" => $configoption['Description']);
				}

				$additionalfields[$heading] = $tempfields;
			}
		}


		if (!$csr) {
			$csr = "-----BEGIN CERTIFICATE REQUEST-----

-----END CERTIFICATE REQUEST-----";
		}

		$result = select_query("tblsslorders", "", array("userid" => $_SESSION['uid'], "MD5(id)" => $cert));
		$data = mysql_fetch_array($result);
		$status = $data['status'];
		$smartyvalues['status'] = $status;
		$smartyvalues['displaydata'] = $result['displaydata'];
		$smartyvalues['webservertypes'] = getSSLWebServerTypes();
		$smartyvalues['servertype'] = $servertype;
		$smartyvalues['csr'] = $csr;
		$smartyvalues['additionalfields'] = $additionalfields;
		$smartyvalues['firstname'] = $firstname;
		$smartyvalues['lastname'] = $lastname;
		$smartyvalues['orgname'] = $orgname;
		$smartyvalues['jobtitle'] = $jobtitle;
		$smartyvalues['email'] = $email;
		$smartyvalues['address1'] = $address1;
		$smartyvalues['address2'] = $address2;
		$smartyvalues['city'] = $city;
		$smartyvalues['state'] = $state;
		$smartyvalues['postcode'] = $postcode;
		$smartyvalues['country'] = $country;
		$smartyvalues['phonenumber'] = $phonenumber;
		$smartyvalues['faxnumber'] = $faxnumber;
		$smartyvalues['countriesdropdown'] = getCountriesDropDown($country);
	}


	if ($step == "2") {
		if (count($result['approveremails'])) {
			$smartyvalues['displaydata'] = $result['displaydata'];
			$smartyvalues['approveremails'] = $result['approveremails'];
			$templatefile = "configuressl-steptwo";
		}
		else {
			header("Location: configuressl.php?cert=" . $cert . "&step=3");
			exit();
		}
	}


	if ($step == "3") {
		$templatefile = "configuressl-complete";
	}
}
else {
	include "login.php";
}

outputClientArea($templatefile);
?>