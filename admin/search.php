<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("loginonly");

if ($a == "savenotes") {
	update_query("tbladmins", array("notes" => $notes), array("id" => $_SESSION['adminid']));
	exit();
}


if ($a == "minsidebar") {
	wSetCookie("MinSidebar", "1");
	exit();
}


if ($a == "maxsidebar") {
	wDelCookie("MinSidebar");
	exit();
}

$matches = $tempmatches = $invoicematches = $ticketmatches = "";

if ($intellisearch) {
	$value = trim($_POST['value']);

	if (strlen($value) < 3 && !is_numeric($value)) {
		exit();
	}

	$value = db_escape_string($value);

	if (checkPermission("List Clients", true) || checkPermission("View Clients Summary", true)) {
		$query = "SELECT id,firstname,lastname,companyname,email,status FROM tblclients WHERE concat(firstname,' ',lastname) LIKE '%" . $value . "%' OR companyname LIKE '%" . $value . "%' OR address1 LIKE '%" . $value . "%' OR address2 LIKE '%" . $value . "%' OR postcode LIKE '%" . $value . "%' OR phonenumber LIKE '%" . $value . "%'";

		if (is_numeric($value)) {
			$query .= " OR id='" . $value . "'";
		}


		if (is_numeric($value) && strlen($value) == 4) {
			$query .= " OR cardlastfour='" . $value . "'";
		}
		else {
			$query .= " OR city LIKE '%" . $value . "%' OR state LIKE '%" . $value . "%' OR email LIKE '%" . $value . "%'";
		}

		$query .= " LIMIT 0,10";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$userid = $data['id'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$email = $data['email'];
			$status = $data['status'];

			if ($companyname) {
				$companyname = " (" . $companyname . ")";
			}

			$tempmatches .= "<div class=\"searchresult\"><a href=\"clientssummary.php?userid=" . $userid . "\"><strong>" . $firstname . " " . $lastname . $companyname . "</strong> #" . $userid . " <span class=\"label " . strtolower($status) . ("\">" . $status . "</span><br /><span class=\"desc\">" . $email . "</span></a></div>");
		}


		if ($tempmatches) {
			$matches .= "<div class=\"searchresultheader\">Clients</div>" . $tempmatches;
		}

		$tempmatches = "";
		$query = "SELECT id,userid,firstname,lastname,companyname,email FROM tblcontacts WHERE concat(firstname,' ',lastname) LIKE '%" . $value . "%' OR companyname LIKE '%" . $value . "%' OR address1 LIKE '%" . $value . "%' OR address2 LIKE '%" . $value . "%' OR postcode LIKE '%" . $value . "%' OR phonenumber LIKE '%" . $value . "%'";

		if (is_numeric($value)) {
			$query .= " OR id='" . $value . "'";
		}
		else {
			$query .= " OR city LIKE '%" . $value . "%' OR state LIKE '%" . $value . "%' OR email LIKE '%" . $value . "%'";
		}

		$query .= " LIMIT 0,10";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$contactid = $data['id'];
			$userid = $data['userid'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$email = $data['email'];

			if ($companyname) {
				$companyname = " (" . $companyname . ")";
			}

			$tempmatches .= "<div class=\"searchresult\"><a href=\"clientscontacts.php?userid=" . $userid . "&contactid=" . $contactid . "\"><strong>" . $firstname . " " . $lastname . $companyname . "</strong> #" . $contactid . "<br /><span class=\"desc\">" . $email . "</span></a></div>";
		}


		if ($tempmatches) {
			$matches .= "<div class=\"searchresultheader\">Contacts</div>" . $tempmatches;
		}
	}


	if (checkPermission("List Services", true) || checkPermission("View Clients Products/Services", true)) {
		$tempmatches = "";
		$query = "SELECT tblclients.firstname,tblclients.lastname,tblclients.companyname,tblhosting.id,tblhosting.userid,tblhosting.domain,tblproducts.name,tblhosting.domainstatus FROM tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid WHERE ";

		if (is_numeric($value)) {
			$query .= "tblhosting.id='" . $value . "' OR";
		}

		$query .= " domain LIKE '%" . $value . "%' OR username LIKE '%" . $value . "%' OR dedicatedip LIKE '%" . $value . "%' OR tblhosting.notes LIKE '%" . $value . "%'";
		$query .= " LIMIT 0,10";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$productid = $data['id'];
			$userid = $data['userid'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];

			if ($companyname) {
				$companyname = " (" . $companyname . ")";
			}

			$domain = $data['domain'];
			$productname = $data['name'];

			if (!$domain) {
				$domain = "No Domain";
			}

			$status = $data['domainstatus'];
			$tempmatches .= "<div class=\"searchresult\"><a href=\"clientshosting.php?userid=" . $userid . "&id=" . $productid . "\"><strong>" . $productname . " - " . $domain . "</strong> <span class=\"label " . strtolower($status) . ("\">" . $status . "</span><br /><span class=\"desc\">" . $firstname . " " . $lastname . $companyname . " #" . $userid . "</span></a></div>");
		}


		if ($tempmatches) {
			$matches .= "<div class=\"searchresultheader\">Products/Services</div>" . $tempmatches;
		}
	}


	if (checkPermission("List Domains", true) || checkPermission("View Clients Domains", true)) {
		$tempmatches = "";
		$query = "SELECT tblclients.firstname,tblclients.lastname,tblclients.companyname,tbldomains.id,tbldomains.userid,tbldomains.domain,tbldomains.status FROM tbldomains INNER JOIN tblclients ON tblclients.id=tbldomains.userid WHERE ";

		if (is_numeric($value)) {
			$query .= "tbldomains.id='" . $value . "' OR";
		}

		$query .= " domain LIKE '%" . $value . "%' OR tbldomains.additionalnotes LIKE '%" . $value . "%'";
		$query .= " LIMIT 0,10";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$domainid = $data['id'];
			$userid = $data['userid'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];

			if ($companyname) {
				$companyname = " (" . $companyname . ")";
			}

			$domain = $data['domain'];

			if (!$domain) {
				$domain = "No Domain";
			}

			$status = $data['status'];
			$tempmatches .= "<div class=\"searchresult\"><a href=\"clientsdomains.php?userid=" . $userid . "&domainid=" . $domainid . "\"><strong>" . $domain . "</strong> <span class=\"label " . strtolower($status) . ("\">" . $status . "</span><br /><span class=\"desc\">" . $firstname . " " . $lastname . $companyname . " #" . $userid . "</span></a></div>");
		}


		if ($tempmatches) {
			$matches .= "<div class=\"searchresultheader\">Domains</div>" . $tempmatches;
		}
	}


	if (is_numeric($value)) {
		if (checkPermission("List Invoices", true) || checkPermission("Manage Invoice", true)) {
			$query = "SELECT tblclients.firstname,tblclients.lastname,tblclients.companyname,tblinvoices.id,tblinvoices.userid,tblinvoices.status FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.id='" . $value . "'";
			$result = full_query($query);

			while ($data = mysql_fetch_array($result)) {
				$invoiceid = $data['id'];
				$userid = $data['userid'];
				$firstname = $data['firstname'];
				$lastname = $data['lastname'];
				$companyname = $data['companyname'];
				$status = $data['status'];

				if ($companyname) {
					$companyname = " (" . $companyname . ")";
				}

				$id = $data['id'];
				$invoicematches .= "<div class=\"searchresult\"><a href=\"invoices.php?action=edit&id=" . $invoiceid . "\"><strong>Invoice #" . $id . "</strong> <span class=\"label " . strtolower($status) . ("\">" . $status . "</span><br><span class=\"desc\">" . $firstname . " " . $lastname . $companyname . " #" . $userid . "</span></a></div>");
			}
		}
	}


	if (checkPermission("List Support Tickets", true) || checkPermission("View Support Ticket", true)) {
		$query = "SELECT id,tid,title FROM tbltickets WHERE tbltickets.tid='" . $value . "' OR tbltickets.title LIKE '%" . $value . "%' ORDER BY lastreply DESC LIMIT 0,10";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$ticketid = $data['id'];
			$tid = $data['tid'];
			$title = $data['title'];
			$ticketmatches .= "<div class=\"searchresult\"><a href=\"supporttickets.php?action=viewticket&id=" . $ticketid . "\"><strong>Ticket #" . $tid . "</strong><br /><span class=\"desc\">" . $title . "</span></a></div>";
		}
	}


	if (checkPermission("List Invoices", true) || checkPermission("Manage Invoice", true)) {
		$query = "SELECT tblclients.firstname,tblclients.lastname,tblclients.companyname,tblinvoices.id,tblinvoices.userid,tblinvoices.status FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.invoicenum='" . $value . "'";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$invoiceid = $data['id'];
			$userid = $data['userid'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$status = $data['status'];

			if ($companyname) {
				$companyname = " (" . $companyname . ")";
			}

			$id = $data['id'];
			$invoicematches .= "<div class=\"searchresult\"><a href=\"invoices.php?action=edit&id=" . $invoiceid . "\"><strong>Invoice #" . $id . "</strong> <span class=\"label " . strtolower($status) . ("\">" . $status . "</span><br><span class=\"desc\">" . $firstname . " " . $lastname . $companyname . " #" . $userid . "</span></a></div>");
		}
	}


	if ($invoicematches) {
		$matches .= "<div class=\"searchresultheader\">Invoices</div>" . $invoicematches;
	}


	if ($ticketmatches) {
		$matches .= "<div class=\"searchresultheader\">Support Tickets</div>" . $ticketmatches;
	}


	if (!$matches) {
		$matches = "<div class=\"searchresultheader\">No Matches Found!</div>";
	}

	echo $matches;
	exit();
}


if ($clientsearch || $ticketclientsearch) {
	if ($clientsearch) {
		if (!checkPermission("List Clients", true)) {
			exit("Access Denied");
		}
	}


	if ($ticketclientsearch) {
		if (!checkPermission("List Support Tickets", true)) {
			exit("Access Denied");
		}
	}

	$value = trim($_POST['value']);

	if (strlen($value) < 3 || is_numeric($value)) {
		exit();
	}

	$value = db_escape_string($value);
	$tempmatches = "";
	$query = "SELECT id,firstname,lastname,companyname,email FROM tblclients WHERE concat(firstname,' ',lastname) LIKE '%" . $value . "%' OR companyname LIKE '%" . $value . "%' OR email LIKE '%" . $value . "%' LIMIT 0,5";
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		$userid = $data['id'];
		$firstname = $data['firstname'];
		$lastname = $data['lastname'];
		$companyname = $data['companyname'];
		$email = $data['email'];

		if ($companyname) {
			$companyname = " (" . $companyname . ")";
		}

		$tempmatches .= "<div class=\"searchresult\"><a href=\"#\" onclick=\"searchselectclient('" . $userid . "','" . addslashes($firstname . " " . $lastname . $companyname) . "','" . addslashes($email) . ("');return false\"><strong>" . $firstname . " " . $lastname . $companyname . "</strong> #" . $userid . "<br /><span class=\"desc\">" . $email . "</span></a></div>");
	}


	if ($tempmatches) {
		$matches .= "<div class=\"searchresultheader\">Search Results</div>" . $tempmatches;
	}


	if (!$matches) {
		$matches = "<div class=\"searchresultheader\">No Matches Found!</div>";
	}

	echo $matches;
	exit();
}


if ($type == "clients") {
	if ($field == "ID" || $field == "Client ID") {
		$searchin = "userid";
	}
	else {
		if (($field == "First Name" || $field == "Last Name") || $field == "Client Name") {
			$searchin = "clientname";
		}
		else {
			if ($field == "Company Name") {
				$searchin = "companyname";
			}
			else {
				if ($field == "Email Address") {
					$searchin = "email";
				}
				else {
					if ($field == "Address 1") {
						$searchin = "address";
					}
					else {
						if ($field == "Address 2") {
							$searchin = "address";
						}
						else {
							if ($field == "City") {
								$searchin = "address";
							}
							else {
								if ($field == "State") {
									$searchin = "address";
								}
								else {
									if ($field == "Postcode") {
										$searchin = "address";
									}
									else {
										if ($field == "Country") {
											$searchin = "country";
										}
										else {
											if ($field == "Phone Number") {
												$searchin = "phonenumber";
											}
											else {
												if ($field == "CC Last Four") {
													$searchin = "cardlastfour";
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

	header("Location: clients.php?" . $searchin . "=" . $q);
	exit();
	return 1;
}


if ($type == "orders") {
	if ($field == "Order ID") {
		header("Location: orders.php?orderid=" . $q);
	}
	else {
		if ($field == "Order #") {
			header("Location: orders.php?ordernum=" . $q);
		}
		else {
			if ($field == "Order Date") {
				header("Location: orders.php?orderdate=" . $q);
			}
			else {
				if ($field == "Client Name") {
					header("Location: orders.php?clientname=" . $q);
				}
				else {
					if ($field == "Amount") {
						header("Location: orders.php?amount=" . $q);
					}
				}
			}
		}
	}

	exit();
	return 1;
}


if ($type == "services") {
	if ($field == "ID" || $field == "Service ID") {
		header("Location: clientshostinglist.php?id=" . $q);
	}
	else {
		if ($field == "Domain") {
			header("Location: clientshostinglist.php?domain=" . $q);
		}
		else {
			if ($field == "Client Name") {
				header("Location: clientshostinglist.php?clientname=" . $q);
			}
			else {
				if ($field == "Package" || $field == "Product") {
					header("Location: clientshostinglist.php?packagesearch=" . $q);
				}
				else {
					if ($field == "Billing Cycle") {
						header("Location: clientshostinglist.php?billingcycle=" . $q);
					}
					else {
						if ($field == "Status") {
							header("Location: clientshostinglist.php?status=" . $q);
						}
						else {
							if ($field == "Username") {
								header("Location: clientshostinglist.php?username=" . $q);
							}
							else {
								if ($field == "Dedicated IP") {
									header("Location: clientshostinglist.php?dedicatedip=" . $q);
								}
								else {
									if ($field == "Assigned IPs") {
										header("Location: clientshostinglist.php?assignedips=" . $q);
									}
									else {
										if ($field == "Subscription ID") {
											header("Location: clientshostinglist.php?subscriptionid=" . $q);
										}
										else {
											if ($field == "Notes") {
												header("Location: clientshostinglist.php?notes=" . $q);
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

	exit();
	return 1;
}


if ($type == "domains") {
	if ($field == "ID" || $field == "Domain ID") {
		header("Location: clientsdomainlist.php?id=" . $q);
	}
	else {
		if ($field == "Domain") {
			header("Location: clientsdomainlist.php?domain=" . $q);
		}
		else {
			if ($field == "Client Name") {
				header("Location: clientsdomainlist.php?clientname=" . $q);
			}
			else {
				if ($field == "Registrar") {
					header("Location: clientsdomainlist.php?registrar=" . $q);
				}
				else {
					if ($field == "Status") {
						header("Location: clientsdomainlist.php?status=" . $q);
					}
					else {
						if ($field == "Subscription ID") {
							header("Location: clientsdomainlist.php?subscriptionid=" . $q);
						}
						else {
							if ($field == "Notes") {
								header("Location: clientsdomainlist.php?notes=" . $q);
							}
						}
					}
				}
			}
		}
	}

	exit();
	return 1;
}


if ($type == "invoices") {
	if ($field == "Invoice #") {
		header("Location: invoices.php?invoicenum=" . $q);
	}
	else {
		if ($field == "Client Name") {
			header("Location: invoices.php?clientname=" . $q);
		}
		else {
			if ($field == "Line Item") {
				header("Location: invoices.php?lineitem=" . $q);
			}
			else {
				if ($field == "Invoice Date") {
					header("Location: invoices.php?invoicedate=" . $q);
				}
				else {
					if ($field == "Due Date") {
						header("Location: invoices.php?duedate=" . $q);
					}
					else {
						if ($field == "Date Paid") {
							header("Location: invoices.php?datepaid=" . $q);
						}
						else {
							if ($field == "Total Due") {
								header("Location: invoices.php?totalfrom=" . $q . "&totalto=" . $q);
							}
							else {
								if ($field == "Status") {
									header("Location: invoices.php?staus=" . $q);
								}
							}
						}
					}
				}
			}
		}
	}

	exit();
	return 1;
}


if ($type == "tickets") {
	if ($field == "Ticket #") {
		header("Location: supporttickets.php?ticketid=" . $q);
	}
	else {
		if ($field == "Tag") {
			header("Location: supporttickets.php?tag=" . $q);
		}
		else {
			if ($field == "Subject") {
				header("Location: supporttickets.php?subject=" . $q);
			}
			else {
				if ($field == "Email Address") {
					header("Location: supporttickets.php?email=" . $q);
				}
				else {
					if ($field == "Client Name") {
						header("Location: supporttickets.php?clientname=" . $q);
					}
				}
			}
		}
	}

	exit();
}

?>