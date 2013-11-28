<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_Admin {
	public $loginRequired = true;
    public $requiredPermission = "";
    public $title = "";
    public $sidebar = "";
    public $icon = "";
    public $helplink = "";
    public $jscode = "";
    public $internaljquerycode = array();
    public $jquerycode = "";
    public $template = "";
    public $content = "";
    public $templatevars = array();
    public $filename = "";
    public $rowLimit = 50;
    public $tablePagination = true;
    public $inClientsProfile = false;
    public $adminTemplate = "blend";
    public $exitmsg = "";
    public $language = "chinese";
    public $extrajscode = array();
    public $headOutput = array();
    public $chartFunctions = array();
    public $sortableTableCount = 0;
    public $smarty = "";

	public function __construct($reqpermission, $releaseSession = true) {
		global $CONFIG;
		global $licensing;
		global $_ADMINLANG;
		global $infobox;
		global $whmcs;

		$infobox = "";
		$licensing->remoteCheck();

		if ($licensing->getStatus() != "Active") {
			redir("licenseerror=" . $licensing->getStatus(), "licenseerror.php");
		}


		if ($CONFIG['AdminForceSSL'] && $CONFIG['SystemSSLURL']) {
			if (!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == "off") {
				$requesturl = $_SERVER['PHP_SELF'] . "?";
				foreach ($_REQUEST as $key => $value) {

					if (!is_array($value)) {
						$requesturl .= "" . $key . "=" . urlencode($value) . "&";
						continue;
					}
				}

				$requesturl = substr($requesturl, 0, 0 - 1);
				$requesturl = substr($requesturl, strrpos($requesturl, "/"));
				header("Location: " . $CONFIG['SystemSSLURL'] . "/" . $whmcs->get_admin_folder_name() . $requesturl);
				exit();
			}
		}


		if ($reqpermission == "loginonly") {
			$this->loginRequired = true;
		}
		else {
			if ($reqpermission) {
				$this->requiredPermission = $reqpermission;
			}
			else {
				$this->loginRequired = false;
			}
		}

		require ROOTDIR . "/includes/smarty/Smarty.class.php";

		if ($this->loginRequired) {
			$auth = new WHMCS_Auth();

			if (!$auth->isLoggedIn()) {
				$_SESSION['admloginurlredirect'] = html_entity_decode($_SERVER['REQUEST_URI']);
				redir("", "login.php");
			}

			$auth->getInfobyID($_SESSION['adminid']);

			if ($auth->isSessionPWHashValid()) {
				$auth->updateAdminLog();
				$this->adminTemplate = $auth->getAdminTemplate();

				if ($auth->getAdminLanguage()) {
					$this->language = $auth->getAdminLanguage();
				}
			}
			else {
				$auth->destroySession();
				redir("", "login.php");
			}
		}


		if ($releaseSession) {
			releaseSession();
		}


		if ($this->requiredPermission) {
			$permid = array_search($this->requiredPermission, getAdminPermsArray());
			$result = select_query("tbladmins", "roleid", array("id" => $_SESSION['adminid']));
			$data = mysql_fetch_array($result);
			$roleid = $data['roleid'];
			$result = select_query("tbladminperms", "COUNT(*)", array("roleid" => $roleid, "permid" => $permid));
			$data = mysql_fetch_array($result);
			$match = $data[0];

			if (!$match) {
				redir("permid=" . $permid, "accessdenied.php");
				exit();
			}
		}

		$filename = $_SERVER['PHP_SELF'];
		$filename = substr($filename, strrpos($filename, "/"));
		$filename = str_replace(array("/", ".php"), "", $filename);

		if (isset($_SESSION['adminid'])) {
			$twofa = new WHMCS_2FA();
			$twofa->setAdminID($_SESSION['adminid']);

			if ((($filename != "myaccount" && $twofa->isForced()) && !$twofa->isEnabled()) && $twofa->isActiveAdmins()) {
				redir("2faenforce=1", "myaccount.php");
			}
		}

		$this->filename = $filename;
		$this->rowLimit = $CONFIG['NumRecordstoDisplay'];

		if (isset($_SESSION['adminlang']) && $_SESSION['adminlang']) {
			$this->language = $_SESSION['adminlang'];
		}

		$this->language = $whmcs->validateLanguage($this->language, true);
		$whmcs->loadLanguage($this->language, true);
	}

	public function requiredFiles($reqfiles) {
		if (is_array($reqfiles)) {
			foreach ($reqfiles as $filename) {
				require ROOTDIR . "/includes/" . $filename . ".php";
			}
		}

	}

	public function setTemplate($tplname) {
		$this->template = $tplname;
	}

	public function assign($tplvar, $value = null) {
		$this->templatevars[$tplvar] = $value;
	}

	public function clientsDropDown($selectedval, $autosubmit = "", $fieldname = "userid", $anyoption = "") {
		global $CONFIG;

		if ($CONFIG['DisableClientDropdown']) {
			return "<input type=\"text\" name=\"" . $fieldname . "\" value=\"" . $selectedval . "\" size=\"10\" />";
		}

		$clientgroups = getClientGroups();
		$code = "<select name=\"" . $fieldname . "\"";

		if ($autosubmit) {
			$code .= " onChange=\"submit();\"";
		}

		$code .= ">";

		if ($anyoption) {
			$code .= "<option value=\"\">" . $this->lang("global", "any") . "</option>";
		}

		$orderby = "firstname` ASC,`lastname";

		if ($CONFIG['ClientDropdownFormat'] == 2) {
			$orderby = "companyname";
		}

		$result = select_query("tblclients", "id,firstname,lastname,companyname,groupid", "status='Active' OR id=" . (int)$selectedval, $orderby, "ASC");

		while ($data = mysql_fetch_array($result)) {
			$selectid = $data['id'];
			$selectfirstname = $data['firstname'];
			$selectlastname = $data['lastname'];
			$selectcompanyname = $data['companyname'];
			$selectgroup = $data['groupid'];
			$selectfield = "";

			if ($CONFIG['ClientDropdownFormat'] == 1) {
				$selectfield .= "" . $selectfirstname . " " . $selectlastname;

				if ($selectcompanyname) {
					$selectfield .= " (" . $selectcompanyname . ")";
				}
			}
			else {
				if ($CONFIG['ClientDropdownFormat'] == 2) {
					if ($selectcompanyname) {
						$selectfield .= "" . $selectcompanyname . " - ";
					}

					$selectfield .= "" . $selectfirstname . " " . $selectlastname;
				}
				else {
					$selectfield .= "#" . $selectid . " - " . $selectfirstname . " " . $selectlastname;

					if ($selectcompanyname) {
						$selectfield .= " - " . $selectcompanyname;
					}
				}
			}

			$code .= "<option value=\"" . $selectid . "\"";

			if (isset($clientgroups[$selectgroup]['colour'])) {
				$code .= " style=\"background-color:" . $clientgroups[$selectgroup]['colour'] . "\"";
			}


			if ($selectid == $selectedval) {
				$code .= " selected";
			}

			$code .= ">" . $selectfield . "</option>" . "\r\n";
		}

		$code .= "</select>";
		return $code;
	}

	public function productStatusDropDown($status, $anyop = false, $name = "status", $id = "") {
		$statuses = array("Pending", "Active", "Suspended", "Terminated", "Cancelled", "Fraud");
		$code = "<select name=\"" . $name . "\"" . ($id ? " id=\"" . $id . "\"" : "") . ">";

		if ($anyop) {
			$code .= "<option value=\"\">" . $this->lang("global", "any") . "</option>";
		}

		foreach ($statuses as $stat) {
			$code .= "<option value=\"" . $stat . "\"";

			if ($status == $stat) {
				$code .= " selected";
			}

			$code .= ">" . $this->lang("status", strtolower($stat)) . "</option>";
		}

		$code .= "</select>";
		return $code;
	}

	public function getTemplate($template) {
		global $whmcs;
		global $templates_compiledir;
		global $CONFIG;
		global $_ADMINLANG;

		$smarty = new Smarty();
		$smarty->template_dir = ROOTDIR . "/" . $whmcs->get_admin_folder_name() . "/templates/";
		$smarty->compile_dir = $templates_compiledir;
		$smarty->assign("_ADMINLANG", $_ADMINLANG);
		foreach ($this->templatevars as $key => $value) {
			$smarty->assign($key, $value);
		}

		$template_output = $smarty->fetch($this->adminTemplate . ("/" . $template . ".tpl"));
		return $template_output;
	}

	public function getTemplatePath() {
		global $whmcs;

		return ROOTDIR . "/" . $whmcs->get_admin_folder_name() . "/templates/";
	}

	public function display() {
		global $templates_compiledir;
		global $CONFIG;
		global $disable_admin_ticket_page_counts;
		global $_ADMINLANG;

		$this->smarty = new Smarty();
		$this->smarty->template_dir = $this->getTemplatePath();
		$this->smarty->compile_dir = $templates_compiledir;

		if ($this->inClientsProfile) {
			$this->title = "Clients Profile";
			$this->sidebar = "clients";
			$this->icon = "clientsprofile";
		}


		if (count($this->chartFunctions)) {
			$chartredrawjs = "function redrawCharts() { ";
			foreach ($this->chartFunctions as $chartfunc) {
				$chartredrawjs .= $chartfunc . "(); ";
			}

			$chartredrawjs .= "}";
			$this->extrajscode[] = $chartredrawjs;
			$this->extrajscode[] = "$(window).bind(\"resize\", function(event) { redrawCharts(); });";
		}

		$jquerycode = (count($this->internaljquerycode) ? implode("\r\n", $this->internaljquerycode) : "");

		if ($this->jquerycode) {
			$jquerycode .= "\r\n" . $this->jquerycode;
		}

		$this->assign("charset", $CONFIG['Charset']);
		$this->assign("template", $this->adminTemplate);
		$this->assign("pagetemplate", $this->template);

		if (isset($_SESSION['adminid'])) {
			$this->assign("adminid", $_SESSION['adminid']);
		}

		$this->assign("filename", $this->filename);
		$this->assign("pagetitle", $this->title);
		$this->assign("helplink", str_replace(" ", "_", $this->helplink));
		$this->assign("sidebar", $this->sidebar);
		$this->assign("minsidebar", (isset($_COOKIE['WHMCSMinSidebar']) ? true : false));
		$this->assign("pageicon", $this->icon);
		$this->assign("jquerycode", $jquerycode);
		$this->assign("jscode", $this->jscode . implode("\r\n", $this->extrajscode));
		$this->assign("_ADMINLANG", $_ADMINLANG);
		$this->assign("csrfToken", generate_token("plain"));
		$addonmodulesperms = unserialize($CONFIG['AddonModulesPerms']);
		$this->assign("datepickerformat", str_replace(array("DD", "MM", "YYYY"), array("dd", "mm", "yy"), $CONFIG['DateFormat']));

		if (isset($_SESSION['adminid'])) {
			$result = select_query("tbladmins", "firstname,lastname,notes,supportdepts,roleid", array("id" => $_SESSION['adminid']));
			$data = mysql_fetch_array($result);
			$admin_username = $data['firstname'] . " " . $data['lastname'];
			$admin_notes = $data['notes'];
			$admin_supportdepts = $data['supportdepts'];
			$admin_roleid = $data['roleid'];
			$this->assign("admin_username", ucfirst($admin_username));
			$this->assign("admin_notes", $admin_notes);
			$admin_perms = array();
			$adminpermsarray = getAdminPermsArray();
			$result = select_query("tbladminperms", "permid", array("roleid" => $admin_roleid));

			while ($data = mysql_fetch_array($result)) {
				$admin_perms[] = $adminpermsarray[$data[0]];
			}

			$this->assign("admin_perms", $admin_perms);
			$this->assign("addon_modules", $addonmodulesperms[$admin_roleid]);
		}

		$admins = "";
		$query = "SELECT DISTINCT adminusername FROM tbladminlog WHERE lastvisit>='" . date("Y-m-d H:i:s", mktime(date("H"), date("i") - 15, date("s"), date("m"), date("d"), date("Y"))) . "' AND logouttime='0000-00-00' ORDER BY lastvisit ASC";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$admins .= $data['adminusername'] . ", ";
		}

		$this->assign("adminsonline", substr($admins, 0, 0 - 2));
		$flaggedticketschecked = false;
		$flaggedtickets = 0;

		if ($this->sidebar == "support") {
			$allactive = $awaitingreply = 0;
			$ticketcounts = array();
			$admin_supportdepts_qry = array();
			$admin_supportdepts = explode(",", $admin_supportdepts);
			foreach ($admin_supportdepts as $deptid) {

				if (trim($deptid)) {
					$admin_supportdepts_qry[] = (int)$deptid;
					continue;
				}
			}


			if (count($admin_supportdepts_qry) < 1) {
				$admin_supportdepts_qry[] = 0;
			}


			if ($disable_admin_ticket_page_counts) {
				$query = "SELECT tblticketstatuses.title,'x',showactive,showawaiting FROM tblticketstatuses ORDER BY sortorder ASC";
			}
			else {
				$query = "SELECT tblticketstatuses.title,(SELECT COUNT(tbltickets.id) FROM tbltickets WHERE did IN (" . db_build_in_array($admin_supportdepts_qry) . ") AND tbltickets.status=tblticketstatuses.title),showactive,showawaiting FROM tblticketstatuses ORDER BY sortorder ASC";
			}

			$result = full_query($query);

			while ($data = mysql_fetch_array($result)) {
				$ticketcounts[] = array("title" => $data[0], "count" => $data[1]);

				if ($data['showactive']) {
					$allactive += $data[1];
				}


				if ($data['showawaiting']) {
					$awaitingreply += $data[1];
				}
			}


			if (!$disable_admin_ticket_page_counts) {
				$result = select_query("tbltickets", "COUNT(*)", "status!='Closed' AND flag='" . (int)$_SESSION['adminid'] . "'");
				$data = mysql_fetch_array($result);
				$flaggedtickets = $data[0];
				$flaggedticketschecked = true;
			}

			$this->assign("ticketsallactive", $allactive);
			$this->assign("ticketsawaitingreply", $awaitingreply);
			$this->assign("ticketsflagged", $flaggedtickets);
			$this->assign("ticketcounts", $ticketcounts);
			$this->assign("ticketstatuses", $ticketcounts);
			$departments = array();
			$result = select_query("tblticketdepartments", "id,name", "id IN (" . db_build_in_array($admin_supportdepts_qry) . ")", "order", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$departments[] = array("id" => $data['id'], "name" => $data['name']);
			}

			$this->assign("ticketdepts", $departments);
		}


		if (checkPermission("Sidebar Statistics", true)) {
			$templatevars = array();
			$pendingorderstatuses = array();
			$result = select_query("tblorderstatuses", "title", "showpending=1");

			while ($data = mysql_fetch_array($result)) {
				$pendingorderstatuses[] = $data['title'];
			}

			$query = "SELECT COUNT(*) FROM tblorders INNER JOIN tblclients ON tblclients.id=tblorders.userid WHERE tblorders.status IN (" . db_build_in_array($pendingorderstatuses) . ")";
			$result = full_query($query);
			$data = mysql_fetch_array($result);
			$templatevars['orders']['pending'] = $data[0];
			$templatevars['clients']['active'] = $templatevars['clients']['inactive'] = $templatevars['clients']['closed'] = 0;
			$query = "SELECT status,COUNT(*) FROM tblclients GROUP BY status";
			$result = full_query($query);

			while ($data = mysql_fetch_array($result)) {
				$templatevars['clients'][strtolower($data[0])] = $data[1];
			}

			$templatevars['services']['pending'] = $templatevars['services']['active'] = $templatevars['services']['suspended'] = $templatevars['services']['terminated'] = $templatevars['services']['cancelled'] = $templatevars['services']['fraud'] = 0;
			$query = "SELECT domainstatus,COUNT(*) FROM tblhosting GROUP BY domainstatus";
			$result = full_query($query);

			while ($data = mysql_fetch_array($result)) {
				$templatevars['services'][strtolower($data[0])] = $data[1];
			}

			$templatevars['domains']['pending'] = $templatevars['domains']['active'] = $templatevars['domains']['pendingtransfer'] = $templatevars['domains']['expired'] = $templatevars['domains']['cancelled'] = $templatevars['domains']['fraud'] = 0;
			$query = "SELECT status,COUNT(*) FROM tbldomains GROUP BY status";
			$result = full_query($query);

			while ($data = mysql_fetch_array($result)) {
				$templatevars['domains'][str_replace(" ", "", strtolower($data[0]))] = $data[1];
			}

			$query = "SELECT COUNT(id) FROM tblinvoices WHERE status='Unpaid'";
			$result = full_query($query);
			$data = mysql_fetch_array($result);
			$templatevars['invoices']['unpaid'] = $data[0];
			$query = "SELECT COUNT(id) FROM tblinvoices WHERE status='Unpaid' AND duedate<'" . date("Ymd") . "'";
			$result = full_query($query);
			$data = mysql_fetch_array($result);
			$templatevars['invoices']['overdue'] = $data[0];

			if (!$disable_admin_ticket_page_counts) {
				$query = "SELECT COUNT(*) FROM tbltickets WHERE status!='Closed'";
				$result = full_query($query);
				$data = mysql_fetch_array($result);
				$templatevars['tickets']['active'] = $data[0];
				$query = "SELECT COUNT(*) FROM tbltickets WHERE status IN (SELECT title FROM `tblticketstatuses` WHERE showawaiting = '1')";
				$result = full_query($query);
				$data = mysql_fetch_array($result);
				$templatevars['tickets']['awaitingreply'] = $data[0];

				if ($flaggedticketschecked) {
					$templatevars['tickets']['flagged'] = $flaggedtickets;
				}
				else {
					$query = "SELECT COUNT(*) FROM tbltickets WHERE status!='Closed' AND flag='" . (int)$_SESSION['adminid'] . "'";
					$result = full_query($query);
					$data = mysql_fetch_array($result);
					$templatevars['tickets']['flagged'] = $data[0];
				}

				$ticketstats = array();
				$query = "SELECT status,COUNT(*) FROM tbltickets GROUP BY status";
				$result = full_query($query);

				while ($data = mysql_fetch_array($result)) {
					$ticketstats[$data[0]] = $data[1];
				}

				$templatevars['tickets']['onhold'] = (array_key_exists("On Hold", $ticketstats) ? $ticketstats["On Hold"] : "0");
				$templatevars['tickets']['inprogress'] = (array_key_exists("In Progress", $ticketstats) ? $ticketstats["In Progress"] : "0");
			}

			$this->assign("sidebarstats", $templatevars);
		}

		$this->assignToSmarty();
		$this->output();
	}

	public function assignToSmarty() {
		foreach ($this->templatevars as $key => $value) {
			$this->smarty->assign($key, $value);
		}

	}

	public function output() {
		global $whmcs;

		$hookvars = $this->templatevars;
		unset($hookvars['_ADMINLANG']);
		$hookres = run_hook("AdminAreaPage", $hookvars);
		foreach ($hookres as $arr) {
			foreach ($arr as $k => $v) {
				$hookvars[$k] = $v;
				$this->smarty->assign($k, $v);
			}
		}

		$hookres = run_hook("AdminAreaHeadOutput", $hookvars);
		$headoutput = (count($this->headOutput) ? implode("\r\n", $this->headOutput) : "");

		if (count($hookres)) {
			$headoutput .= "\r\n" . implode("\r\n", $hookres);
		}

		$this->smarty->assign("headoutput", $headoutput);
		$hookres = run_hook("AdminAreaHeaderOutput", $hookvars);
		$headeroutput = (count($hookres) ? implode("\r\n", $hookres) : "");
		$this->smarty->assign("headeroutput", $headeroutput);
		$hookres = run_hook("AdminAreaFooterOutput", $hookvars);
		$footeroutput = (count($hookres) ? implode("\r\n", $hookres) : "");
		$this->smarty->assign("footeroutput", $footeroutput);
		$this->smarty->display($this->adminTemplate . "/header.tpl");

		if ($this->inClientsProfile) {
			$this->profileHeader();
		}

		$content = $this->content;

		if ($this->template) {
			$content = $this->smarty->fetch($this->adminTemplate . "/" . $this->template . ".tpl");
		}


		if ($whmcs->getCurrentFilename() != "systemintegrationcode") {
			$content = preg_replace("/(<form\W[^>]*\bmethod=('|\"|)POST('|\"|)\b[^>]*>)/i", "$1" . "\r\n" . generate_token(), $content);

		}


		if ($this->exitmsg) {
			$content = $this->exitmsg;
		}

		echo $content;

		if ($this->inClientsProfile) {
			echo "</div></div>";
		}

		$this->smarty->display($this->adminTemplate . "/footer.tpl");
	}

	public function displayPopUp() {
		global $CONFIG;

		$content = $this->content;
		$content = preg_replace("/(<form\W[^>]*\bmethod=('|\"|)POST('|\"|)\b[^>]*>)/i", "$1" . "\r\n" . generate_token(), $content);

		echo "<html>
<head>
<title>WHMCS - " . $this->title . "</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $CONFIG['Charset'] . "\">
<link href=\"templates/" . $this->adminTemplate . "/style.css\" rel=\"stylesheet\" type=\"text/css\">
<link href=\"../includes/jscript/css/ui.all.css\" rel=\"stylesheet\" type=\"text/css\" />
<script type=\"text/javascript\" src=\"../includes/jscript/jquery.js\"></script>
<script type=\"text/javascript\" src=\"../includes/jscript/jqueryui.js\"></script>
<script type=\"text/javascript\" src=\"../includes/jscript/textext.js\"></script>
<script>
$(document).ready(function(){
    $(\".datepick\").datepicker({
        dateFormat: \"" . str_replace(array("DD", "MM", "YYYY"), array("dd", "mm", "yy"), $CONFIG['DateFormat']) . "\",
        showOn: \"button\",
        buttonImage: \"images/showcalendar.gif\",
        buttonImageOnly: true,
        showButtonPanel: true,
        showOtherMonths: true,
		selectOtherMonths: true
    });
});
</script>
</head>
<body style=\"margin:0px\">

<table width=\"100%\" bgcolor=\"#ffffff\" cellpadding=\"15\"><tr><td>

<h2>" . $this->title . "</h2>

" . $content . "

</td></tr></table>

</body>
</html>";
	}

	public function Tabs($tabs = array(), $firsttabhidden = false) {
		$jquerycode = "$(\".tabbox\").css(\"display\",\"none\");
var selectedTab;
$(\".tab\").click(function(){
    var elid = $(this).attr(\"id\");
    $(\".tab\").removeClass(\"tabselected\");
    $(\"#\"+elid).addClass(\"tabselected\");
    ";

		if (!$firsttabhidden) {
			$jquerycode .= "if (elid != selectedTab) {
        $(\".tabbox\").slideUp();
        $(\"#\"+elid+\"box\").slideDown();
        selectedTab = elid;
    }
    ";
		}
		else {
			$jquerycode .= "$(\".tabbox\").slideUp();
    if (elid != selectedTab) {
        selectedTab = elid;
        $(\"#\"+elid+\"box\").slideDown();
    } else {
        selectedTab = null;
        $(\".tab\").removeClass(\"tabselected\");
    }
    ";
		}

		$jquerycode .= "$(\"#tab\").val(elid.substr(3));
});
";

		if (!$firsttabhidden || isset($_REQUEST['tab'])) {
			$tabnumber = 0;

			if ($_REQUEST['tab']) {
				$tabnumber = $_REQUEST['tab'];
			}

			$jquerycode .= "selectedTab = \"tab" . $tabnumber . "\";
$(\"#tab" . $tabnumber . "\").addClass(\"tabselected\");
$(\"#tab" . $tabnumber . "box\").css(\"display\",\"\");";
		}

		$content = "<div id=\"tabs\"><ul>";
		foreach ($tabs as $i => $tab) {
			$content .= "<li id=\"tab" . $i . "\" class=\"tab\"><a href=\"javascript:;\">" . $tab . "</a></li>";
		}

		$content .= "</ul></div>
        ";
		$this->internaljquerycode[] = $jquerycode;
		return $content;
	}

	public function sortableTableInit($defaultsort, $defaultorder = "ASC") {
		global $orderby;
		global $order;
		global $page;
		global $limit;
		global $tabledata;

		$sortpage = $this->filename;

		if ($defaultsort == "nopagination") {
			$this->tablePagination = false;
		}
		else {
			$this->tablePagination = true;
			$sortdata = (isset($_COOKIE['sortdata']) ? $_COOKIE['sortdata'] : "");
			$sortdata = json_decode(base64_decode($sortdata), true);

			if (!is_array($sortdata)) {
				$sortdata = array();
			}

			$xorderby = $sortdata[$sortpage . "orderby"];
			$xorder = $sortdata[$sortpage . "order"];

			if (!$xorderby) {
				$xorderby = $defaultsort;
			}


			if (!$xorder) {
				$xorder = $defaultorder;
			}


			if ($xorderby == $orderby) {
				if ($xorder == "ASC") {
					$xorder = "DESC";
				}
				else {
					$xorder = "ASC";
				}
			}


			if ($orderby) {
				$xorderby = $orderby;
			}

			$xorderby = trim(preg_replace("/[^a-z]/", "", strtolower($xorderby)));

			if (!in_array($xorder, array("ASC", "DESC"))) {
				$xorder = ($defaultorder ? $defaultorder : "ASC");
			}

			$sortdata[$sortpage . "orderby"] = $xorderby;
			$sortdata[$sortpage . "order"] = $xorder;
			$orderby = db_escape_string($xorderby);
			$order = db_escape_string($xorder);
			setcookie("sortdata", base64_encode(json_encode($sortdata)));
		}


		if (!$page) {
			$page = 0;
		}

		$limit = $this->rowLimit;
		$this->sortableTableCount++;
		$tabledata = array();
	}

	public function sortableTable($columns, $tabledata, $formurl = "", $formbuttons = "", $topbuttons = "") {
		global $orderby;
		global $order;
		global $numrows;
		global $page;

		$pages = ceil($numrows / $this->rowLimit);

		if ($pages == 0) {
			$pages = 1;
		}

		$content = "";

		if ($this->tablePagination) {
			$varsrecall = "";
			foreach ($_REQUEST as $key => $value) {

				if (!in_array($key, array("orderby", "page", "PHPSESSID", "token")) && $value) {
					if (is_array($value)) {
						foreach ($value as $k => $v) {

							if ($v) {
								$varsrecall .= "<input type=\"hidden\" name=\"" . $key . "[" . $k . "]\" value=\"" . $v . "\" />" . "\r\n";
								continue;
							}
						}

						continue;
					}

					$varsrecall .= "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $value . "\" />" . "\r\n";
					continue;
				}
			}


			if ($varsrecall) {
				$varsrecall = "\r\n" . $varsrecall;
			}

			$content .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">" . $varsrecall . "
<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\"><tr>
<td width=\"50%\" align=\"left\">" . $numrows . " " . $this->lang("global", "recordsfound") . ", " . $this->lang("global", "page") . " " . ($page + 1) . " " . $this->lang("global", "of") . " " . $pages . "</td>
<td width=\"50%\" align=\"right\">" . $this->lang("global", "jumppage") . ": <select name=\"page\" onchange=\"submit()\">";
			$i = 1;

			while ($i <= $pages) {
				$newpage = $i - 1;
				$content .= "<option value=\"" . $newpage . "\"";

				if ($page == $newpage) {
					$content .= " selected";
				}

				$content .= ">" . $i . "</option>";
				++$i;
			}

			$content .= "</select> <input type=\"submit\" value=\"" . $this->lang("global", "go") . "\" class=\"btn-small\" /></td>
</tr></table>
</form>
";
		}


		if ($formurl) {
			$content .= "<form method=\"post\" action=\"" . $formurl . "\">" . $varsrecall;
		}


		if ($topbuttons) {
			$content .= "<div style=\"padding-bottom:2px;\">" . $this->lang("global", "withselected") . ": " . $formbuttons . "</div>";
		}

		$content .= "
<div class=\"tablebg\">
<table id=\"sortabletbl" . $this->sortableTableCount . "\" class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\">
<tr>";
		foreach ($columns as $column) {

			if (is_array($column)) {
				$sortableheader = true;
				$columnid = $column[0];
				$columnname = $column[1];
				$width = $column[2];

				if (!$columnid) {
					$sortableheader = false;
				}
			}
			else {
				$sortableheader = false;
				$columnid = $width = "";
				$columnname = $column;
			}


			if (!$columnname) {
				$content .= "<th width=\"20\"></th>";
				continue;
			}


			if ($columnname == "checkall") {
				$this->internaljquerycode[] = "$(\"#checkall" . $this->sortableTableCount . "\").click(function () {
    $(\"#sortabletbl" . $this->sortableTableCount . " .checkall\").attr(\"checked\",this.checked);
});";
				$content .= "<th width=\"20\"><input type=\"checkbox\" id=\"checkall" . $this->sortableTableCount . "\"></th>";
				continue;
			}

			$width = ($width ? " width=\"" . $width . "\"" : "");
			$content .= "<th" . $width . ">";

			if ($sortableheader) {
				$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?";
				foreach ($_REQUEST as $key => $value) {

					if (($key != "orderby" && $key != "PHPSESSID") && $value) {
						$content .= "" . $key . "=" . $value . "&";
						continue;
					}
				}

				$content .= "orderby=" . $columnid . "\">";
			}

			$content .= $columnname;

			if ($sortableheader) {
				$content .= "</a>";

				if ($orderby == $columnid) {
					$content .= " <img src=\"images/" . strtolower($order) . ".gif\" class=\"absmiddle\" />";
				}
			}

			$content .= "</th>";
		}

		$content .= "</tr>\r\n";
		$totalcols = count($columns);

		if (is_array($tabledata) && count($tabledata)) {
			foreach ($tabledata as $tablevalues) {

				if ($tablevalues[0] == "dividingline") {
					$content .= "<tr><td colspan=\"" . $totalcols . "\" style=\"background-color:#efefef;\"><div align=\"left\"><b>" . $tablevalues[1] . "</b></div></td></tr>\r\n";
					continue;
				}

				$content .= "<tr>";
				foreach ($tablevalues as $tablevalue) {
					$content .= "<td>" . $tablevalue . "</td>";
				}

				$content .= "</tr>\r\n";
			}
		}
		else {
			$content .= "<tr><td colspan=\"" . $totalcols . "\">" . $this->lang("global", "norecordsfound") . "</td></tr>\r\n";
		}

		$content .= "</table>\r\n</div>\r\n";

		if ($formbuttons) {
			$content .= "" . $this->lang("global", "withselected") . ": " . $formbuttons . "\r\n</form>\r\n";
		}


		if ($this->tablePagination) {
			$content .= "<p align=\"center\">";

			if (0 < $page) {
				$prevoffset = $page - 1;
				$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?";
				foreach ($_REQUEST as $key => $value) {

					if ((($key != "orderby" && $key != "page") && $key != "PHPSESSID") && $value) {
						if (is_array($value)) {
							foreach ($value as $k => $v) {

								if ($v) {
									$content .= $key . (("[") . $k . "]=" . $v . "&");
									continue;
								}
							}

							continue;
						}

						$content .= "" . $key . "=" . $value . "&";
						continue;
					}
				}

				$content .= "page=" . $prevoffset . "\">" . $this->lang("global", "previouspage") . "</a> &nbsp; ";
			}
			else {
				$content .= $this->lang("global", "previouspage") . " &nbsp;";
			}


			if (($page * $this->rowLimit + $this->rowLimit) / $this->rowLimit == $pages) {
				$content .= $this->lang("global", "nextpage");
			}
			else {
				$newoffset = $page + 1;
				$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?";
				foreach ($_REQUEST as $key => $value) {

					if ((($key != "orderby" && $key != "page") && $key != "PHPSESSID") && $value) {
						if (is_array($value)) {
							foreach ($value as $k => $v) {

								if ($v) {
									$content .= $key . (("[") . $k . "]=" . $v . "&");
									continue;
								}
							}

							continue;
						}

						$content .= "" . $key . "=" . $value . "&";
						continue;
					}
				}

				$content .= "page=" . $newoffset . "\">" . $this->lang("global", "nextpage") . "</a>";
			}

			$content .= "</p>";
		}

		return $content;
	}

	public function profileHeader() {
		global $CONFIG;

		$uid = (int)$GLOBALS['userid'];
		$tabarray = array();
		$tabarray['clientssummary'] = $this->lang("clientsummary", "summary");
		$tabarray['clientsprofile'] = $this->lang("clientsummary", "profile");
		$tabarray['clientscontacts'] = $this->lang("clientsummary", "contacts");
		$tabarray['clientsservices'] = $this->lang("clientsummary", "products");
		$tabarray['clientsdomains'] = $this->lang("clientsummary", "domains");
		$tabarray['clientsbillableitems'] = $this->lang("clientsummary", "billableitems");
		$tabarray['clientsinvoices'] = $this->lang("clientsummary", "invoices");
		$tabarray['clientsquotes'] = $this->lang("clientsummary", "quotes");
		$tabarray['clientstransactions'] = $this->lang("clientsummary", "transactions");
		$tabarray['clientsemails'] = $this->lang("clientsummary", "emails");
		$tabarray['clientsnotes'] = $this->lang("clientsummary", "notes") . " (" . get_query_val("tblnotes", "COUNT(id)", array("userid" => $uid)) . ")";
		$tabarray['clientslog'] = $this->lang("clientsummary", "log");
		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"get\">\r\n<p>" . $this->lang("clientsummary", "activeclient") . ": ";

		if ($CONFIG['DisableClientDropdown']) {
			$result = select_query("tblclients", "", array("id" => $uid));
			$data = mysql_fetch_array($result);
			$selectfirstname = $data['firstname'];
			$selectlastname = $data['lastname'];
			$selectcompanyname = $data['companyname'];
			echo $selectfirstname . " " . $selectlastname;

			if ($selectcompanyname) {
				echo " (" . $selectcompanyname . ")";
			}
		}
		else {
			echo $this->clientsDropDown($uid, true);
			echo " <input type=\"submit\" value=\"Go\">";
		}

		echo "</p>\r\n</form>\r\n<div id=\"clienttabs\">\r\n<ul>";
		foreach ($tabarray as $link => $name) {

			if ($link == $this->filename) {
				$class = "tabselected";
			}
			else {
				$class = "tab";
			}

			echo "<li class=\"" . $class . "\"><a href=\"" . $link . ".php?userid=" . $_GET['userid'] . "\">" . $name . "</a></li>";
		}

		echo "</ul>\r\n</div>\r\n<div id=\"tab0box\" class=\"tabbox\">\r\n  <div id=\"tab_content\" style=\"text-align:left;\">";
	}

	public function gracefulExit($msg) {
		$this->exitmsg = "<div class=\"gracefulexit\">" . $msg . "</div>";
		$this->display();
		exit();
	}

	public function cyclesDropDown($billingcycle, $any = "", $freeop = "", $name = "billingcycle", $onchange = "") {
		if (!$freeop) {
			$freeop = $this->lang("billingcycles", "free");
		}


		if ($onchange) {
			$onchange = "onchange=\"" . $onchange . "\"";
		}

		$code = "<select name=\"" . $name . "\"" . $onchange . ">";

		if ($any) {
			$code .= "<option value=\"\">" . $this->lang("global", "any") . "</option>";
		}

		$code .= "<option value=\"Free Account\"";

		if ($billingcycle == "Free Account") {
			$code .= " selected";
		}

		$code .= ">" . $freeop . "</option>";
		$code .= "<option value=\"One Time\"";

		if ($billingcycle == "One Time") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "onetime") . "</option>";
		$code .= "<option value=\"Monthly\"";

		if ($billingcycle == "Monthly") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "monthly") . "</option>";
		$code .= "<option value=\"Quarterly\"";

		if ($billingcycle == "Quarterly") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "quarterly") . "</option>";
		$code .= "<option value=\"Semi-Annually\"";

		if ($billingcycle == "Semi-Annually") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "semiannually") . "</option>";
		$code .= "<option value=\"Annually\"";

		if ($billingcycle == "Annually") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "annually") . "</option>";
		$code .= "<option value=\"Biennially\"";

		if ($billingcycle == "Biennially") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "biennially") . "</option>";
		$code .= "<option value=\"Triennially\"";

		if ($billingcycle == "Triennially") {
			$code .= " selected";
		}

		$code .= ">" . $this->lang("billingcycles", "triennially") . "</option>";
		$code .= "</select>";
		return $code;
	}

	public function jqueryDialog($name, $title, $message, $buttons = array(), $height = "", $width = "", $alerttype = "alert") {
		static $dialogjsdone = false;

		$jquerycode = "$(\"#" . $name . "\").dialog({
    autoOpen: false,
    resizable: false,
    ";

		if ($height) {
			$jquerycode .= "height: " . $height . ",
    ";
		}


		if ($width) {
			$jquerycode .= "width: " . $width . ",
    ";
		}

		$jquerycode .= "modal: true,
	buttons: {";
		$buttoncode = "";
		foreach ($buttons as $k => $v) {

			if (!$v) {
				$v = "$(this).dialog('close');";
			}

			$buttoncode .= "'" . $k . "': function() {
		    " . $v . "
		},";
		}

		$jquerycode .= substr($buttoncode, 0, 0 - 1) . "}\r\n});\r\n";
		$this->internaljquerycode[] = $jquerycode;
		$alerticon = "";

		if ($alerttype == "alert") {
			$alerticon = "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 40px 0;\"></span>";
		}

		$htmlcode = "<div id=\"" . $name . "\" title=\"" . $title . "\" style=\"display:none;\">
	<p>" . $alerticon . $message . "</p>
</div>
";

		if (!$dialogjsdone) {
			$this->extrajscode[] = "function showDialog(name) {
$(\"#\"+name).dialog('open');
}";
		}

		$dialogjsdone = true;
		return $htmlcode;
	}

	public function outputClientLink($userid, $firstname = "", $lastname = "", $companyname = "", $groupid = "") {
		global $CONFIG;
		static $clientgroups = "";
		static $ClientOutputData = array();
		static $ContactOutputData = array();

		$contactid = 0;

		if (is_array($userid)) {
			$contactid = $userid[1];
			$userid = $userid[0];
		}


		if (!is_array($clientgroups)) {
			$clientgroups = getClientGroups();
		}


		if ((!$firstname && !$lastname) && !$companyname) {
			if (isset($ClientOutputData[$userid])) {
				$data = $ClientOutputData[$userid];
			}
			else {
				$result = select_query("tblclients", "firstname,lastname,companyname,groupid", array("id" => $userid));
				$data = mysql_fetch_array($result);
				$ClientOutputData[$userid] = $data;
			}

			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$groupid = $data['groupid'];

			if ($contactid) {
				if (isset($ContactOutputData[$contactid])) {
					$contactdata = $ContactOutputData[$contactid];
				}
				else {
					$contactdata = get_query_vals("tblcontacts", "firstname,lastname", array("id" => $contactid, "userid" => $userid));
					$ContactOutputData[$contactid] = $contactdata;
				}

				$firstname = $contactdata['firstname'];
				$lastname = $contactdata['lastname'];
			}
		}

		$style = (isset($clientgroups[$groupid]['colour']) ? " style=\"background-color:" . $clientgroups[$groupid]['colour'] . "\"" : "");
		$clientlink = "<a href=\"clientssummary.php?userid=" . $userid . "\"" . $style . ">";

		if ($CONFIG['ClientDisplayFormat'] == 2) {
			if ($companyname) {
				$clientlink .= $companyname;
			}
			else {
				$clientlink .= $firstname . " " . $lastname;
			}
		}
		else {
			if ($CONFIG['ClientDisplayFormat'] == 3) {
				$clientlink .= $firstname . " " . $lastname;

				if ($companyname) {
					$clientlink .= " (" . $companyname . ")";
				}
			}
			else {
				$clientlink .= $firstname . " " . $lastname;
			}
		}

		$clientlink .= "</a>";
		return $clientlink;
	}

	public function lang($section, $var, $escape = "") {
		global $_ADMINLANG;

		if ($escape) {
			return addslashes($_ADMINLANG[$section][$var]);
		}

		return isset($_ADMINLANG[$section][$var]) ? $_ADMINLANG[$section][$var] : (defined("DEVMODE") ? "Missing Language Var \"" . $section . "." . $var . "\"" : "");
	}

	public function deleteJSConfirm($name, $langtype, $langvar, $url) {
		$this->extrajscode[] = "function " . $name . "(id) {
if (confirm(\"" . $this->lang($langtype, $langvar, 1) . "\")) {
window.location='" . $url . "'+id+'" . generate_token("link") . "';
}}";
	}

	public function popupWindow($link, $width = "600", $height = "400", $output = true) {
		if (!$this->popupwincount) {
			$this->popupwincount = 0;
		}

		$this->popupwincount++;
		$this->extrajscode[] = "function popupWin" . $this->popupwincount . "() {
    var winl = (screen.width - " . $width . ") / 2;
    var wint = (screen.height - " . $height . ") / 2;
    win = window.open('" . $link . "', 'popwin" . $this->popupwincount . "', 'height=" . $height . ",width=" . $width . ",top='+wint+',left='+winl+',scrollbars=yes');
}";

		if ($output) {
			echo "popupWin" . $this->popupwincount . "();return false";
		}

	}

	public function valUserID($tempuid) {
		global $userid;
		global $clientsdetails;

		$userid = (int)$tempuid;

		if (!function_exists("getClientsDetails")) {
			require ROOTDIR . "/includes/clientfunctions.php";
		}

		$clientsdetails = getClientsDetails($userid);
		$userid = $_REQUEST['userid'] = $_POST['userid'] = $_GET['userid'] = $clientsdetails['userid'];

		if (!$userid) {
			$this->gracefulExit($this->lang("clients", "invalidclientid"));
		}

	}

	public function richTextEditor() {
		echo "<script type=\"text/javascript\" src=\"../includes/jscript/tiny_mce/jquery.tinymce.js\"></script>
<script type=\"text/javascript\">
	$().ready(function() {
		$(\"textarea.tinymce\").tinymce({
			// Location of TinyMCE script
			script_url : \"../includes/jscript/tiny_mce/tiny_mce.js\",

			// General options
			theme : \"advanced\",
			plugins : \"autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,advlist\",

			// Theme options
			theme_advanced_buttons1 : \"fontselect,fontsizeselect,forecolor,backcolor,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code\",
			theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
			theme_advanced_toolbar_location : \"top\",
			theme_advanced_toolbar_align : \"left\",
			theme_advanced_statusbar_location : \"bottom\",
			theme_advanced_resizing : true,
            convert_urls : false,
	        relative_urls : false,
            forced_root_block : false
		});
	});

function toggleEditor() {
    if ($(\"textarea.tinymce\").tinymce().isHidden()) {
        $(\"textarea.tinymce\").tinymce().show();
    } else {
        $(\"textarea.tinymce\").tinymce().hide();
    }
}

function insertMergeField(mfield) {
    $(\"#email_msg1\").tinymce().execCommand(\"mceInsertContent\",false,'{\$'+mfield+'}');
}

</script>
";
	}

	public function productDropDown($pid = 0, $noneopt = "", $anyopt = "") {
		global $aInt;

		$code = "";

		if ($anyopt) {
			$code .= "<option value=\"\">" . $aInt->lang("global", "any") . "</option>";
		}


		if ($noneopt) {
			$code .= "<option value=\"\">" . $aInt->lang("global", "none") . "</option>";
		}

		$groupname = "";
		$result = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.name,tblproducts.retired,tblproductgroups.name AS groupname", "", "tblproductgroups`.`order` ASC,`tblproducts`.`order` ASC,`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");

		while ($data = mysql_fetch_array($result)) {
			$packid = $data['id'];
			$gid = $data['gid'];
			$name = $data['name'];
			$packtype = $data['groupname'];

			if ($packtype != $groupname) {
				if (!$groupname) {
					$code .= "</optgroup>";
				}

				$code .= "<optgroup label=\"" . $packtype . "\">";
				$groupname = $packtype;
			}


			if (!$data['retired'] || $pid == $packid) {
				$code .= "<option value=\"" . $packid . "\"";

				if ($pid == $packid) {
					$code .= " selected";
				}

				$code .= ">" . $name . "</option>";
			}
		}

		$code .= "</optgroup>";
		return $code;
	}

	public function dialog($funccall = "", $content = "") {
		if (!$content) {
			$content = "<div style=\"padding:70px;text-align:center;\"><img src=\"images/loader.gif\" /></div>";
		}


		if ($funccall) {
			$content .= "<form><input type=\"hidden\" name=\"" . $funccall . "\" value=\"1\" /></form>";
		}

		$this->extrajscode[] = "

var dialoginit = false;

$(window).resize(function() {
  dialogCenter();
});

function dialogOpen() {

    $(\"body\").css(\"overflow\",\"hidden\");

    if (!dialoginit) {

    $(\"body\").append(\"<div id=\\\"bgfilter\\\"></div>\");
    $(\"#bgfilter\").css(\"position\",\"absolute\").css(\"top\",\"0\").css(\"left\",\"0\").css(\"width\",\"100%\").css(\"height\",$(\"body\").height()).css(\"background-color\",\"#ccc\").css(\"display\",\"block\").css(\"filter\",\"alpha(opacity=70)\").css(\"-moz-opacity\",\"0.7\").css(\"-khtml-opacity\",\"0.7\").css(\"opacity\",\"0.7\").css(\"z-index\",\"1000\");

    $(\"body\").append(\"<div class=\\\"admindialog\\\" id=\\\"dl1\\\"><a href=\\\"#\\\" onclick=\\\"dialogClose();return false\\\" class=\\\"close\\\">x</a><div id=\\\"admindialogcont\\\">" . addslashes($content) . "</div></div>\");
		$(\"#dl1\").css(\"position\",\"absolute\");
    $(\"#dl1\").css(\"z-index\",\"1001\");

    dialoginit = true;

    } else {

    $(\"#dl1\").html(\"<a href=\\\"#\\\" onclick=\\\"dialogClose();return false\\\" class=\\\"close\\\">x</a><div id=\\\"admindialogcont\\\">" . addslashes($content) . "</div>\");

		}

		dialogCenter();
		$(\"#dl1\").show();

    " . ($funccall ? "dialogSubmit();" : "") . "

}

function dialogCenter() {
    $(\"#dl1\").css(\"top\",Math.max(50, (($(window).height() - $(\"#dl1\").outerHeight()) / 2) + $(window).scrollTop() - 100) + \"px\");
    $(\"#dl1\").css(\"left\",Math.max(0, (($(window).width() - $(\"#dl1\").outerWidth()) / 2) + $(window).scrollLeft()) + \"px\");
}

function dialogSubmit() {
    $.post(\"" . $_SERVER['PHP_SELF'] . "\", $(\"#admindialogcont\").find(\"form\").serialize(),
    function(data){
        jQuery(\"#admindialogcont\").html(data);
        dialogCenter();
    });
}

function dialogClose() {
    $(\"#dl1\").fadeOut(\"\",function() {
        $(\"#bgfilter\").fadeOut();
        $(\"body\").css(\"overflow\",\"inherit\");
    });
}

function dialogChangeTab(id) {
    $(\"#admindialogcont .content .boxy\").fadeOut();
    $(\"#admindialogcont .content .boxy\").promise().done(function() {
        $(\"#admindialogcont .content .boxy\").hide();
        $(\"#\"+id).fadeIn();
    });
}

";
		}

		public function addHeadOutput($output) {
			$this->headOutput[] = $output;
			return true;
		}
	}

?>