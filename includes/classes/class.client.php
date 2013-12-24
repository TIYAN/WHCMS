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

class WHMCS_Client {
	private $userid = "";

	public function __construct($userid) {
		$this->setID($userid);
		return $this;
	}

	public function setID($userid) {
		$this->userid = $userid;
		return true;
	}

	public function getID() {
		return (int)$this->userid;
	}

	public function getUneditableClientProfileFields() {
		global $whmcs;

		return explode(",", $whmcs->get_config("ClientsProfileUneditableFields"));
	}

	public function isEditableField($field) {
		$uneditablefields = (defined("CLIENTAREA") ? $this->getUneditableClientProfileFields() : array());
		return !in_array($field, $uneditablefields) ? true : false;
	}

	public function getDetails() {
		return getClientsDetails($this->getID());
	}

	public function getCurrency() {
		return getCurrency($this->getID());
	}

	public function updateClient() {
		global $whmcs;

		$exinfo = getClientsDetails($this->getID());

		if (defined("ADMINAREA")) {
			$updatefieldsarray = array();
		}
		else {
			$updatefieldsarray = array("firstname" => "First Name", "lastname" => "Last Name", "companyname" => "Company Name", "email" => "Email Address", "address1" => "Address 1", "address2" => "Address 2", "city" => "City", "state" => "State", "postcode" => "Postcode", "country" => "Country", "phonenumber" => "Phone Number", "billingcid" => "Billing Contact");

			if ($whmcs->get_config("AllowClientsEmailOptOut")) {
				$updatefieldsarray['emailoptout'] = "Newsletter Email Opt Out";
			}
		}

		$changelist = array();
		$updateqry = array();
		foreach ($updatefieldsarray as $field => $displayname) {

			if ($this->isEditableField($field)) {
				$value = $whmcs->get_req_var($field);

				if ($field == "emailoptout" && !$value) {
					$value = "0";
				}

				$updateqry[$field] = $value;

				if ($value != $exinfo[$field]) {
					$changelist[] = "" . $displayname . ": '" . $exinfo[$field] . "' to '" . $value . "'";
					continue;
				}

				continue;
			}
		}

		update_query("tblclients", $updateqry, array("id" => $this->getID()));
		$old_customfieldsarray = getCustomFields("client", "", $this->getID(), "", "");
		$customfields = getCustomFields("client", "", $this->getID(), "", "");
		foreach ($customfields as $v) {
			$k = $v['id'];
			$customfieldsarray[$k] = $_POST['customfield'][$k];
		}

		saveCustomFields($this->getID(), $customfieldsarray);
		$paymentmethod = $whmcs->get_req_var("paymentmethod");
		clientChangeDefaultGateway($this->getID(), $paymentmethod);

		if ($paymentmethod != $exinfo['defaultgateway']) {
			$changelist[] = "Default Payment Method: '" . getGatewayName($exinfo['defaultgateway']) . "' to '" . getGatewayName($paymentmethod) . "'<br>
";
		}

		run_hook("ClientEdit", array_merge(array("userid" => $this->getID(), "olddata" => $exinfo), $updateqry));

		if (!defined("ADMINAREA") && $whmcs->get_config("SendEmailNotificationonUserDetailsChange")) {
			foreach ($old_customfieldsarray as $values) {

				if ($values['value'] != $_POST['customfield'][$values['id']]) {
					$changelist[] = $values['name'] . ": '" . $values['value'] . "' to '" . $_POST['customfield'][$values['id']] . "'";
					continue;
				}
			}


			if (0 < count($changelist)) {
				$adminurl = ($whmcs->get_config("SystemSSLURL") ? $whmcs->get_config("SystemSSLURL") : $whmcs->get_config("SystemURL"));
				$adminurl .= "/" . $whmcs->get_admin_folder_name() . "/clientssummary.php?userid=" . $this->getID();
				sendAdminNotification("account", "WHMCS User Details Change", "<p>Client ID: <a href=\"" . $adminurl . "\">" . $this->getID() . " - " . $exinfo['firstname'] . " " . $exinfo['lastname'] . "</a> has requested to change his/her details as indicated below:<br><br>" . implode("<br />
", $changelist) . "<br>If you are unhappy with any of the changes, you need to login and revert them - this is the only record of the old details.</p>");
				logActivity("Client Profile Modified - " . implode(", ", $changelist) . " - User ID: " . $this->getID());
			}
		}

		return true;
	}

	public function getContactsWithAddresses() {
		$where = array();
		$where['userid'] = $this->userid;
		$where['address1'] = array("sqltype" => "NEQ", "value" => "");
		return $this->getContactsData($where);
	}

	public function getContacts() {
		$where = array();
		$where['userid'] = $this->userid;
		return $this->getContactsData($where);
	}

	private function getContactsData($where) {
		$contactsarray = array();
		$result = select_query("tblcontacts", "id,firstname,lastname,email", $where, "firstname` ASC,`lastname", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$contactsarray[] = array("id" => $data['id'], "name" => $data['firstname'] . " " . $data['lastname'], "email" => $data['email']);
		}

		return $contactsarray;
	}

	public function getContact($contactid) {
		$result = select_query("tblcontacts", "", array("userid" => $this->userid, "id" => $contactid));
		$data = mysql_fetch_assoc($result);
		$data['permissions'] = explode(",", $data['permissions']);
		return isset($data['id']) ? $data : false;
	}

	public function deleteContact($contactid) {
		delete_query("tblcontacts", array("userid" => $this->userid, "id" => $contactid));
		update_query("tblclients", array("billingcid" => ""), array("billingcid" => $contactid, "id" => $this->userid));
		run_hook("ContactDelete", array("userid" => $this->userid, "contactid" => $contactid));
		return true;
	}

	public function getFiles() {
		$where = array("userid" => $this->userid);

		if (!defined("ADMINAREA")) {
			$where['adminonly'] = "";
		}

		$files = array();
		$result = select_query("tblclientsfiles", "", $where, "title", "ASC");

		while ($data = mysql_fetch_assoc($result)) {
			$id = $data['id'];
			$title = $data['title'];
			$adminonly = $data['adminonly'];
			$filename = $data['filename'];
			$filename = substr($filename, 11);
			$date = fromMySQLDate($data['dateadded'], 0, 1);
			$files[] = array("id" => $id, "date" => $date, "title" => $title, "adminonly" => $adminonly, "filename" => $filename);
		}

		return $files;
	}

	public function resetSendPW() {
		sendMessage("Automated Password Reset", $this->userid);
		return true;
	}

	public function sendEmailTpl($tplname) {
		return sendMessage($tplname, $this->userid);
	}

	public function getEmailTemplates() {
		$query = "SELECT * FROM tblemailtemplates WHERE type='general' AND language='' AND name!='Password Reset Validation' ORDER BY name ASC";
		full_query($query);
		$result = $emailtpls = array();

		while ($data = mysql_fetch_array($result)) {
			$messagename = $data['name'];
			$custom = $data['custom'];
			$emailtpls[] = array("name" => $messagename, "custom" => $custom);
		}

		return $emailtpls;
	}

	public function sendCustomEmail($subject, $msg) {
		delete_query("tblemailtemplates", array("name" => "Client Custom Email Msg"));
		insert_query("tblemailtemplates", array("type" => "general", "name" => "Client Custom Email Msg", "subject" => html_entity_decode($subject), "message" => html_entity_decode($message)));
		sendMessage("Client Custom Email Msg", $this->userid);
		delete_query("tblemailtemplates", array("name" => "Client Custom Email Msg"));
		return true;
	}
}

?>