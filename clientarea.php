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

define("CLIENTAREA", true);
require "init.php";
require "includes/clientfunctions.php";
require "includes/gatewayfunctions.php";
require "includes/ccfunctions.php";
require "includes/domainfunctions.php";
require "includes/registrarfunctions.php";
require "includes/customfieldfunctions.php";
require "includes/invoicefunctions.php";
require "includes/configoptionsfunctions.php";
$action = $whmcs->get_req_var("action");
$sub = $whmcs->get_req_var("sub");
$id = (int)$whmcs->get_req_var("id");
$q = $whmcs->get_req_var("q");
$paymentmethod = WHMCS_Gateways::makesafename($whmcs->get_req_var("paymentmethod"));

if ($action == "changesq" || $whmcs->get_req_var("2fasetup")) {
  $action = "security";
}

$ca = new WHMCS_ClientArea();
$ca->setPageTitle($whmcs->get_lang("clientareatitle"));
$ca->addToBreadCrumb("index.php", $whmcs->get_lang("globalsystemname"));
$ca->addToBreadCrumb("clientarea.php", $whmcs->get_lang("clientareatitle"));
$ca->initPage();
$ca->requireLogin();

if ($action == "details") {
  $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
}


if ($action == "hosting") {
  $ca->addToBreadCrumb("clientarea.php?action=hosting", $whmcs->get_lang("clientareanavhosting"));
}


if (in_array($action, array("products", "cancel"))) {
  $ca->addToBreadCrumb("clientarea.php?action=products", $whmcs->get_lang("clientareaproducts"));
}


if (in_array($action, array("domains", "domaindetails", "domaincontacts", "domaindns", "domainemailforwarding", "domaingetepp", "domainregisterns"))) {
  $ca->addToBreadCrumb("clientarea.php?action=domains", $whmcs->get_lang("clientareanavdomains"));
}


if ($action == "invoices") {
  $ca->addToBreadCrumb("clientarea.php?action=invoices", $whmcs->get_lang("invoices"));
}


if ($action == "emails") {
  $ca->addToBreadCrumb("clientarea.php?action=emails", $whmcs->get_lang("clientareaemails"));
}


if ($action == "addfunds") {
  $ca->addToBreadCrumb("clientarea.php?action=addfunds", $whmcs->get_lang("addfunds"));
}


if ($action == "masspay") {
  $ca->addToBreadCrumb("clientarea.php?action=masspay" . ($all ? "&all=true" : "") . "#", $whmcs->get_lang("masspaytitle"));
}


if ($action == "quotes") {
  $ca->addToBreadCrumb("clientarea.php?action=quotes", $whmcs->get_lang("quotestitle"));
}

$client = new WHMCS_Client(WHMCS_Session::get("uid"));
$currency = $client->getCurrency();
$ca->assign("action", $action);
$ca->assign("clientareaaction", $action);

if ($action == "") {
  $ca->setTemplate("clientareahome");
  require "includes/ticketfunctions.php";
  $tickets = array();
  $statusfilter = "";
  $result = select_query("tblticketstatuses", "title", array("showactive" => "1"));

  while ($data = mysql_fetch_array($result)) {
    $statusfilter .= "'" . $data[0] . "',";
  }

  $statusfilter = substr($statusfilter, 0, 0 - 1);
  $result = select_query("tbltickets", "", "userid='" . mysql_real_escape_string($client->getID()) . ("' AND status IN (" . $statusfilter . ")"), "lastreply", "DESC");

  while ($data = mysql_fetch_array($result)) {
    $id = $data['id'];
    $tid = $data['tid'];
    $c = $data['c'];
    $deptid = $data['did'];
    $date = $data['date'];
    $date = fromMySQLDate($date, 1, 1);
    $subject = $data['title'];
    $tstatus = $data['status'];
    $urgency = $data['urgency'];
    $lastreply = $data['lastreply'];
    $lastreply = fromMySQLDate($lastreply, 1, 1);
    $clientunread = $data['clientunread'];
    $tstatus = getStatusColour($tstatus);
    $dept = getDepartmentName($deptid);
    $urgency = $_LANG["supportticketsticketurgency" . strtolower($urgency)];
    $tickets[] = array("id" => $id, "tid" => $tid, "c" => $c, "date" => $date, "department" => $dept, "subject" => $subject, "status" => $tstatus, "urgency" => $urgency, "lastreply" => $lastreply, "unread" => $clientunread);
  }

  $ca->assign("tickets", $tickets);
  $invoice = new WHMCS_Invoice();
  $invoices = $invoice->getInvoices("Unpaid", $client->getID(), "id", "DESC");
  $ca->assign("invoices", $invoices);
  $ca->assign("totalbalance", $invoice->getTotalBalanceFormatted());
  $ca->assign("masspay", $CONFIG['EnableMassPay']);
  $ca->assign("defaultpaymentmethod", getGatewayName($clientsdetails['defaultgateway']));
  $files = $client->getFiles($client->getID());
  $ca->assign("files", $files);
  $ca->assign("addfundsenabled", $CONFIG['AddFundsEnabled']);
  $announcements = array();
  $result = select_query("tblannouncements", "", array("published" => "on"), "date", "DESC", "0,3");

  while ($data = mysql_fetch_array($result)) {
    $id = $data['id'];
    $date = $data['date'];
    $title = $data['title'];
    $announcement = $data['announcement'];
    $result2 = select_query("tblannouncements", "", array("parentid" => $id, "language" => $_SESSION['Language']));
    $data = mysql_fetch_array($result2);

    if ($data['title']) {
      $title = $data['title'];
    }


    if ($data['announcement']) {
      $announcement = $data['announcement'];
    }

    $date = fromMySQLDate($date);
    $announcements[] = array("id" => $id, "date" => $date, "title" => $title, "urlfriendlytitle" => getModRewriteFriendlyString($title), "text" => $announcement);
  }

  $smartyvalues['announcements'] = $announcements;

  if ($CONFIG['AllowRegister']) {
    $smartyvalues['registerdomainenabled'] = true;
  }


  if ($CONFIG['AllowTransfer']) {
    $smartyvalues['transferdomainenabled'] = true;
  }


  if ($CONFIG['AllowOwnDomain']) {
    $smartyvalues['owndomainenabled'] = true;
  }

  $captcha = clientAreaInitCaptcha();
  $smartyvalues['captcha'] = $captcha;
  $smartyvalues['recaptchahtml'] = clientAreaReCaptchaHTML();
  $addons_html = run_hook("ClientAreaHomepage", array());
  $ca->assign("addons_html", $addons_html);
}
else {
  if ($action == "details") {
    checkContactPermission("profile");
    $ca->setTemplate("clientareadetails");
    $uneditablefields = explode(",", $CONFIG['ClientsProfileUneditableFields']);
    $smartyvalues['uneditablefields'] = $uneditablefields;
    $e = "";

    if ($save) {
      check_token();
      $e = checkDetailsareValid($client->getID(), false);

      if ($e) {
        $ca->assign("errormessage", $e);
      }
      else {
        $client->updateClient();
        $ca->assign("successful", true);
      }
    }


    if (!$e) {
      $exdetails = $client->getDetails();
    }

    include "includes/countries.php";
    $ca->assign("clientfirstname", $whmcs->get_req_var_if($e, "firstname", $exdetails));
    $ca->assign("clientlastname", $whmcs->get_req_var_if($e, "lastname", $exdetails));
    $ca->assign("clientcompanyname", $whmcs->get_req_var_if($e, "companyname", $exdetails));
    $ca->assign("clientemail", $whmcs->get_req_var_if($e, "email", $exdetails));
    $ca->assign("clientaddress1", $whmcs->get_req_var_if($e, "address1", $exdetails));
    $ca->assign("clientaddress2", $whmcs->get_req_var_if($e, "address2", $exdetails));
    $ca->assign("clientcity", $whmcs->get_req_var_if($e, "city", $exdetails));
    $ca->assign("clientstate", $whmcs->get_req_var_if($e, "state", $exdetails));
    $ca->assign("clientpostcode", $whmcs->get_req_var_if($e, "postcode", $exdetails));
    $ca->assign("clientcountry", $countries[$whmcs->get_req_var_if($e, "country", $exdetails)]);
    $ca->assign("clientcountriesdropdown", getCountriesDropDown($whmcs->get_req_var_if($e, "country", $exdetails)));
    $ca->assign("clientphonenumber", $whmcs->get_req_var_if($e, "phonenumber", $exdetails));
    $ca->assign("customfields", getCustomFields("client", "", $client->getID(), "", "", $_POST['customfield']));
    $ca->assign("contacts", $client->getContacts());
    $ca->assign("billingcid", $whmcs->get_req_var_if($e, "billingcid", $exdetails));
    $ca->assign("paymentmethods", showPaymentGatewaysList());
    $ca->assign("emailoptout", $whmcs->get_req_var_if($e, "emailoptout", $exdetails));
    $ca->assign("emailoptoutenabled", $whmcs->get_config("AllowClientsEmailOptOut"));
    $ca->assign("defaultpaymentmethod", $whmcs->get_req_var_if($e, "defaultgateway", $exdetails));
  }
else {
  if ($action == "contacts") {
    checkContactPermission("contacts");
    $ca->setTemplate("clientareacontacts");
    $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
    $ca->addToBreadCrumb("clientarea.php?action=contacts", $whmcs->get_lang("clientareanavcontacts"));
    $contact_data = array();

    if ($contactid) {
      if ($contactid == "new") {
        redir("action=addcontact");
      }

      $id = (int) $contactid;
    }

    if ($id) {
      $contact_data = $client->getContact($id);

      if (!$contact_data) {
        redir("action=contacts", "clientarea.php");
      }

      $id = $contact_data['id'];
    }

    if ($delete) {
      $client->deleteContact($id);
      redir("action=contacts");
    }

    $e = "";

if ($submit) {
  check_token();
  $errormessage = $e = checkContactDetails($id);

  if (!$subaccount) {
    $password = $permissions = "";
  }

  $smartyvalues['errormessage'] = $errormessage;

  if (!$errormessage) {
    $oldcontactdata = get_query_vals("tblcontacts", "", array("userid" => $client->getID(), "id" => $id));
    $array = db_build_update_array(array("firstname", "lastname", "companyname", "email", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "subaccount", "permissions", "generalemails", "productemails", "domainemails", "invoiceemails", "supportemails"), "implode");
    $array['subaccount'] = ($subaccount ? "1": "0");

    if ($password) {
      $array['password'] = generateClientPW($password);
    }

    update_query("tblcontacts", $array, array("userid" => $client->getID(), "id" => $id));
    run_hook("ContactEdit", array_merge(array("userid" => $client->getID(), "contactid" => $id, "olddata" => $oldcontactdata), $array));
    logActivity("Client Contact Modified - Contact ID: ".$id." - User ID: ".$client->getID());
    $smartyvalues['successful'] = true;
  }
}

if ($success) {
  $smartyvalues['successful'] = true;
}

$contactsarray = $client->getContacts();

if (!$id && count($contactsarray)) {
  $id = $contactsarray[0]['id'];
}

$smartyvalues['contacts'] = $contactsarray;
include "includes/countries.php";
$smartyvalues['contactid'] = $id;

if (((!$errormessage && $submit) && $id) || ($id && !count($contact_data))) {
  $contact_data = $client->getContact($id);

  if (!$contact_data) {
    redir("action=contacts", "clientarea.php");
  }
}

$smartyvalues['contactfirstname'] = $whmcs->get_req_var_if($e, "firstname", $contact_data);
$smartyvalues['contactlastname'] = $whmcs->get_req_var_if($e, "lastname", $contact_data);
$smartyvalues['contactcompanyname'] = $whmcs->get_req_var_if($e, "companyname", $contact_data);
$smartyvalues['contactemail'] = $whmcs->get_req_var_if($e, "email", $contact_data);
$smartyvalues['contactaddress1'] = $whmcs->get_req_var_if($e, "address1", $contact_data);
$smartyvalues['contactaddress2'] = $whmcs->get_req_var_if($e, "address2", $contact_data);
$smartyvalues['contactcity'] = $whmcs->get_req_var_if($e, "city", $contact_data);
$smartyvalues['contactstate'] = $whmcs->get_req_var_if($e, "state", $contact_data);
$smartyvalues['contactpostcode'] = $whmcs->get_req_var_if($e, "postcode", $contact_data);
$smartyvalues['contactphonenumber'] = $whmcs->get_req_var_if($e, "phonenumber", $contact_data);
$smartyvalues['countriesdropdown'] = getCountriesDropDown($whmcs->get_req_var_if($e, "country", $contact_data));
$smartyvalues['subaccount'] = $whmcs->get_req_var_if($e, "subaccount", $contact_data);
$smartyvalues['permissions'] = $whmcs->get_req_var_if($e, "permissions", $contact_data);
$smartyvalues['generalemails'] = $whmcs->get_req_var_if($e, "generalemails", $contact_data);
$smartyvalues['productemails'] = $whmcs->get_req_var_if($e, "productemails", $contact_data);
$smartyvalues['domainemails'] = $whmcs->get_req_var_if($e, "domainemails", $contact_data);
$smartyvalues['invoiceemails'] = $whmcs->get_req_var_if($e, "invoiceemails", $contact_data);
$smartyvalues['supportemails'] = $whmcs->get_req_var_if($e, "supportemails", $contact_data);
} else {
  if ($action == "addcontact") {
    checkContactPermission("contacts");
    $ca->setTemplate("clientareaaddcontact");
    $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
    $ca->addToBreadCrumb("clientarea.php?action=addcontact", $whmcs->get_lang("clientareanavaddcontact"));
    include "includes/countries.php";

    if ($submit) {
      check_token();
      $errormessage = checkContactDetails("", true);

      if (!$subaccount) {
        $password = $permissions = "";
      }

      $smartyvalues['errormessage'] = $errormessage;

      if (!$errormessage) {
        $contactid = addContact($client->getID(), $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails);
        redir("action=contacts&id=".$contactid."&success=1");
        exit();
      }
    }

    $contactsarray = $client->getContacts();
    $smartyvalues['contacts'] = $contactsarray;

    if (!$permissions) {
      $permissions = array();
    }

    $smartyvalues['contactfirstname'] = $firstname;
    $smartyvalues['contactlastname'] = $lastname;
    $smartyvalues['contactcompanyname'] = $companyname;
    $smartyvalues['contactemail'] = $email;
    $smartyvalues['contactaddress1'] = $address1;
    $smartyvalues['contactaddress2'] = $address2;
    $smartyvalues['contactcity'] = $city;
    $smartyvalues['contactstate'] = $state;
    $smartyvalues['contactpostcode'] = $postcode;
    $smartyvalues['contactphonenumber'] = $phonenumber;
    $smartyvalues['countriesdropdown'] = getCountriesDropDown($country);
    $smartyvalues['subaccount'] = $subaccount;
    $smartyvalues['permissions'] = $permissions;
    $smartyvalues['generalemails'] = $generalemails;
    $smartyvalues['productemails'] = $productemails;
    $smartyvalues['domainemails'] = $domainemails;
    $smartyvalues['invoiceemails'] = $invoiceemails;
    $smartyvalues['supportemails'] = $supportemails;
  } else {
    if ($action == "creditcard") {
      if (!CALinkUpdateCC()) {
        redir();
      }

      checkContactPermission("invoices");
      $ca->setTemplate("clientareacreditcard");
      $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
      $ca->addToBreadCrumb("clientarea.php?action=creditcard", $whmcs->get_lang("clientareanavchangecc"));
      $gateways = new WHMCS_Gateways();
      $gotpm = false;

      if (!$gotpm) {
        $result = select_query("tblpaymentgateways", "gateway", array("setting" => "type", "value" => "CC"));
      }

      while ($data = mysql_fetch_array($result)) {
        $gateway = $data['gateway'];

        if (function_exists($gateway."_remoteupdate")) {
          $params = getGatewayVariables($gateway);
          $result = select_query("tblclients", "gatewayid", array("id" => $client->getID()));
          $data = mysql_fetch_array($result);
          $params['gatewayid'] = $data['gatewayid'];
          $remoteupdatecode = call_user_func($gateway."_remoteupdate", $params);

          if (!$remoteupdatecode) {
            $remoteupdatecode = $_LANG['creditcardupdatenotpossible'];
          }

          $smartyvalues['remoteupdatecode'] = $remoteupdatecode;
        }
      }

      if ($submit) {
        check_token();
        $errormessage = updateCCDetails($client->getID(), $cctype, $ccnumber, $cardcvv, $ccexpirymonth.$ccexpiryyear, $ccstartmonth.$ccstartyear, $ccissuenum);

        if (!$errormessage) {
          $smartyvalues['successful'] = true;
        }
      }

      if ($delete && $CONFIG['CCAllowCustomerDelete']) {
        updateCCDetails($client->getID(), "", "", "", "", "");
        $errormessage = "<li>".$_LANG['creditcarddeleteconfirmation'];
      }

      $smartyvalues['errormessage'] = $errormessage;
      $data = getCCDetails($client->getID());
      $smartyvalues['cardtype'] = $data['cardtype'];
      $smartyvalues['cardnum'] = $data['cardnum'];
      $smartyvalues['cardexp'] = $data['expdate'];
      $smartyvalues['cardstart'] = $data['startdate'];
      $smartyvalues['cardissuenum'] = $data['issuenumber'];
      $acceptedcctypes = $CONFIG['AcceptedCardTypes'];
      $acceptedcctypes = explode(",", $acceptedcctypes);
      $smartyvalues['acceptedcctypes'] = $acceptedcctypes;
      $smartyvalues['showccissuestart'] = $CONFIG['ShowCCIssueStart'];
      $smartyvalues['allowcustomerdelete'] = $CONFIG['CCAllowCustomerDelete'];
      $smartyvalues['cctype'] = $cctype;
      $smartyvalues['ccnumber'] = $ccnumber;
      $smartyvalues['ccexpirymonth'] = $ccexpirymonth;
      $smartyvalues['ccexpiryyear'] = $ccexpiryyear;
      $smartyvalues['ccstartmonth'] = $ccstartmonth;
      $smartyvalues['ccstartyear'] = $ccstartyear;
      $smartyvalues['ccissuenum'] = $ccissuenum;
      $smartyvalues['months'] = $gateways->getCCDateMonths();
      $smartyvalues['startyears'] = $gateways->getCCStartDateYears();
      $smartyvalues['expiryyears'] = $smartyvalues['years'] = $gateways->getCCExpiryDateYears();
    } else {
      if ($action == "changepw") {
        $ca->setTemplate("clientareachangepw");
        $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
        $ca->addToBreadCrumb("clientarea.php?action=changepw", $whmcs->get_lang("clientareanavchangepw"));
        $validate = new WHMCS_Validate();

        if ($submit) {
          check_token();
          $existingpw = html_entity_decode($existingpw);
          $newpw = html_entity_decode($newpw);
          $confirmpw = html_entity_decode($confirmpw);

          if ($_SESSION['cid']) {
            $result = select_query("tblcontacts", "password", array("id" => $_SESSION['cid'], "userid" => $client->getID()));
          } else {
            $result = select_query("tblclients", "password", array("id" => $client->getID()));
          }

          $data = mysql_fetch_array($result);

          if ($CONFIG['NOMD5']) {
            $existingpwd = decrypt($data['password']);
          } else {
            $existingpwd = $data['password'];
            $salt = explode(":", $existingpwd);
            $salt = $salt[1];
            $existingpw = generateClientPW($existingpw, $salt);
          }

          if ($validate->validate("match_value", "existingpwd", "existingpasswordincorrect", array($existingpw, $existingpwd))) {
            if ($validate->validate("required", "newpw", "ordererrorpassword")) {
              if ($validate->validate("pwstrength", "newpw", "pwstrengthfail")) {
                if ($validate->validate("required", "confirmpw", "clientareaerrorpasswordconfirm")) {
                  $validate->validate("match_value", "newpw", "clientareaerrorpasswordnotmatch", "confirmpw");
                }
              }
            }
          }

          if (!$validate->hasErrors()) {
            if ($_SESSION['cid']) {
              update_query("tblcontacts", array("password" => generateClientPW($newpw)), array("id" => $_SESSION['cid'], "userid" => $client->getID()));
            } else {
              update_query("tblclients", array("password" => generateClientPW($newpw)), array("id" => $client->getID()));
              run_hook("ClientChangePassword", array("userid" => $client->getID(), "password" => $newpw));
            }

            logActivity("Modified Password - User ID: ".$client->getID(). ($_SESSION['cid'] ? " - Contact ID: ".$_SESSION['cid'] : ""));
            $smartyvalues['successful'] = true;
          }
        }

        $smartyvalues['errormessage'] = $validate->getHTMLErrorOutput();
      } else {
        if ($action == "security") {
          checkContactPermission("changesq");
          $ca->setTemplate("clientareasecurity");
          $ca->addToBreadCrumb("clientarea.php?action=details", $whmcs->get_lang("clientareanavdetails"));
          $ca->addToBreadCrumb("clientarea.php?action=security", $whmcs->get_lang("clientareanavsecurity"));

          if ($whmcs->get_req_var("successful")) {
            $smartyvalues['successful'] = true;
          }

          $twofa = new WHMCS_2FA();
          $twofa->setClientID($ca->getUserID());

          if ($twofa->isActiveClients()) {
            $ca->assign("twofaavailable", true);

            if ($whmcs->get_req_var("2fasetup")) {
              if (!$twofa->isActiveClients()) {
                exit("Access denied");
              }

              ob_start();

              if ($twofa->isEnabled()) {
                echo "<div class=\"content\"><div style=\"padding:15px;\">";
                $disabled = $incorrect = false;

                if ($password = $whmcs->get_req_var("pwverify")) {
                  $dbpwd = get_query_val("tblclients", "password", array("id" => $ca->getUserID()));

                  if ($whmcs->get_config("NOMD5")) {
                    $check_pwd = decrypt($dbpwd);
                  } else {
                    $salt = explode(":", $dbpwd);
                    $salt = $salt[1];
                    $password = generateClientPW($password, $salt);
                    $check_pwd = $dbpwd;
                  }

                  if ($password == $check_pwd) {
                    $twofa->disableUser();
                    $disabled = true;
                  } else {
                    $incorrect = true;
                  }
                }

                echo "<h2>".$whmcs->get_lang("twofadisable")."</h2>";

                if (!$disabled) {
                  echo "<p>".$whmcs->get_lang("twofadisableintro")."</p>";

                  if ($incorrect) {
                    echo "<div class=\"errorbox\"><strong>Password Incorrect</strong><br />Please try again...</div>";
                  }

                  echo "<form onsubmit=\"dialogSubmit();return false\"><input type=\"hidden\" name=\"2fasetup\" value=\"1\" /><p>".$whmcs->get_lang("twofaconfirmpw").": <input type=\"password\" name=\"pwverify\" value=\"\" size=\"20\" /><p><p><input type=\"button\" value=\"".$whmcs->get_lang("twofadisable")."\" class=\"btn\" onclick=\"dialogSubmit()\" /></p></form>";
                } else {
                  echo "<p>".$whmcs->get_lang("twofadisableconfirmation")."</p><form method=\"post\" action=\"clientarea.php?action=security\"><p><input type=\"submit\" value=\"".$whmcs->get_lang("returnclient")."\" class=\"btn\" /></p></form>";
                }

                echo "</div></div>";
              } else {
                $modules = $twofa->getAvailableModules();

                if (isset($module) && in_array($module, $modules)) {
                  $output = $twofa->moduleCall("activate", $module);

                  if (is_array($output) && isset($output['completed'])) {
                    $msg = (isset($output['msg']) ? $output['msg'] : "");
                    $settings = (isset($output['settings']) ? $output['settings'] : array());
                    $backupcode = $twofa->activateUser($module, $settings);
                    $output = "";

                    if ($backupcode) {
                      $output = "<h2>".$whmcs->get_lang("twofaactivationcomplete")."</h2>";

                      if ($msg) {
                        $output .= "<div style=\"margin:20px;padding:10px;background-color:#f7f7f7;border:1px dashed #cccccc;text-align:center;\">".$msg."</div>";
                      }

                      $output .= "<h2>".$whmcs->get_lang("twofabackupcodeis").":</h2><div style=\"margin:20px auto;padding:10px;width:280px;background-color:#F2D4CE;border:1px dashed #AE432E;text-align:center;font-size:20px;\">".$backupcode."</div><p>".$whmcs->get_lang("twofabackupcodeexpl")."</p>";
                      $output .= "<form method=\"post\" action=\"clientarea.php?action=security\"><p><input type=\"submit\" value=\"".$whmcs->get_lang("returnclient")."\" class=\"btn\" /></p></form>";
                    } else {
                      $output = $whmcs->get_lang("twofaactivationerror");
                    }
                  } else {
                    if ($output) {
                      $output = "<div class=\"textleft\">".$output."</div>";
                    }
                  }

                  if (!$output) {
                    echo $whmcs->get_lang("twofageneralerror");
                  } else {
                    echo $output;
                  }
                } else {
                  echo "<h2>".$whmcs->get_lang("twofasetup")."</h2>";

                  if ($whmcs->get_req_var("enforce")) {
                    echo "<br /><div class=\"errorbox\">".$whmcs->get_lang("twofaenforced")."</div>";
                  }

                  echo "<p class=\"textleft\">".$whmcs->get_lang("twofaactivationintro")."</p>
<form><input type=\"hidden\" name=\"2fasetup\" value=\"1\" />";

                  if (1 < count($modules)) {
                    echo "<p>".$whmcs->get_lang("twofaactivationmultichoice")."</p>
<div style=\"margin:0 auto;width:400px;\">";
                    $mod = new WHMCS_Module("security");
                    $first = true;
                    foreach($modules as $module) {
                      $mod->load($module);
                      $configarray = $mod->call("config");
                      echo "<label class=\"radio textleft\"><input type=\"radio\" name=\"module\" value=\"".$module."\"". ($first ? " checked": "")." /> ". (isset($configarray['FriendlyName']['Value']) ? $configarray['FriendlyName']['Value'] : ucfirst($module))."</label>";
                      $first = false;
                    }

                    echo "</div>";
                  } else {
                    echo "<input type=\"hidden\" name=\"module\" value=\"".$modules[0]."\" />";
                  }

                  echo "<p align=\"center\"><br /><input type=\"button\" value=\"".$whmcs->get_lang("twofasetupgetstarted")." &raquo;\" onclick=\"dialogSubmit()\" class=\"btn btn-primary\" /></form>";
                }
              }

              $content = ob_get_contents();
              ob_end_clean();
              $ca->assign("twofaactivation", $content);
            }

            $ca->assign("twofastatus", $twofa->isEnabled());
          }

          $securityquestions = getSecurityQuestions("");
          $smartyvalues['securityquestions'] = $securityquestions;
          $smartyvalues['securityquestionsenabled'] = (count($securityquestions) ? true: false);
          $clientsdetails = getClientsDetails($client->getID());

          if ($clientsdetails['securityqid'] == 0) {
            $smartyvalues['nocurrent'] = true;
          } else {
            foreach($securityquestions as $values) {

              if ($values['id'] == $clientsdetails['securityqid']) {
                $smartyvalues['currentquestion'] = $values['question'];
                continue;
              }
            }
          }

          if ($whmcs->get_req_var("submit")) {
            check_token();

            if ($clientsdetails['securityqid'] && $clientsdetails['securityqans'] != $currentsecurityqans) {
              $errormessage .= "<li>".$_LANG['securitycurrentincorrect'];
            }

            if (!$securityqans) {
              $errormessage .= "<li>".$_LANG['securityanswerrequired'];
            }

            if ($securityqans != $securityqans2) {
              $errormessage .= "<li>".$_LANG['securitybothnotmatch'];
            }

            if (!$errormessage) {
              update_query("tblclients", array("securityqid" => $securityqid, "securityqans" => encrypt($securityqans)), array("id" => $client->getID()));
              logActivity("Modified Security Question - User ID: ".$client->getID());
              redir("action=changesq&successful=true");
              exit();
            }
          }

          $smartyvalues['errormessage'] = $errormessage;
        } else {
          if ($action == "hosting" || $action == "products") {
            checkContactPermission("products");
            $ca->setTemplate("clientareaproducts");
            $table = "tblhosting";
            $fields = "COUNT(*)";
            $where = "userid='".db_escape_string($client->getID())."'";

            if ($q) {
              $q = preg_replace("/[^a-z0-9-.]/", "", strtolower($q));
              $where .= " AND domain LIKE '%".db_escape_string($q)."%'";
              $smartyvalues['q'] = $q;
            }

            $innerjoin = "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid";
            $result = select_query($table, $fields, $where, "", "", "", $innerjoin);
            $data = mysql_fetch_array($result);
            $numitems = $data[0];
            list($orderby, $sort, $limit) = clientAreaTableInit("prod", "product", "ASC", $numitems);
            $smartyvalues['orderby'] = $orderby;
            $smartyvalues['sort'] = strtolower($sort);

            if ($orderby == "price") {
              $orderby = "amount";
            } else {
              if ($orderby == "billingcycle") {
                $orderby = "billingcycle";
              } else {
                if ($orderby == "nextduedate") {
                  $orderby = "nextduedate";
                } else {
                  if ($orderby == "status") {
                    $orderby = "domainstatus";
                  } else {
                    $orderby = "domain` ".$sort.",`tblproducts`.`name";
                  }
                }
              }
            }

            $accounts = array();
            $fields = "tblhosting.*,tblproductgroups.name AS productgroup,tblproducts.name,tblproducts.tax,tblproducts.upgradepackages,tblproducts.downloads,tblproducts.servertype";
            $result = select_query($table, $fields, $where, $orderby, $sort, $limit, $innerjoin);

            while ($data = mysql_fetch_array($result)) {
              $id = $data['id'];
              $regdate = $data['regdate'];
              $domain = $data['domain'];
              $firstpaymentamount = $data['firstpaymentamount'];
              $recurringamount = $data['amount'];
              $nextduedate = $data['nextduedate'];
              $billingcycle = $data['billingcycle'];
              $status = $data['domainstatus'];
              $upgradepackages = count(unserialize($data['upgradepackages']));
              $downloads = unserialize($data['downloads']);
              $productgroup = $data['productgroup'];
              $productname = $data['name'];
              $tax = $data['tax'];
              $server = $data['server'];
              $username = $data['username'];
              $module = $data['servertype'];
              $downloadscount = 0;

              if (is_array($downloads)) {
                foreach($downloads as $dl) {

                  if (trim($dl)) {++$downloadscount;
                    continue;
                  }
                }
              }

              $serverarray = array();

              if ($server) {
                $result2 = select_query("tblservers", "", array("id" => $server));
                $serverarray = mysql_fetch_array($result2);
              }

              if ($tax) {}

              $regdate = fromMySQLDate($regdate, 0, 1, "-");
              $nextduedate = fromMySQLDate($nextduedate, 0, 1, "-");
              $langbillingcycle = $ca->getRawStatus($billingcycle);
              $rawstatus = $ca->getRawStatus($status);
              $xstatus = $status;

              if ($status == "Active") {
                $xcolor = "clientarealistactive";
              } else {
                if ($status == "Completed") {
                  $xcolor = "clientarealistactive";
                } else {
                  if ($status == "Pending") {
                    $xcolor = "clientarealistpending";
                  } else {
                    if ($status == "Suspended") {
                      $xcolor = "clientarealistsuspended";
                    } else {
                      $xcolor = "clientarealistterminated";
                      $xstatus = "terminated";
                    }
                  }
                }
              }

              $accounts[] = array("id" => $id, "regdate" => $regdate, "group" => $productgroup, "product" => $productname, "module" => $module, "server" => $serverarray, "domain" => $domain, "firstpaymentamount" => formatCurrency($firstpaymentamount), "recurringamount" => formatCurrency($recurringamount), "amount" => ($billingcycle == "One Time" ? formatCurrency($firstpaymentamount) : formatCurrency($recurringamount)), "nextduedate" => $nextduedate, "billingcycle" => $_LANG["orderpaymentterm".$langbillingcycle], "username" => $username, "status" => $status, "rawstatus" => $rawstatus, "statustext" => $_LANG["clientarea".$rawstatus], "class" => strtolower($xstatus), "addons" => (get_query_val("tblhostingaddons", "id", array("hostingid" => $id), "id", "DESC") ? "1": ""), "packagesupgrade" => ($upgradepackages ? "1": ""), "downloads" => ($downloads ? "1": ""), "showcancelbutton" => $CONFIG['ShowCancellationButton']);
            }

            $ca->assign("services", $accounts);
            $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
          }
else {
  if ($action == "productdetails") {
    checkContactPermission("products");
    $ca->setTemplate("clientareaproductdetails");
    $service = new WHMCS_Service($id, $client->getID());

    if ($service->isNotValid()) {
      redir("action=products", "clientarea.php");
    }

    $ca->addToBreadCrumb("clientarea.php?action=products", $whmcs->get_lang("clientareaproducts"));
    $ca->addToBreadCrumb("clientarea.php?action=productdetails#", $whmcs->get_lang("clientareaproductdetails"));
    $customfields = $service->getCustomFields();
    $ca->assign("id", $service->getData("id"));
    $ca->assign("pid", $service->getData("packageid"));
    $ca->assign("type", $service->getData("type"));
    $ca->assign("regdate", fromMySQLDate($service->getData("regdate"), 0, 1, "-"));
    $ca->assign("modulename", $service->getModule());
    $ca->assign("module", $service->getModule());
    $ca->assign("serverdata", $service->getServerInfo());
    $ca->assign("domain", $service->getData("domain"));
    $ca->assign("groupname", $service->getData("groupname"));
    $ca->assign("product", $service->getData("productname"));
    $ca->assign("paymentmethod", $service->getPaymentMethod());
    $ca->assign("firstpaymentamount", formatCurrency($service->getData("firstpaymentamount")));
    $ca->assign("recurringamount", formatCurrency($service->getData("amount")));
    $ca->assign("billingcycle", $service->getBillingCycleDisplay());
    $ca->assign("nextduedate", fromMySQLDate($service->getData("nextduedate"), 0, 1, "-"));
    $ca->assign("status", $service->getStatusDisplay());
    $ca->assign("rawstatus", strtolower($service->getData("status")));
    $ca->assign("dedicatedip", $service->getData("dedicatedip"));
    $ca->assign("assignedips", $service->getData("assignedips"));
    $ca->assign("ns1", $service->getData("ns1"));
    $ca->assign("ns2", $service->getData("ns2"));
    $ca->assign("packagesupgrade", $service->getAllowProductUpgrades());
    $ca->assign("configoptionsupgrade", $service->getAllowConfigOptionsUpgrade());
    $ca->assign("customfields", $customfields);
    $ca->assign("productcustomfields", $customfields);
    $ca->assign("suspendreason", $service->getSuspensionReason());
    $ca->assign("subscriptionid", $service->getData("subscriptionid"));
    $diskstats = $service->getDiskUsageStats();
    foreach($diskstats as $k => $v) {
      $ca->assign($k, $v);
    }

    $ca->assign("showcancelbutton", $service->getAllowCancellation());
    $ca->assign("configurableoptions", $service->getConfigurableOptions());
    $ca->assign("addons", $service->getAddons());
    $ca->assign("addonsavailable", $service->hasProductGotAddons());
    $ca->assign("downloads", $service->getAssociatedDownloads());
    $servicepw = $service->getData("password");
    $moduleclientarea = "";

    if ($service->getModule() && $service->getData("status") == "Active") {
      $allowedclientmodulefunctions = array();
      $success = $service->moduleCall("ClientAreaAllowedFunctions");

      if ($success) {
        $data = $service->getModuleReturn("data");

        if (is_array($data)) {
          foreach($data as $v) {
            $allowedclientmodulefunctions[] = $v;
          }
        }
      }

      $success = $service->moduleCall("ClientAreaCustomButtonArray");

      if ($success) {
        $data = $service->getModuleReturn("data");
        $ca->assign("servercustombuttons", $data);
        $ca->assign("modulecustombuttons", $data);

        if (is_array($data)) {
          foreach($data as $k => $v) {
            $allowedclientmodulefunctions[] = $v;
          }
        }
      }

      $modop = $whmcs->get_req_var("modop");

      if ($whmcs->get_req_var("serveraction")) {
        $modop = $whmcs->get_req_var("serveraction");
      }

      $a = $whmcs->get_req_var("a");

      if ($modop == "custom" && in_array($a, $allowedclientmodulefunctions)) {
        checkContactPermission("manageproducts");
        $success = $service->moduleCall($a);

        if ($success) {
          $data = $service->getModuleReturn("data");

          if (is_array($data)) {
            if (isset($data['templatefile'])) {
              $ca->setTemplate("/modules/servers/".$service->getModule()."/".$data['templatefile'].".tpl");
            }

            if (isset($data['breadcrumb'])) {
              if (is_array($data['breadcrumb'])) {
                foreach($data['breadcrumb'] as $k => $v) {
                  $ca->addToBreadCrumb($k, $v);
                }
              }
            }

            if (is_array($data['vars'])) {
              foreach($data['vars'] as $k => $v) {
                $smartyvalues[$k] = $v;
              }
            }
          } else {
            $ca->assign("modulecustombuttonresult", "success");
          }
        } else {
          $ca->assign("modulecustombuttonresult", $service->getLastError());
        }
      }

      if ($service->hasFunction("ChangePassword") && $service->getAllowChangePassword()) {
        $ca->assign("serverchangepassword", true);
        $ca->assign("modulechangepassword", true);
        $modulechangepasswordmessage = "";
        $modulechangepassword = $whmcs->get_req_var("modulechangepassword");

        if ($whmcs->get_req_var("serverchangepassword")) {
          $modulechangepassword = true;
        }

        if ($modulechangepassword) {
          check_token();
          checkContactPermission("manageproducts");
          $newpwfield = "newpw";
          $newpassword1 = $whmcs->get_req_var("newpw");
          $newpassword2 = $whmcs->get_req_var("confirmpw");
          foreach(array("newpassword1", "newserverpassword1") as $key) {

            if (!$newpassword1 && $whmcs->get_req_var($key)) {
              $newpwfield = $key;
              $newpassword1 = $whmcs->get_req_var($key);
              continue;
            }
          }

          foreach(array("newpassword2", "newserverpassword2") as $key) {

            if ($whmcs->get_req_var($key)) {
              $newpassword2 = $whmcs->get_req_var($key);
              continue;
            }
          }

          $validate = new WHMCS_Validate();

          if ($validate->validate("match_value", "newpw", "clientareaerrorpasswordnotmatch", array($newpassword1, $newpassword2))) {
            $validate->validate("pwstrength", $newpwfield, "pwstrengthfail");
          }

          if ($validate->hasErrors()) {
            $modulechangepwresult = "error";
            $modulechangepasswordmessage = $validate->getHTMLErrorOutput();
          } else {
            update_query("tblhosting", array("password" => encrypt($newpassword1)), array("id" => $id));
            $success = $service->moduleCall("ChangePassword", array("password" => html_entity_decode($newpassword1)));

            if ($success) {
              logActivity("Module Change Password Successful - Service ID: ".$id);
              $modulechangepwresult = "success";
              $modulechangepasswordmessage = $_LANG['serverchangepasswordsuccessful'];
              $servicepw = $newpassword1;
            } else {
              $modulechangepwresult = "error";
              $modulechangepasswordmessage = $_LANG['serverchangepasswordfailed'];
              update_query("tblhosting", array("password" => encrypt($servicepw)), array("id" => $id));
            }
          }

          $smartyvalues['modulechangepwresult'] = $modulechangepwresult;
          $smartyvalues['modulechangepasswordmessage'] = $modulechangepasswordmessage;
        }
      }

      if (checkContactPermission("manageproducts", true)) {
        $moduletemplatefile = "";
        $success = $service->moduleCall("ClientArea");
        $data = $service->getModuleReturn("data");

        if (is_array($data)) {
          if (isset($data['templatefile'])) {
            $moduletemplatefile = "/modules/servers/".$service->getModule()."/".$data['templatefile'].".tpl";
          }
        } else {
          $moduleclientarea = ($data != FUNCTIONDOESNTEXIST ? $data: "");
        }

        if (!$moduletemplatefile && file_exists(ROOTDIR."/modules/servers/".$service->getModule()."/clientarea.tpl")) {
          $moduletemplatefile = "/modules/servers/".$service->getModule()."/clientarea.tpl";
        }

        if ($moduletemplatefile) {
          $moduleparams = $service->buildParams();

          if ((is_array($data) && array_key_exists("vars", $data)) && is_array($data['vars'])) {
            foreach($data['vars'] as $k => $v) {
              $moduleparams[$k] = $v;
            }
          }

          $moduleclientarea = $ca->getSingleTPLOutput($moduletemplatefile, $moduleparams);
        }
      }
    }

    if (checkContactPermission("manageproducts", true)) {
      $ca->assign("serverclientarea", $moduleclientarea);
      $ca->assign("moduleclientarea", $moduleclientarea);
      $ca->assign("username", $service->getData("username"));
      $ca->assign("password", $servicepw);
    }
  } else {
    if ($action == "domains") {
      checkContactPermission("domains");
      $ca->setTemplate("clientareadomains");
      $where = "userid='".db_escape_string($client->getID())."'";

      if ($q) {
        $q = preg_replace("/[^a-z0-9-.]/", "", strtolower($q));
        $where .= " AND domain LIKE '%".db_escape_string($q)."%'";
        $smartyvalues['q'] = $q;
      }

      $result = select_query("tbldomains", "COUNT(*)", $where);
      $data = mysql_fetch_array($result);
      $numitems = $data[0];
      list($orderby, $sort, $limit) = clientAreaTableInit("dom", "domain", "ASC", $numitems);
      $smartyvalues['orderby'] = $orderby;
      $smartyvalues['sort'] = strtolower($sort);

      if ($orderby == "price") {
        $orderby = "recurringamount";
      } else {
        if ($orderby == "regdate") {
          $orderby = "registrationdate";
        } else {
          if ($orderby == "nextduedate") {
            $orderby = "nextduedate";
          } else {
            if ($orderby == "status") {
              $orderby = "status";
            } else {
              if ($orderby == "autorenew") {
                $orderby = "donotrenew";
              } else {
                $orderby = "domain";
              }
            }
          }
        }
      }

      $domains = array();
      $result = select_query("tbldomains", "", $where, $orderby, $sort, ($page - 1) * $pagelimit. (",".$pagelimit));

      while ($data = mysql_fetch_array($result)) {
        $id = $data['id'];
        $registrationdate = $data['registrationdate'];
        $domain = $data['domain'];
        $amount = $data['recurringamount'];
        $nextduedate = $data['nextduedate'];
        $expirydate = $data['expirydate'];
        $status = $data['status'];
        $donotrenew = $data['donotrenew'];
        $rawstatus = $ca->getRawStatus($status);
        $autorenew = ($donotrenew ? false: true);
        $registrationdate = fromMySQLDate($registrationdate, 0, 1, "-");
        $nextduedate = fromMySQLDate($nextduedate, 0, 1, "-");
        $expirydate = fromMySQLDate($expirydate, 0, 1, "-");
        $domains[] = array("id" => $id, "domain" => $domain, "amount" => formatCurrency($amount), "registrationdate" => $registrationdate, "nextduedate" => $nextduedate, "expirydate" => $expirydate, "status" => $status, "rawstatus" => $rawstatus, "statustext" => $_LANG["clientarea".$rawstatus], "autorenew" => $autorenew);
      }

      $ca->assign("domains", $domains);
      $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
    } else {
      if ($action == "domaindetails") {
        checkContactPermission("domains");
        $ca->setTemplate("clientareadomaindetails");
        $domains = new WHMCS_Domains();
        $domain_data = $domains->getDomainsDatabyID($id);

        if (!$domain_data) {
          redir("action=domains", "clientarea.php");
        }

        if ($autorenew == "enable") {
          update_query("tbldomains", array("donotrenew" => ""), array("id" => $id, "userid" => $client->getID()));
          $domainname = get_query_val("tbldomains", "domain", array("userid" => $client->getID(), "id" => $id));
          logActivity("Client Enabled Domain Auto Renew - Domain ID: ".$id." - Domain: ".$domainname);
          $ca->assign("updatesuccess", true);
        } else {
          if ($autorenew == "disable") {
            disableAutoRenew($id);
            $ca->assign("updatesuccess", true);
          }
        }

        $domain_data = $domains->getDomainsDatabyID($id);
        $domain = $domains->getData("domain");
        $firstpaymentamount = $domains->getData("firstpaymentamount");
        $recurringamount = $domains->getData("recurringamount");
        $nextduedate = $domains->getData("nextduedate");
        $expirydate = $domains->getData("expirydate");
        $paymentmethod = $domains->getData("paymentmethod");
        $domainstatus = $domains->getData("status");
        $registrationperiod = $domains->getData("registrationperiod");
        $registrationdate = $domains->getData("registrationdate");
        $donotrenew = $domains->getData("donotrenew");
        $dnsmanagement = $domains->getData("dnsmanagement");
        $emailforwarding = $domains->getData("emailforwarding");
        $idprotection = $domains->getData("idprotection");
        $registrar = $domains->getModule();
        $gatewaysarray = getGatewaysArray();
        $paymentmethod = $gatewaysarray[$paymentmethod];
        $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain);
        $registrationdate = fromMySQLDate($registrationdate, 0, 1, "-");
        $nextduedate = fromMySQLDate($nextduedate, 0, 1, "-");
        $expirydate = fromMySQLDate($expirydate, 0, 1, "-");
        $rawstatus = $ca->getRawStatus($domainstatus);
        $allowrenew = false;

        if ($domainstatus == "Active" || $domainstatus == "Expired") {
          $allowrenew = true;
        }

        $autorenew = ($donotrenew ? false: true);
        $sld = $domains->getSLD();
        $tld = $domains->getTLD();
        $ca->assign("domainid", $domains->getData("id"));
        $ca->assign("domain", $domain);
        $ca->assign("sld", $sld);
        $ca->assign("tld", $tld);
        $ca->assign("firstpaymentamount", formatCurrency($firstpaymentamount));
        $ca->assign("recurringamount", formatCurrency($recurringamount));
        $ca->assign("registrationdate", $registrationdate);
        $ca->assign("nextduedate", $nextduedate);
        $ca->assign("expirydate", $expirydate);
        $ca->assign("registrationperiod", $registrationperiod);
        $ca->assign("paymentmethod", $paymentmethod);
        $ca->assign("status", $_LANG["clientarea".$rawstatus]);
        $ca->assign("rawstatus", $rawstatus);
        $ca->assign("donotrenew", $donotrenew);
        $ca->assign("autorenew", $autorenew);
        $ca->assign("subaction", $sub);
        $ca->assign("addonstatus", array("dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection));

        if ($allowrenew) {
          $ca->assign("renew", $allowrenew);
        }

        $tlddata = get_query_vals("tbldomainpricing", "", array("extension" => ".".$tld));
        $ca->assign("addons", array("dnsmanagement" => $tlddata['dnsmanagement'], "emailforwarding" => $tlddata['emailforwarding'], "idprotection" => $tlddata['idprotection']));
        $addonscount = 0;

        if ($tlddata['dnsmanagement']) {++$addonscount;
        }

        if ($tlddata['emailforwarding']) {++$addonscount;
        }

        if ($tlddata['idprotection']) {++$addonscount;
        }

        $ca->assign("addonscount", $addonscount);
        $result = select_query("tblpricing", "", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
        $data = mysql_fetch_array($result);
        $domaindnsmanagementprice = $data['msetupfee'];
        $domainemailforwardingprice = $data['qsetupfee'];
        $domainidprotectionprice = $data['ssetupfee'];
        $ca->assign("addonspricing", array("dnsmanagement" => formatCurrency($domaindnsmanagementprice), "emailforwarding" => formatCurrency($domainemailforwardingprice), "idprotection" => formatCurrency($domainidprotectionprice)));

        if ($domainstatus == "Active" && $domains->getModule()) {
          $registrarclientarea = "";
          $ca->assign("registrar", $registrar);

          if ($sub == "savens") {
            check_token();
            checkContactPermission("managedomains");
            $nameservers = ($nschoice == "default" ? $domains->getDefaultNameservers() : array("ns1" => $ns1, "ns2" => $ns2, "ns3" => $ns3, "ns4" => $ns4, "ns5" => $ns5));
            $success = $domains->moduleCall("SaveNameservers", $nameservers);

            if ($success) {
              $smartyvalues['updatesuccess'] = true;
            } else {
              $smartyvalues['error'] = $domains->getLastError();
            }
          }

          if ($sub == "savereglock") {
            check_token();
            checkContactPermission("managedomains");
            $newlockstatus = ($whmcs->get_req_var("reglock") ? "locked": "unlocked");
            $success = $domains->moduleCall("SaveRegistrarLock", array("lockenabled" => $newlockstatus));

            if ($success) {
              $smartyvalues['updatesuccess'] = true;
            } else {
              $smartyvalues['error'] = $domains->getLastError();
            }
          }

          $success = $domains->moduleCall("GetNameservers");

          if ($success) {
            $i = 1;

            while ($i <= 5) {
              $ca->assign("ns".$i, $domains->getModuleReturn("ns".$i)); ++$i;
            }

            $smartyvalues['managens'] = true;
            $defaultns = array();
            $i = 1;

            while ($i <= 5) {
              if (trim($CONFIG["DefaultNameserver".$i])) {
                $defaultns[trim($CONFIG["DefaultNameserver".$i])] = true;
              }

              ++$i;
            }

            foreach($values as $ns) {
              unset($defaultns[$ns]);
            }

            if (!count($defaultns)) {
              $smartyvalues['defaultns'] = true;
            }
          } else {
            $smartyvalues['error'] = $domains->getLastError();
          }

          if (!preg_match('/uk$/i', $tld) && $domains->hasFunction("GetRegistrarLock")) {
            $success = $domains->moduleCall("GetRegistrarLock");

            if ($success) {
              $ca->assign("lockstatus", $domains->getModuleReturn());
            }
          }

          $smartyvalues['managecontacts'] = ($domains->hasFunction("GetContactDetails") ? true: false);
          $smartyvalues['registerns'] = ($domains->hasFunction("RegisterNameserver") ? true: false);
          $smartyvalues['dnsmanagement'] = (($dnsmanagement && $domains->hasFunction("GetDNS")) ? true: false);
          $smartyvalues['emailforwarding'] = (($emailforwarding && $domains->hasFunction("GetEmailForwarding")) ? true: false);
          $smartyvalues['getepp'] = (($tlddata['eppcode'] && $domains->hasFunction("GetEPPCode")) ? true: false);

          if (preg_match('/uk$/i', $tld) && $domains->hasFunction("ReleaseDomain")) {
            $allowrelease = false;

            if (isset($params['AllowClientTAGChange'])) {
              if ($params['AllowClientTAGChange']) {
                $allowrelease = true;
              }
            } else {
              $allowrelease = true;
            }

            if ($allowrelease) {
              $smartyvalues['releasedomain'] = true;

              if ($sub == "releasedomain") {
                check_token();
                checkContactPermission("managedomains");
                $success = $domains->moduleCall("ReleaseDomain", array("transfertag" => $transtag));

                if ($success) {
                  $ca->assign("status", $whmcs->get_lang("clientareacancelled"));
                  logActivity("Client Requested Domain Release to Tag ".$transtag);
                } else {
                  $smartyvalues['error'] = $domains->getLastError();
                }
              }
            } else {
              $smartyvalues['releasedomain'] = false;
            }
          }

          $allowedclientregistrarfunctions = array();

          if ($domains->hasFunction("ClientAreaAllowedFunctions")) {
            $success = $domains->moduleCall("ClientAreaAllowedFunctions");
            $registrarallowedfunctions = $domains->getModuleReturn();

            if (is_array($registrarallowedfunctions)) {
              foreach($registrarallowedfunctions as $v) {
                $allowedclientregistrarfunctions[] = $v;
              }
            }
          }

          if ($domains->hasFunction("ClientAreaCustomButtonArray")) {
            $success = $domains->moduleCall("ClientAreaCustomButtonArray");
            $registrarcustombuttons = $domains->getModuleReturn();

            if (is_array($registrarcustombuttons)) {
              foreach($registrarcustombuttons as $k => $v) {
                $allowedclientregistrarfunctions[] = $v;
              }
            }

            $ca->assign("registrarcustombuttons", $registrarcustombuttons);
          }

          if ($modop == "custom" && in_array($a, $allowedclientregistrarfunctions)) {
            checkContactPermission("managedomains");
            $success = $domains->moduleCall($a);
            $data = $domains->getModuleReturn();

            if (is_array($data)) {
              if (isset($data['templatefile'])) {
                $ca->setTemplate("/modules/registrars/".$registrar."/".$data['templatefile'].".tpl");
              }

              if (isset($data['breadcrumb'])) {
                if (is_array($data['breadcrumb'])) {
                  foreach($data['breadcrumb'] as $k => $v) {
                    $ca->addToBreadCrumb($k, $v);
                  }
                }
              }

              if (is_array($data['vars'])) {
                foreach($data['vars'] as $k => $v) {
                  $smartyvalues[$k] = $v;
                }
              }
            } else {
              if (!$data || $data == "success") {
                $ca->assign("registrarcustombuttonresult", "success");
              } else {
                $ca->assign("registrarcustombuttonresult", $data);
              }
            }
          }

          if (checkContactPermission("managedomains", true)) {
            $moduletemplatefile = "";
            $result = RegClientAreaOutput($params);

            if (is_array($result)) {
              if (isset($result['templatefile'])) {
                $moduletemplatefile = "/modules/registrars/".$registrar."/".$result['templatefile'].".tpl";
              }
            } else {
              $registrarclientarea = $result;
            }

            if (!$moduletemplatefile && file_exists(ROOTDIR. ("/modules/registrars/".$registrar."/clientarea.tpl"))) {
              $moduletemplatefile = "/modules/registrars/".$registrar."/clientarea.tpl";
            }

            if ($moduletemplatefile) {
              if (is_array($result['vars'])) {
                foreach($result['vars'] as $k => $v) {
                  $params[$k] = $v;
                }
              }

              $registrarclientarea = $ca->getSingleTPLOutput($moduletemplatefile, $moduleparams);
            }
          }

          $smartyvalues['registrarclientarea'] = $registrarclientarea;
        }
      } else {
        if ($action == "domaincontacts") {
          checkContactPermission("managedomains");
          $ca->setTemplate("clientareadomaincontactinfo");
          $contactsarray = $client->getContactsWithAddresses();
          $smartyvalues['contacts'] = $contactsarray;
          $domains = new WHMCS_Domains();
          $domain_data = $domains->getDomainsDatabyID($domainid);

          if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction("GetContactDetails")) {
            redir("action=domains", "clientarea.php");
          }

          $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain_data['domain']);
          $ca->addToBreadCrumb("#", $whmcs->get_lang("domaincontactinfo"));

          if ($sub == "save") {
            check_token();
            $wc = $whmcs->get_req_var("wc");
            $contactdetails = $whmcs->get_req_var("contactdetails");
            foreach($wc as $wc_key => $wc_val) {

              if ($wc_val == "contact") {
                $selctype = $sel[$wc_key][0];
                $selcid = substr($sel[$wc_key], 1);
                $tmpcontactdetails = array();

                if ($selctype == "c") {
                  $tmpcontactdetails = get_query_vals("tblcontacts", "", array("userid" => $client->getID(), "id" => $selcid));
                } else {
                  if ($selctype == "u") {
                    $tmpcontactdetails = get_query_vals("tblclients", "", array("id" => $client->getID()));
                  }
                }

                $contactdetails[$wc_key] = $domains->buildWHOISSaveArray($tmpcontactdetails);
                continue;
              }
            }

            $success = $domains->moduleCall("SaveContactDetails", array("contactdetails" => $contactdetails));

            if ($success) {
              $smartyvalues['successful'] = true;
            } else {
              $smartyvalues['error'] = $domains->getLastError();
            }
          }

          $success = $domains->moduleCall("GetContactDetails");

          if ($success) {
            $smartyvalues['contactdetails'] = $domains->getModuleReturn();
          } else {
            $smartyvalues['error'] = $domains->getLastError();
          }

          $smartyvalues['domainid'] = $domains->getData("id");
          $smartyvalues['domain'] = $domains->getData("domain");
          $smartyvalues['contacts'] = $client->getContactsWithAddresses();
        } else {
          if ($action == "domainemailforwarding") {
            checkContactPermission("managedomains");
            $ca->setTemplate("clientareadomainemailforwarding");
            $domains = new WHMCS_Domains();
            $domain_data = $domains->getDomainsDatabyID($domainid);

            if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction("GetEmailForwarding")) {
              redir("action=domains", "clientarea.php");
            }

            $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain_data['domain']);
            $ca->addToBreadCrumb("#", $whmcs->get_lang("domainemailforwarding"));

            if ($sub == "save") {
              check_token();
              $key = 0;
              $vars = array();

              if ($whmcs->get_req_var("emailforwarderprefix")) {
                foreach($whmcs->get_req_var("emailforwarderprefix") as $key => $value) {
                  $vars['prefix'][$key] = $whmcs->get_req_var("emailforwarderprefix", $key);
                  $vars['forwardto'][$key] = $whmcs->get_req_var("emailforwarderforwardto", $key);
                }
              }

              if ($whmcs->get_req_var("emailforwarderprefixnew")) {++$key;
                $vars['prefix'][$key] = $whmcs->get_req_var("emailforwarderprefixnew");
                $vars['forwardto'][$key] = $whmcs->get_req_var("emailforwarderforwardtonew");
              }

              $success = $domains->moduleCall("SaveEmailForwarding", $vars);

              if (!$success) {
                $smartyvalues['error'] = $domains->getLastError();
              }
            }

            $success = $domains->moduleCall("GetEmailForwarding");

            if (!$success) {
              $smartyvalues['error'] = $domains->getLastError();
            }

            $smartyvalues['domainid'] = $domain_data['id'];
            $smartyvalues['domain'] = $domain_data['domain'];

            if ($domains->getModuleReturn("external")) {
              $ca->assign("external", true);
              $ca->assign("code", $domains->getModuleReturn("code"));
            } else {
              $ca->assign("emailforwarders", $domains->getModuleReturn());
            }
          } else {
            if ($action == "domaindns") {
              checkContactPermission("managedomains");
              $ca->setTemplate("clientareadomaindns");
              $domains = new WHMCS_Domains();
              $domain_data = $domains->getDomainsDatabyID($domainid);

              if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction("GetDNS")) {
                redir("action=domains", "clientarea.php");
              }

              $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain_data['domain']);
              $ca->addToBreadCrumb("#", $whmcs->get_lang("domaindnsmanagement"));

              if ($sub == "save") {
                check_token();
                $vars = array();
                foreach($_POST['dnsrecordhost'] as $num => $dnshost) {
                  $vars[] = array("hostname" => $dnshost, "type" => $_POST['dnsrecordtype'][$num], "address" => $_POST['dnsrecordaddress'][$num], "priority" => $_POST['dnsrecordpriority'][$num], "recid" => $_POST['dnsrecid'][$num]);
                }

                $success = $domains->moduleCall("SaveDNS", array("dnsrecords" => $vars));

                if (!$success) {
                  $smartyvalues['error'] = $domains->getLastError();
                }
              }

              $success = $domains->moduleCall("GetDNS");

              if (!$success) {
                $smartyvalues['error'] = $domains->getLastError();
              }

              $smartyvalues['domainid'] = $domain_data['id'];
              $smartyvalues['domain'] = $domain_data['domain'];

              if ($domains->getModuleReturn("external")) {
                $ca->assign("external", true);
                $ca->assign("code", $domains->getModuleReturn("code"));
              } else {
                $ca->assign("dnsrecords", $domains->getModuleReturn());
              }
            } else {
              if ($action == "domaingetepp") {
                checkContactPermission("managedomains");
                $ca->setTemplate("clientareadomaingetepp");
                $domains = new WHMCS_Domains();
                $domain_data = $domains->getDomainsDatabyID($domainid);

                if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction("GetEPPCode")) {
                  redir("action=domains", "clientarea.php");
                }

                $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain_data['domain']);
                $ca->addToBreadCrumb("#", $whmcs->get_lang("domaingeteppcode"));
                $smartyvalues['domainid'] = $domain_data['id'];
                $smartyvalues['domain'] = $domain_data['domain'];
                $success = $domains->moduleCall("GetEPPCode");

                if (!$success) {
                  $smartyvalues['error'] = $domains->getLastError();
                } else {
                  $smartyvalues['eppcode'] = $domains->getModuleReturn("eppcode");
                }
              } else {
                if ($action == "domainregisterns") {
                  checkContactPermission("managedomains");
                  $ca->setTemplate("clientareadomainregisterns");
                  $domains = new WHMCS_Domains();
                  $domain_data = $domains->getDomainsDatabyID($domainid);

                  if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction("RegisterNameserver")) {
                    redir("action=domains", "clientarea.php");
                  }

                  $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=".$domain_data['id'], $domain_data['domain']);
                  $ca->addToBreadCrumb("#", $whmcs->get_lang("domainregisterns"));
                  $smartyvalues['domainid'] = $domain_data['id'];
                  $smartyvalues['domain'] = $domain_data['domain'];
                  $result = "";
                  $vars = array();
                  $ns = $whmcs->get_req_var("ns");

                  if ($sub == "register") {
                    check_token();
                    $ipaddress = $whmcs->get_req_var("ipaddress");
                    $nameserver = $ns.".".$domain_data['domain'];
                    $vars['nameserver'] = $nameserver;
                    $vars['ipaddress'] = $ipaddress;
                    $success = $domains->moduleCall("RegisterNameserver", $vars);
                    $result = ($success ? $_LANG['domainregisternsregsuccess'] : $domains->getLastError());
                  } else {
                    if ($sub == "modify") {
                      check_token();
                      $nameserver = $ns.".".$domain_data['domain'];
                      $currentipaddress = $whmcs->get_req_var("currentipaddress");
                      $newipaddress = $whmcs->get_req_var("newipaddress");
                      $vars['nameserver'] = $nameserver;
                      $vars['currentipaddress'] = $currentipaddress;
                      $vars['newipaddress'] = $newipaddress;
                      $success = $domains->moduleCall("ModifyNameserver", $vars);
                      $result = ($success ? $_LANG['domainregisternsmodsuccess'] : $domains->getLastError());
                    } else {
                      if ($sub == "delete") {
                        check_token();
                        $nameserver = $ns.".".$domain_data['domain'];
                        $vars['nameserver'] = $nameserver;
                        $success = $domains->moduleCall("DeleteNameserver", $vars);
                        $result = ($success ? $_LANG['domainregisternsdelsuccess'] : $domains->getLastError());
                      }
                    }
                  }

                  $smartyvalues['result'] = $result;
                } else {
                  if ($action == "domainrenew") {
                    checkContactPermission("orders");
                    redir("gid=renewals", "cart.php");
                  } else {
                    if ($action == "invoices") {
                      checkContactPermission("invoices");
                      $ca->setTemplate("clientareainvoices");
                      $numitems = get_query_val("tblinvoices", "COUNT(*)", array("userid" => $client->getID()));
                      list($orderby, $sort, $limit) = clientAreaTableInit("inv", "status", "DESC", $numitems);
                      $smartyvalues['orderby'] = $orderby;
                      $smartyvalues['sort'] = strtolower($sort);

                      if ($orderby == "invoicenum") {
                        $orderby = "invoicenum` ".$sort.",`id";
                      } else {
                        if ($orderby == "date") {
                          $orderby = "date";
                        } else {
                          if ($orderby == "duedate") {
                            $orderby = "duedate";
                          } else {
                            if ($orderby == "total") {
                              $orderby = "total";
                            } else {
                              if ($orderby == "status") {
                                $orderby = "status";
                              } else {
                                $orderby = "status` DESC,`duedate";
                              }
                            }
                          }
                        }
                      }

                      $invoice = new WHMCS_Invoice();
                      $invoices = $invoice->getInvoices("", $client->getID(), $orderby, $sort, $limit);
                      $ca->assign("invoices", $invoices);

                      if ($invoice->getTotalBalance() <= 0) {
                        $ca->assign("nobalance", true);
                      }

                      $ca->assign("totalbalance", $invoice->getTotalBalanceFormatted());
                      $ca->assign("masspay", $CONFIG['EnableMassPay']);
                      $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
                    } else {
                      if ($action == "emails") {
                        checkContactPermission("emails");
                        $ca->setTemplate("clientareaemails");
                        $result = select_query("tblemails", "COUNT(*)", array("userid" => $client->getID()), "id", "DESC");
                        $data = mysql_fetch_array($result);
                        $numitems = $data[0];
                        list($orderby, $sort, $limit) = clientAreaTableInit("emails", "date", "DESC", $numitems);
                        $smartyvalues['orderby'] = $orderby;
                        $smartyvalues['sort'] = strtolower($sort);

                        if ($orderby == "subject") {
                          $orderby = "subject";
                        } else {
                          $orderby = "date";
                        }

                        $emails = array();
                        $result = select_query("tblemails", "", array("userid" => $client->getID()), $orderby, $sort, $limit);

                        while ($data = mysql_fetch_array($result)) {
                          $id = $data['id'];
                          $date = $data['date'];
                          $subject = $data['subject'];
                          $date = fromMySQLDate($date, 1, 1);
                          $emails[] = array("id" => $id, "date" => $date, "subject" => $subject);
                        }

                        $ca->assign("emails", $emails);
                        $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
                      } else {
                        if ($action == "cancel") {
                          checkContactPermission("orders");
                          $service = new WHMCS_Service($id, $client->getID());

                          if ($service->isNotValid()) {
                            redir("action=products", "clientarea.php");
                          }

                          $allowedstatuscancel = array("Active", "Suspended");

                          if (!in_array($service->getData("status"), $allowedstatuscancel)) {
                            redir("action=productdetails&id=".$id);
                          }

                          $ca->setTemplate("clientareacancelrequest");
                          $ca->addToBreadCrumb("clientarea.php?action=productdetails&id=".$id, $whmcs->get_lang("clientareaproductdetails"));
                          $ca->addToBreadCrumb("cancel&id=".$id, $whmcs->get_lang("clientareacancelrequest"));
                          $clientsdetails = getClientsDetails($client->getID());
                          $smartyvalues['id'] = $service->getData("id");
                          $smartyvalues['groupname'] = $service->getData("groupname");
                          $smartyvalues['productname'] = $service->getData("productname");
                          $smartyvalues['domain'] = $service->getData("domain");
                          $cancelrequests = get_query_val("tblcancelrequests", "COUNT(*)", array("relid" => $id));

                          if ($cancelrequests) {
                            $smartyvalues['invalid'] = "on";
                          } else {
                            if ($sub == "submit") {
                              check_token();

                              if (!trim($cancellationreason)) {
                                $smartyvalues['error'] = true;
                              }

                              if (!$smartyvalues['error']) {
                                if (!in_array($type, array("Immediate", "End of Billing Period"))) {
                                  $type = "End of Billing Period";
                                }

                                createCancellationRequest($client->getID(), $id, $cancellationreason, $type);

                                if ($canceldomain) {
                                  $domainid = get_query_val("tbldomains", "id", array("userid" => $client->getID(), "domain" => $service->getData("domain")));

                                  if ($domainid) {
                                    disableAutoRenew($domainid);
                                  }
                                }

                                sendMessage("Cancellation Request Confirmation", $id);
                                sendAdminMessage("New Cancellation Request", array("client_id" => $client->getID(), "clientname" => $clientsdetails['firstname']." ".$clientsdetails['lastname'], "service_id" => $id, "product_name" => $service->getData("productname"), "service_cancellation_type" => $type, "service_cancellation_type" => $type, "service_cancellation_reason" => $cancellationreason), "account");
                                $smartyvalues['requested'] = "on";
                              }
                            }

                            if ($service->getData("domain")) {
                              $data = get_query_vals("tbldomains", "id,recurringamount,registrationperiod,nextduedate", array("userid" => $client->getID(), "domain" => $service->getData("domain"), "status" => "Active", "donotrenew" => ""));
                              $smartyvalues['domainid'] = $data['id'];
                              $smartyvalues['domainprice'] = formatCurrency($data['recurringamount']);
                              $smartyvalues['domainregperiod'] = $data['registrationperiod'];
                              $smartyvalues['domainnextduedate'] = fromMySQLDate($data['nextduedate'], 0, 1);
                            }
                          }
                        } else {
                          if ($action == "addfunds") {
                            checkContactPermission("invoices");
                            $clientsdetails = getClientsDetails();
                            $addfundsmaxbal = convertCurrency($CONFIG['AddFundsMaximumBalance'], 1, $clientsdetails['currency']);
                            $addfundsmax = convertCurrency($CONFIG['AddFundsMaximum'], 1, $clientsdetails['currency']);
                            $addfundsmin = convertCurrency($CONFIG['AddFundsMinimum'], 1, $clientsdetails['currency']);
                            $result = select_query("tblorders", "COUNT(*)", array("userid" => $client->getID(), "status" => "Active"));
                            $data = mysql_fetch_array($result);
                            $numactiveorders = $data[0];

                            if (!$CONFIG['AddFundsRequireOrder']) {
                              $numactiveorders = 1;
                            }

                            if (!$CONFIG['AddFundsEnabled']) {
                              $smartyvalues['addfundsdisabled'] = true;
                            } else {
                              if (!$numactiveorders) {
                                $smartyvalues['notallowed'] = true;
                              } else {
                                if ($amount) {
                                  check_token();
                                  $totalcredit = $clientsdetails['credit'] + $amount;

                                  if ($addfundsmaxbal < $totalcredit) {
                                    $errormessage = $_LANG['addfundsmaximumbalanceerror']." ".formatCurrency($addfundsmaxbal);
                                  }

                                  if ($addfundsmax < $amount) {
                                    $errormessage = $_LANG['addfundsmaximumerror']." ".formatCurrency($addfundsmax);
                                  }

                                  if ($amount < $addfundsmin) {
                                    $errormessage = $_LANG['addfundsminimumerror']." ".formatCurrency($addfundsmin);
                                  }

                                  if ($errormessage) {
                                    $ca->assign("errormessage", $errormessage);
                                  } else {
                                    $paymentmethods = getGatewaysArray();

                                    if (!array_key_exists($paymentmethod, $paymentmethods)) {
                                      $paymentmethod = getClientsPaymentMethod($client->getID());
                                    }

                                    $paymentmethod = WHMCS_Gateways::makesafename($paymentmethod);

                                    if (!$paymentmethod) {
                                      exit("Unexpected payment method value. Exiting.");
                                    }

                                    require "includes/processinvoices.php";
                                    $invoiceid = createInvoices($client->getID());
                                    insert_query("tblinvoiceitems", array("userid" => $client->getID(), "type" => "AddFunds", "relid" => "", "description" => $_LANG['addfunds'], "amount" => $amount, "taxed" => "0", "duedate" => "now()", "paymentmethod" => $paymentmethod));
                                    $invoiceid = createInvoices($client->getID(), "", true);
                                    $result = select_query("tblpaymentgateways", "value", array("gateway" => $paymentmethod, "setting" => "type"));
                                    $data = mysql_fetch_array($result);
                                    $gatewaytype = $data['value'];

                                    if ($gatewaytype == "CC" || $gatewaytype == "OfflineCC") {
                                      if (!isValidforPath($paymentmethod)) {
                                        exit("Invalid Payment Gateway Name");
                                      }

                                      $gatewaypath = ROOTDIR."/modules/gateways/".$paymentmethod.".php";

                                      if (file_exists($gatewaypath)) {
                                        require_once $gatewaypath;
                                      }

                                      if (!function_exists($paymentmethod."_link")) {
                                        redir("invoiceid=". (int) $invoiceid, "creditcard.php");
                                      }
                                    }

                                    $result = select_query("tblinvoices", "", array("userid" => $client->getID(), "id" => $invoiceid));
                                    $data = mysql_fetch_array($result);
                                    $id = $data['id'];
                                    $total = $data['total'];
                                    $paymentmethod = $data['paymentmethod'];
                                    $clientsdetails = getClientsDetails($client->getID());
                                    $params = getGatewayVariables($paymentmethod, $id, $total);
                                    $paymentbutton = call_user_func($paymentmethod."_link", $params);
                                    $ca->setTemplate("forwardpage");
                                    $ca->assign("message", $_LANG['forwardingtogateway']);
                                    $ca->assign("code", $paymentbutton);
                                    $ca->assign("invoiceid", $id);
                                    $ca->output();
                                    exit();
                                  }
                                } else {
                                  $amount = $addfundsmin;
                                }
                              }
                            }

                            $ca->setTemplate("clientareaaddfunds");
                            $ca->assign("minimumamount", formatCurrency($addfundsmin));
                            $ca->assign("maximumamount", formatCurrency($addfundsmax));
                            $ca->assign("maximumbalance", formatCurrency($addfundsmaxbal));
                            $ca->assign("amount", format_as_currency($amount));
                            $gatewayslist = showPaymentGatewaysList();
                            $ca->assign("gateways", $gatewayslist);
                          } else {
                            if ($action == "masspay") {
                              checkContactPermission("invoices");
                              $ca->setTemplate("masspay");

                              if (!$CONFIG['EnableMassPay']) {
                                redir();
                                exit();
                              }

                              if ($all) {
                                $invoiceids = array();
                                $result = select_query("tblinvoices", "id", array("userid" => $client->getID(), "status" => "Unpaid", "(select count(id) from tblinvoiceitems where invoiceid=tblinvoices.id and type='Invoice')" => array("sqltype" => "<=", "value" => 0)), "id", "DESC");

                                while ($data = mysql_fetch_array($result)) {
                                  $invoiceids[] = $data['id'];
                                }
                              } else {
                                if (count($invoiceids) == 0) {
                                  redir();
                                  exit();
                                } else {
                                  if (count($invoiceids) == 1) {
                                    redir("id=". (int) $invoiceids[0], "viewinvoice.php");
                                  } else {
                                    $tmp_invoiceids = db_escape_numarray($invoiceids);
                                    $invoiceids = array();
                                    $result = select_query("tblinvoices", "id", array("userid" => $client->getID(), "status" => "Unpaid", "id" => array("sqltype" => "IN", "values" => $tmp_invoiceids)), "id", "DESC");

                                    while ($data = mysql_fetch_array($result)) {
                                      $invoiceids[] = $data['id'];
                                    }
                                  }
                                }
                              }

                              $xmasspays = array();
                              $result = select_query("tblinvoiceitems", "invoiceid,relid", array("tblinvoiceitems.userid" => $client->getID(), "tblinvoiceitems.type" => "Invoice", "tblinvoices.status" => "Unpaid"), "", "", "", "tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid");

                              while ($data = mysql_fetch_array($result)) {
                                $xmasspays[$data[0]][$data[1]] = 1;
                              }

                              if (count($xmasspays)) {
                                $numsel = count($invoiceids);
                                foreach($xmasspays as $iid => $vals) {

                                  if (count($vals) == $numsel) {
                                    foreach($invoiceids as $z) {
                                      unset($vals[$z]);
                                    }

                                    if (!count($vals)) {
                                      redir("id=".$iid, "viewinvoice.php");
                                      continue;
                                    }

                                    continue;
                                  }
                                }
                              }

                              if ($geninvoice) {
                                check_token();
                              }

                              $paymentmethods = getGatewaysArray();

                              if (!array_key_exists($paymentmethod, $paymentmethods)) {
                                $paymentmethod = getClientsPaymentMethod($client->getID());
                              }

                              $paymentmethod = WHMCS_Gateways::makesafename($paymentmethod);

                              if (!$paymentmethod) {
                                exit("Unexpected payment method value. Exiting.");
                              }

                              $subtotal = $credit = $tax = $tax2 = $total = $partialpayments = 0;
                              $invoiceitems = array();
                              foreach($invoiceids as $invoiceid) {
                                $result = select_query("tblinvoices", "", array("id" => (int) $invoiceid, "userid" => $client->getID()));
                                $data = mysql_fetch_array($result);
                                $invoiceid = $data['id'];

                                if ($invoiceid) {
                                  $subtotal += $data['subtotal'];
                                  $credit += $data['credit'];
                                  $tax += $data['tax'];
                                  $tax2 += $data['tax2'];
                                  $thistotal = $data['total'];
                                  $total += $thistotal;
                                  $result = select_query("tblaccounts", "SUM(amountin)", array("invoiceid" => (int) $invoiceid));
                                  $data = mysql_fetch_array($result);
                                  $thispayments = $data[0];
                                  $partialpayments += $thispayments;
                                  $thistotal = $thistotal - $thispayments;

                                  if ($geninvoice) {
                                    insert_query("tblinvoiceitems", array("userid" => $client->getID(), "type" => "Invoice", "relid" => (int) $invoiceid, "description" => $_LANG['invoicenumber']. (int) $invoiceid, "amount" => $thistotal, "duedate" => "now()", "paymentmethod" => $paymentmethod));
                                  }

                                  $result = select_query("tblinvoiceitems", "", array("invoiceid" => (int) $invoiceid));

                                  while ($data = mysql_fetch_array($result)) {
                                    $invoiceitems[(int) $invoiceid][] = array("id" => $data['id'], "description" => nl2br($data['description']), "amount" => formatCurrency($data['amount']), "tax" => $data['tax']);
                                  }

                                  continue;
                                }
                              }

                              if ($geninvoice) {
                                foreach($xmasspays as $iid => $vals) {
                                  update_query("tblinvoices", array("status" => "Cancelled"), array("id" => (int) $iid, "userid" => $client->getID()));
                                }

                                require "includes/processinvoices.php";
                                $invoiceid = createInvoices($client->getID(), true, true);
                                $invoiceid = (int) $invoiceid;
                                $result = select_query("tblpaymentgateways", "value", array("gateway" => $paymentmethod, "setting" => "type"));
                                $data = mysql_fetch_array($result);
                                $gatewaytype = $data['value'];

                                if ($gatewaytype == "CC" || $gatewaytype == "OfflineCC") {
                                  if (!isValidforPath($paymentmethod)) {
                                    exit("Invalid Payment Gateway Name");
                                  }

                                  $gatewaypath = ROOTDIR."/modules/gateways/".$paymentmethod.".php";

                                  if (file_exists($gatewaypath)) {
                                    require_once $gatewaypath;
                                  }

                                  if (!function_exists($paymentmethod."_link")) {
                                    redir("invoiceid=". (int) $invoiceid, "creditcard.php");
                                  }
                                }

                                $result = select_query("tblinvoices", "", array("userid" => $client->getID(), "id" => $invoiceid));
                                $data = mysql_fetch_array($result);
                                $id = $data['id'];
                                $total = $data['total'];
                                $paymentmethod = $data['paymentmethod'];
                                $paymentmethod = WHMCS_Gateways::makesafename($paymentmethod);
                                $clientsdetails = getClientsDetails($client->getID());
                                $params = getGatewayVariables($paymentmethod, $id, $total);
                                $paymentbutton = call_user_func($paymentmethod."_link", $params);
                                $ca->setTemplate("forwardpage");
                                $ca->assign("message", $_LANG['forwardingtogateway']);
                                $ca->assign("code", $paymentbutton);
                                $ca->assign("invoiceid", $id);
                                $ca->output();
                                exit();
                              }

                              $smartyvalues['subtotal'] = formatCurrency($subtotal);

                              if ($credit) {
                                $smartyvalues['credit'] = formatCurrency($credit);
                              }

                              if ($tax) {
                                $smartyvalues['tax'] = formatCurrency($tax);
                              }

                              if ($tax2) {
                                $smartyvalues['tax2'] = formatCurrency($tax2);
                              }

                              if ($partialpayments) {
                                $smartyvalues['partialpayments'] = formatCurrency($partialpayments);
                              }

                              $smartyvalues['total'] = formatCurrency($total - $partialpayments);
                              $smartyvalues['invoiceitems'] = $invoiceitems;
                              $gatewayslist = showPaymentGatewaysList();
                              $smartyvalues['gateways'] = $gatewayslist;
                              $smartyvalues['defaultgateway'] = key($gatewayslist);
                            } else {
                              if ($action == "quotes") {
                                $ca->setTemplate("clientareaquotes");
                                require ROOTDIR."/includes/quotefunctions.php";
                                $result = select_query("tblquotes", "COUNT(*)", array("userid" => $client->getID()));
                                $data = mysql_fetch_array($result);
                                $numitems = $data[0];
                                list($orderby, $sort, $limit) = clientAreaTableInit("quote", "id", "DESC", $numitems);

                                if (!in_array($orderby, array("id", "date", "duedate", "total", "stage"))) {
                                  $orderby = "validuntil";
                                }

                                $smartyvalues['orderby'] = $orderby;
                                $smartyvalues['sort'] = strtolower($sort);
                                $quotes = array();
                                $result = select_query("tblquotes", "", array("userid" => $client->getID(), "stage" => array("sqltype" => "NEQ", "value" => "Draft")), $orderby, $sort, $limit);

                                while ($data = mysql_fetch_assoc($result)) {
                                  $data['datecreated'] = fromMySQLDate($data['datecreated'], 0, 1);
                                  $data['validuntil'] = fromMySQLDate($data['validuntil'], 0, 1);
                                  $data['lastmodified'] = fromMySQLDate($data['lastmodified'], 0, 1);
                                  $data['stage'] = getQuoteStageLang($data['stage']);
                                  $quotes[] = $data;
                                }

                                $smartyvalues['quotes'] = $quotes;
                                $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
                              } else {
                                if ($action == "bulkdomain") {
                                  checkContactPermission("managedomains");
                                  $ca->setTemplate("bulkdomainmanagement");
                                  $domainids = "";
                                  foreach($domids as $domid) {
                                    $domainids .= (int) $domid.",";
                                  }

                                  $domainids = substr($domainids, 0, 0 - 1);
                                  $queryfilter = "userid=". (int) $client->getID(). (" AND id IN (".$domainids.")");
                                  $domains = $domainids = $errors = array();
                                  $result = select_query("tbldomains", "id,domain", $queryfilter, "domain", "ASC");

                                  while ($data = mysql_fetch_assoc($result)) {
                                    $domainids[] = $data['id'];
                                    $domains[] = $data['domain'];
                                  }

                                  if (!count($domainids)) {
                                    redir("action=domains");
                                  }

                                  if (!$update) {
                                    if ($nameservers) {
                                      $update = "nameservers";
                                    } else {
                                      if ($autorenew) {
                                        $update = "autorenew";
                                      } else {
                                        if ($reglock) {
                                          $update = "reglock";
                                        } else {
                                          if ($contactinfo) {
                                            $update = "contactinfo";
                                          } else {
                                            if ($renew) {
                                              $update = "renew";
                                            }
                                          }
                                        }
                                      }
                                    }
                                  }

                                  $smartyvalues['domainids'] = $domainids;
                                  $smartyvalues['domains'] = $domains;
                                  $smartyvalues['update'] = $update;
                                  $smartyvalues['save'] = $save;
                                  $currpage = $_SERVER['PHP_SELF']."?action=bulkdomain";
                                  $ca->addToBreadCrumb("clientarea.php?action=domains", $whmcs->get_lang("clientareanavdomains"));

                                  if ($update == "nameservers") {
                                    $ca->addToBreadCrumb($currpage, $whmcs->get_lang("domainmanagens"));

                                    if ($save) {
                                      check_token();
                                      foreach($domainids as $domainid) {
                                        $data = get_query_vals("tbldomains", "domain,registrar", array("id" => $domainid, "userid" => $client->getID()));
                                        $domain = $data['domain'];
                                        $registrar = $data['registrar'];
                                        $domainparts = explode(".", $domain, 2);
                                        $params = array();
                                        $params['domainid'] = $domainid;
                                        $params['sld'] = $domainparts[0];
                                        $params['tld'] = $domainparts[1];
                                        $params['registrar'] = $registrar;
                                        $params = RegBuildParams($params);

                                        if ($nschoice == "default") {
                                          $params = RegGetDefaultNameservers($params, $domain);
                                        } else {
                                          $params['ns1'] = $ns1;
                                          $params['ns2'] = $ns2;
                                          $params['ns3'] = $ns3;
                                          $params['ns4'] = $ns4;
                                          $params['ns5'] = $ns5;
                                        }

                                        $values = RegSaveNameservers($params);

                                        if (!function_exists($registrar."_SaveNameservers")) {
                                          $errors[] = $domain." ".$_LANG['domaincannotbemanaged'];
                                        }

                                        if ($values['error']) {
                                          $errors[] = $domain." - ".$values['error'];
                                          continue;
                                        }
                                      }
                                    }
                                  } else {
                                    if ($update == "autorenew") {
                                      $ca->addToBreadCrumb($currpage."#", $whmcs->get_lang("domainautorenewstatus"));

                                      if ($save) {
                                        check_token();
                                        foreach($domainids as $domainid) {

                                          if ($whmcs->get_req_var("enable")) {
                                            update_query("tbldomains", array("donotrenew" => ""), array("id" => $domainid, "userid" => $client->getID()));
                                            continue;
                                          }

                                          disableAutoRenew($domainid);
                                        }
                                      }
                                    } else {
                                      if ($update == "reglock") {
                                        $ca->addToBreadCrumb($currpage."#", $whmcs->get_lang("domainreglockstatus"));

                                        if ($save) {
                                          check_token();
                                          foreach($domainids as $domainid) {
                                            $data = get_query_vals("tbldomains", "domain,registrar", array("id" => $domainid, "userid" => $client->getID()));
                                            $domain = $data['domain'];
                                            $registrar = $data['registrar'];
                                            $domainparts = explode(".", $domain, 2);
                                            $params = array();
                                            $params['domainid'] = $domainid;
                                            $params['sld'] = $domainparts[0];
                                            $params['tld'] = $domainparts[1];
                                            $params['registrar'] = $registrar;
                                            $params = RegBuildParams($params);
                                            $newlockstatus = ($_POST['enable'] ? "locked": "unlocked");
                                            $params['lockenabled'] = $newlockstatus;
                                            $values = RegSaveRegistrarLock($params);

                                            if (!function_exists($registrar."_SaveRegistrarLock")) {
                                              $errors[] = $domain." ".$_LANG['domaincannotbemanaged'];
                                            }

                                            if ($values['error']) {
                                              $errors[] = $domain." - ".$values['error'];
                                              continue;
                                            }
                                          }
                                        }
                                      } else {
                                        if ($update == "contactinfo") {
                                          if (!is_array($domainids) || count($domainids) <= 0) {
                                            exit("Invalid Access Attempt");
                                          }

                                          $ca->addToBreadCrumb($currpage."#", $whmcs->get_lang("domaincontactinfoedit"));

                                          if ($save) {
                                            check_token();
                                            $wc = $whmcs->get_req_var("wc");
                                            $contactdetails = $whmcs->get_req_var("contactdetails");
                                            foreach($wc as $wc_key => $wc_val) {

                                              if ($wc_val == "contact") {
                                                $selctype = $sel[$wc_key][0];
                                                $selcid = substr($sel[$wc_key], 1);
                                                $tmpcontactdetails = array();

                                                if ($selctype == "c") {
                                                  $tmpcontactdetails = get_query_vals("tblcontacts", "", array("userid" => $client->getID(), "id" => $selcid));
                                                } else {
                                                  if ($selctype == "u") {
                                                    $tmpcontactdetails = get_query_vals("tblclients", "", array("id" => $client->getID()));
                                                  }
                                                }

                                                $contactdetails[$wc_key] = $domains->buildWHOISSaveArray($tmpcontactdetails);
                                                continue;
                                              }
                                            }

                                            foreach($domainids as $domainid) {
                                              $domains = new WHMCS_Domains();
                                              $domain_data = $domains->getDomainsDatabyID($domainid);

                                              if (!$domain_data) {
                                                redir("action=domains", "clientarea.php");
                                              }

                                              $success = $domains->moduleCall("SaveContactDetails", array("contactdetails" => $contactdetails));

                                              if (!$success) {
                                                if ($domains->getLastError() == "Function not found") {
                                                  $errors[] = $domain." ".$_LANG['domaincannotbemanaged'];
                                                  continue;
                                                }

                                                $errors[] = $domains->getLastError();
                                                continue;
                                              }
                                            }
                                          }

                                          $smartyvalues['contacts'] = $client->getContactsWithAddresses();
                                          $domains = new WHMCS_Domains();
                                          $domain_data = $domains->getDomainsDatabyID($domainids[0]);

                                          if (!$domain_data) {
                                            redir("action=domains", "clientarea.php");
                                          }

                                          $success = $domains->moduleCall("GetContactDetails");

                                          if ($success) {
                                            $smartyvalues['contactdetails'] = $domains->getModuleReturn();
                                          }
                                        } else {
                                          if ($update == "renew") {
                                            redir("gid=renewals", "cart.php");
                                          } else {
                                            redir("action=domains");
                                          }
                                        }
                                      }
                                    }
                                  }

                                  $smartyvalues['errors'] = $errors;
                                } else {
                                  if ($action == "domainaddons") {
                                    check_token();
                                    $ca->setTemplate("clientareadomainaddons");
                                    $where = array("id" => $id, "userid" => $client->getID());
                                    $data = get_query_vals("tbldomains", "id,type,domain,registrationperiod,registrar,dnsmanagement,emailforwarding,idprotection", $where);
                                    $domainid = $data['id'];
                                    $domain = $data['domain'];

                                    if (!$domainid) {
                                      redir();
                                    }

                                    $smartyvalues['domainid'] = $domainid;
                                    $smartyvalues['domain'] = $data['domain'];
                                    $domainparts = explode(".", $data['domain'], 2);
                                    $result = select_query("tblpricing", "", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
                                    $pricingdata = mysql_fetch_array($result);
                                    $domaindnsmanagementprice = $pricingdata['msetupfee'];
                                    $domainemailforwardingprice = $pricingdata['qsetupfee'];
                                    $domainidprotectionprice = $pricingdata['ssetupfee'];
                                    $ca->assign("addonspricing", array("dnsmanagement" => formatCurrency($domaindnsmanagementprice), "emailforwarding" => formatCurrency($domainemailforwardingprice), "idprotection" => formatCurrency($domainidprotectionprice)));

                                    if ($disable) {
                                      $smartyvalues['action'] = "disable";
                                      $smartyvalues['addon'] = $disable;

                                      if ($disable == "dnsmanagement") {
                                        if (!$data['dnsmanagement']) {
                                          redir();
                                        }

                                        if ($confirm) {
                                          check_token();
                                          update_query("tbldomains", array("dnsmanagement" => "", "recurringamount" => "-=".$domaindnsmanagementprice), $where);
                                          $smartyvalues['success'] = true;
                                        }
                                      } else {
                                        if ($disable == "emailfwd") {
                                          if (!$data['emailforwarding']) {
                                            redir();
                                          }

                                          if ($confirm) {
                                            check_token();
                                            update_query("tbldomains", array("emailforwarding" => "", "recurringamount" => "-=".$domainemailforwardingprice), $where);
                                            $smartyvalues['success'] = true;
                                          }
                                        } else {
                                          if ($disable == "idprotect") {
                                            if (!$data['idprotection']) {
                                              redir();
                                            }

                                            if ($confirm) {
                                              check_token();
                                              update_query("tbldomains", array("idprotection" => "", "recurringamount" => "-=".$domainidprotectionprice), $where);
                                              $domainparts = explode(".", $domain, 2);
                                              $params = array();
                                              $params['domainid'] = $data['id'];
                                              $params['sld'] = $domainparts[0];
                                              $params['tld'] = $domainparts[1];
                                              $params['regperiod'] = $data['registrationperiod'];
                                              $params['registrar'] = $data['registrar'];
                                              $params['regtype'] = $data['type'];
                                              $values = RegIDProtectToggle($params);

                                              if ($values['error']) {
                                                $smartyvalues['error'] = true;
                                              } else {
                                                $smartyvalues['success'] = true;
                                              }
                                            }
                                          } else {
                                            if ($id) {
                                              redir("action=domaindetails&id=".$id);
                                            } else {
                                              redir();
                                            }
                                          }
                                        }
                                      }
                                    }

                                    if ($buy) {
                                      $smartyvalues['action'] = "buy";
                                      $smartyvalues['addon'] = $buy;
                                      $paymentmethod = getClientsPaymentMethod($client->getID());
                                      $domaintax = ($whmcs->get_config("TaxDomains") ? 1 : 0);
                                      $invdesc = "";

                                      if ($buy == "dnsmanagement") {
                                        if ($confirm) {
                                          $invdesc = $_LANG['domainaddons']." (".$_LANG['domainaddonsdnsmanagement'].") - ".$domain." - 1 ".$_LANG['orderyears'];
                                          $invamt = $domaindnsmanagementprice;
                                          $addontype = "DNS";
                                        }
                                      } else {
                                        if ($buy == "emailfwd") {
                                          if ($confirm) {
                                            $invdesc = $_LANG['domainaddons']." (".$_LANG['domainemailforwarding'].") - ".$domain." - 1 ".$_LANG['orderyears'];
                                            $invamt = $domainemailforwardingprice;
                                            $addontype = "EMF";
                                          }
                                        } else {
                                          if ($buy == "idprotect") {
                                            if ($confirm) {
                                              $invdesc = $_LANG['domainaddons']." (".$_LANG['domainidprotection'].") - ".$domain." - 1 ".$_LANG['orderyears'];
                                              $invamt = $domainidprotectionprice;
                                              $addontype = "IDP";
                                            }
                                          } else {
                                            if ($id) {
                                              redir("action=domaindetails&id=".$id);
                                            } else {
                                              redir();
                                            }
                                          }
                                        }
                                      }

                                      if ($invdesc) {
                                        check_token();
                                        insert_query("tblinvoiceitems", array("userid" => $client->getID(), "type" => "DomainAddon".$addontype, "relid" => $domainid, "description" => $invdesc, "amount" => $invamt, "taxed" => $domaintax, "duedate" => "now()", "paymentmethod" => $paymentmethod));

                                        if (!function_exists("createInvoices")) {
                                          require ROOTDIR."/includes/processinvoices.php";
                                        }

                                        $invoiceid = createInvoices($client->getID());

                                        if ($invoiceid) {
                                          redir("id=".$invoiceid, "viewinvoice.php");
                                        }

                                        redir();
                                      }
                                    }
                                  } else {
                                    redir();
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
}
}
}
}
}
}
}

$ca->output();
?>