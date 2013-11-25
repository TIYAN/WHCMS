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
 * */

function resellone_getConfigArray() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "ResellOne" ), "Username" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your ResellOne Reseller Account Username here" ), "PrivateKey" => array( "Type" => "text", "Size" => "80", "Description" => "Enter your ResellOne Private Key here" ), "TestMode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function resellone_GetNameservers($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";

	if (!class_exists( "PEAR" )) {
		return array( "error" => "OpenSRS/ResellOne Class Files Missing. Visit <a href=\"http://docs.whmcs.com/OpenSRS#Additional_Registrar_Module_Files_Requirement\" target=\"_blank\">http://docs.whmcs.com/OpenSRS#Additional_Registrar_Module_Files_Requirement</a> to resolve" );
	}

	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $resellone_username, "reg_password" => $resellone_password ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];

			if (!$values["error"]) {
				$values["error"] = "API Connection Failure. Please open ports 55443 and 55000 in your servers firewall.";
			}
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$cmd = array( "action" => "get", "object" => "domain", "registrant_ip" => $server_ip, "cookie" => $opensrscookie, "attributes" => array( "type" => "nameservers" ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$nameserver1 = $result["attributes"]["nameserver_list"][0]["name"];
			$nameserver2 = $result["attributes"]["nameserver_list"][1]["name"];
			$nameserver3 = $result["attributes"]["nameserver_list"][2]["name"];
			$nameserver4 = $result["attributes"]["nameserver_list"][3]["name"];
			$nameserver5 = $result["attributes"]["nameserver_list"][4]["name"];
			$values["ns1"] = $nameserver1;
			$values["ns2"] = $nameserver2;
			$values["ns3"] = $nameserver3;
			$values["ns4"] = $nameserver4;
			$values["ns5"] = $nameserver5;
		}
	}

	return $values;
}


function resellone_SaveNameservers($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $resellone_username, "reg_password" => $resellone_password ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}

	$nameserverslist[] = $params["ns1"];
	$nameserverslist[] = $params["ns2"];

	if ($params["ns3"]) {
		$nameserverslist[] = $params["ns3"];
	}


	if ($params["ns4"]) {
		$nameserverslist[] = $params["ns4"];
	}


	if ($params["ns5"]) {
		$nameserverslist[] = $params["ns5"];
	}


	if ($opensrscookie) {
		$cmd = array( "action" => "advanced_update_nameservers", "object" => "domain", "registrant_ip" => $server_ip, "cookie" => $opensrscookie, "attributes" => array( "op_type" => "assign", "assign_ns" => $nameserverslist ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
	}

	return $values;
}


function resellone_GetRegistrarLock($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";

	if (!class_exists( "PEAR" )) {
		return false;
	}

	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $resellone_username, "reg_password" => $resellone_password ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$cmd = array( "action" => "get", "object" => "domain", "registrant_ip" => $server_ip, "cookie" => $opensrscookie, "attributes" => array( "type" => "status" ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result );
		$lockstate = $result["attributes"]["lock_state"];

		if ($lockstate == "1") {
			$lockstate = "locked";
		}
		else {
			$lockstate = "unlocked";
		}

		return $lockstate;
	}

}


function resellone_SaveRegistrarLock($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $resellone_username, "reg_password" => $resellone_password ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		if ($params["lockenabled"] == "locked") {
			$lockstate = "1";
		}
		else {
			$lockstate = "0";
		}

		$cmd = array( "action" => "modify", "object" => "domain", "cookie" => $opensrscookie, "attributes" => array( "data" => "status", "lock_state" => $lockstate ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", $cmd["action"] . " " . $cmd["object"], $cmd, $result );
	}

}


function resellone_RegisterDomain($params) {
	global $opensrscookie;
	global $server_ip;

	$legaltype = $params["additionalfields"]["Legal Type"];
	$regname = $params["additionalfields"]["Registrant Name"];
	$trademarknum = $params["additionalfields"]["Trademark Number"];

	if ($trademarknum) {
		$isatrademark = "1";
	}
	else {
		$isatrademark = "0";
	}


	if (preg_match( "/uk$/i", $params["tld"] )) {
		if ($params["additionalfields"]["Legal Type"] == "UK Limited Company") {
			$legaltype = "LTD";
		}
		else {
			if ($params["additionalfields"]["Legal Type"] == "UK Public Limited Company") {
				$legaltype = "PLC";
			}
			else {
				if ($params["additionalfields"]["Legal Type"] == "UK Partnership") {
					$legaltype = "PTNR";
				}
				else {
					if ($params["additionalfields"]["Legal Type"] == "UK Limited Liability Partnership") {
						$legaltype = "LLP";
					}
					else {
						if ($params["additionalfields"]["Legal Type"] == "Sole Trader") {
							$legaltype = "STRA";
						}
						else {
							if ($params["additionalfields"]["Legal Type"] == "UK Registered Charity") {
								$legaltype = "RCHAR";
							}
							else {
								$legaltype = "IND";
							}
						}
					}
				}
			}
		}
	}
	else {
		if (preg_match( "/ca$/i", $params["tld"] )) {
			if ($legaltype == "Corporation") {
				$legaltype = "CCO";
			}
			else {
				if ($legaltype == "Canadian Citizen") {
					$legaltype = "CCT";
				}
				else {
					if ($legaltype == "Government") {
						$legaltype = "GOV";
					}
					else {
						if ($legaltype == "Canadian Educational Institution") {
							$legaltype = "EDU";
						}
						else {
							if ($legaltype == "Canadian Unincorporated Association") {
								$legaltype = "ASS";
							}
							else {
								if ($legaltype == "Canadian Hospital") {
									$legaltype = "HOP";
								}
								else {
									if ($legaltype == "Partnership Registered in Canada") {
										$legaltype = "PRT";
									}
									else {
										if ($legaltype == "Trade-mark registered in Canada") {
											$legaltype = "TDM";
										}
										else {
											$legaltype = "CCT";
										}
									}
								}
							}
						}
					}
				}
			}
		}
		else {
			if (preg_match( "/de$/i", $params["tld"] )) {
				$params["admincountry"] = "DE";
			}
		}
	}

	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	$domain = $params["sld"] . "." . $params["tld"];
	$f_whois_privacy = ($params["idprotection"] ? "1" : "0");
	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );

	if (!$params["companyname"]) {
		$params["companyname"] = "None";
	}


	if (!$params["admincompanyname"]) {
		$params["admincompanyname"] = "None";
	}

	$nameserverslist = array();
	$nameserverslist[] = array( "sortorder" => "1", "name" => $params["ns1"] );
	$nameserverslist[] = array( "sortorder" => "2", "name" => $params["ns2"] );

	if ($params["ns3"]) {
		$nameserverslist[] = array( "sortorder" => "3", "name" => $params["ns3"] );
	}


	if ($params["ns4"]) {
		$nameserverslist[] = array( "sortorder" => "4", "name" => $params["ns4"] );
	}


	if ($params["ns5"]) {
		$nameserverslist[] = array( "sortorder" => "5", "name" => $params["ns5"] );
	}


	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE 'mod_opensrs'" ) )) {
		$query = "CREATE TABLE `mod_opensrs` (`domain` TEXT NOT NULL ,`username` TEXT NOT NULL ,`password` TEXT NOT NULL)";
		$result = full_query( $query );
	}

	$reselloneusername = resellone_getusername( $params["sld"] . "." . $params["tld"] );
	$resellonepassword = substr( sha1( $params["domainid"] . mt_rand( 1000000, 9999999 ) ), 0, 10 );
	$attributes = array( "f_lock_domain" => "1", "domain" => $domain, "period" => $params["regperiod"], "reg_type" => "new", "reg_username" => $reselloneusername, "reg_password" => $resellonepassword, "custom_tech_contact" => "0", "legal_type" => $legaltype, "isa_trademark" => $isatrademark, "lang_pref" => "EN", "link_domains" => "0", "custom_nameservers" => "1", "f_whois_privacy" => $f_whois_privacy, "nameserver_list" => $nameserverslist, "contact_set" => array( "admin" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => $params["additionalfields"]["Fax Number"], "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"], "lang_pref" => "EN" ), "billing" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => $params["additionalfields"]["Fax Number"], "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"], "lang_pref" => "EN" ), "tech" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => $params["additionalfields"]["Fax Number"], "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"], "lang_pref" => "EN" ), "owner" => array( "first_name" => $params["firstname"], "state" => $params["state"], "country" => $params["country"], "address1" => $params["address1"], "address2" => $params["address2"], "last_name" => $params["lastname"], "address3" => "", "city" => $params["city"], "fax" => $params["additionalfields"]["Fax Number"], "postal_code" => $params["postcode"], "email" => $params["email"], "phone" => $params["fullphonenumber"], "org_name" => $params["companyname"], "lang_pref" => "EN" ) ) );

	if (preg_match( "/au$/i", $params["tld"] )) {
		$eligibilitytype = $params["additionalfields"]["Eligibility ID Type"];

		if ($eligibilitytype == "Australian Company Number (ACN)") {
			$eligibilitytype = "ACN";
		}
		else {
			if ($eligibilitytype == "ACT Business Number") {
				$eligibilitytype = "ABN";
			}
			else {
				if ($eligibilitytype == "NSW Business Number") {
					$eligibilitytype = "NSW BN";
				}
				else {
					if ($eligibilitytype == "NT Business Number") {
						$eligibilitytype = "NT BN";
					}
					else {
						if ($eligibilitytype == "QLD Business Number") {
							$eligibilitytype = "QLD BN";
						}
						else {
							if ($eligibilitytype == "SA Business Number") {
								$eligibilitytype = "SA BN";
							}
							else {
								if ($eligibilitytype == "TAS Business Number") {
									$eligibilitytype = "TAS BN";
								}
								else {
									if ($eligibilitytype == "VIC Business Number") {
										$eligibilitytype = "VIC BN";
									}
									else {
										if ($eligibilitytype == "WA Business Number") {
											$eligibilitytype = "WA BN";
										}
										else {
											if ($eligibilitytype == "Trademark (TM)") {
												$eligibilitytype = "TM";
											}
											else {
												$eligibilitytype = "OTHER";
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

		$attributes["tld_data"]["au_registrant_info"]["eligibility_id"] = $params["additionalfields"]["Eligibility ID"];
		$attributes["tld_data"]["au_registrant_info"]["eligibility_id_type"] = $eligibilitytype;
		$attributes["tld_data"]["au_registrant_info"]["eligibility_name"] = $params["additionalfields"]["Eligibility Name"];
		$attributes["tld_data"]["au_registrant_info"]["eligibility_type"] = $params["additionalfields"]["Eligibility Type"];
		$attributes["tld_data"]["au_registrant_info"]["registrant_name"] = $params["additionalfields"]["Registrant Name"];
	}
	else {
		if (preg_match( "/au$/i", $params["tld"] )) {
			$purpose = $params["additionalfields"]["Application Purpose"];

			if ($purpose == "Business use for profit") {
				$purpose = "P1";
			}
			else {
				if ($purpose == "Non-profit business") {
					$purpose = "P2";
				}
				else {
					if ($purpose == "Club") {
						$purpose = "P2";
					}
					else {
						if ($purpose == "Association") {
							$purpose = "P2";
						}
						else {
							if ($purpose == "Religious Organization") {
								$purpose = "P2";
							}
							else {
								if ($purpose == "Personal Use") {
									$purpose = "P3";
								}
								else {
									if ($purpose == "Educational purposes") {
										$purpose = "P4";
									}
									else {
										if ($purpose == "Government purposes") {
											$purpose = "P5";
										}
									}
								}
							}
						}
					}
				}
			}

			$attributes["tld_data"] = array( "nexus" => array( "category" => $params["additionalfields"]["Nexus Category"], "app_purpose" => $purpose ) );
		}
	}

	$domain = $params["sld"] . "." . $params["tld"];
	$cmd = array( "action" => "SW_REGISTER", "object" => "DOMAIN", "registrant_ip" => $server_ip, "attributes" => $attributes );
	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Register Domain", $attributes, $result, "", array( $resellone_username, $resellone_password ) );

	if ($result["is_success"] != "1") {
		$values["error"] = $result["response_text"] . " - " . $result["attributes"]["error"];
	}
	else {
		$cmd = array( "action" => "process_pending", "object" => "domain", "registrant_ip" => $server_ip, "attributes" => array( "owner_address" => $result["attributes"]["admin_email"], "order_id" => $result["attributes"]["id"] ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Register Domain", $attributes, $result );
	}


	if (!$values["error"]) {
		delete_query( "mod_opensrs", array( "domain" => $params["sld"] . "." . $params["tld"] ) );
		insert_query( "mod_opensrs", array( "domain" => $params["sld"] . "." . $params["tld"], "username" => $reselloneusername, "password" => $resellonepassword ) );
	}

	return $values;
}


function resellone_TransferDomain($params) {
	global $opensrscookie;
	global $server_ip;

	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	$domain = $params["sld"] . "." . $params["tld"];
	$f_whois_privacy = ($params["idprotection"] ? "1" : "0");
	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );

	if (!$params["companyname"]) {
		$params["companyname"] = "None";
	}


	if (!$params["admincompanyname"]) {
		$params["admincompanyname"] = "None";
	}

	$nameserverslist = array();
	$nameserverslist[] = array( "sortorder" => "1", "name" => $params["ns1"] );
	$nameserverslist[] = array( "sortorder" => "2", "name" => $params["ns2"] );

	if ($params["ns3"]) {
		$nameserverslist[] = array( "sortorder" => "3", "name" => $params["ns3"] );
	}


	if ($params["ns4"]) {
		$nameserverslist[] = array( "sortorder" => "4", "name" => $params["ns4"] );
	}


	if ($params["ns5"]) {
		$nameserverslist[] = array( "sortorder" => "5", "name" => $params["ns5"] );
	}

	$reselloneusername = resellone_getusername( $params["sld"] . "." . $params["tld"] );
	$resellonepassword = substr( sha1( $params["domainid"] . mt_rand( 1000000, 9999999 ) ), 0, 10 );
	$domain = $params["sld"] . "." . $params["tld"];
	$cmd = array( "action" => "SW_REGISTER", "object" => "DOMAIN", "registrant_ip" => $server_ip, "attributes" => array( "f_lock_domain" => "1", "domain" => $domain, "period" => $params["regperiod"], "reg_type" => "transfer", "reg_username" => $reselloneusername, "reg_password" => $resellonepassword, "custom_tech_contact" => "0", "link_domains" => "0", "custom_nameservers" => "1", "nameserver_list" => $nameserverslist, "f_whois_privacy" => $f_whois_privacy, "contact_set" => array( "admin" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => "", "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"] ), "billing" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => "", "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"] ), "tech" => array( "first_name" => $params["adminfirstname"], "state" => $params["adminstate"], "country" => $params["admincountry"], "address1" => $params["adminaddress1"], "address2" => $params["adminaddress2"], "last_name" => $params["adminlastname"], "address3" => "", "city" => $params["admincity"], "fax" => $params["additionalfields"]["Fax Number"], "postal_code" => $params["adminpostcode"], "email" => $params["adminemail"], "phone" => $params["adminfullphonenumber"], "org_name" => $params["admincompanyname"] ), "owner" => array( "first_name" => $params["firstname"], "state" => $params["state"], "country" => $params["country"], "address1" => $params["address1"], "address2" => $params["address2"], "last_name" => $params["lastname"], "address3" => "", "city" => $params["city"], "fax" => "", "postal_code" => $params["postcode"], "email" => $params["email"], "phone" => $params["fullphonenumber"], "org_name" => $params["companyname"] ) ) ) );

	if (( ( ( ( preg_match( "/au$/i", $params["tld"] ) || preg_match( "/de$/i", $params["tld"] ) ) || preg_match( "/be$/i", $params["tld"] ) ) || preg_match( "/eu$/i", $params["tld"] ) ) || preg_match( "/it$/i", $params["tld"] ) )) {
		$cmd["attributes"]["owner_confirm_address"] = $params["email"];
	}

	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Transfer Domain", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

	if ($result["is_success"] != "1") {
		$values["error"] = $result["response_text"] . " - " . $result["attributes"]["error"];
	}
	else {
		$cmd = array( "action" => "process_pending", "object" => "domain", "registrant_ip" => $server_ip, "attributes" => array( "owner_address" => $result["attributes"]["admin_email"], "order_id" => $result["attributes"]["id"] ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Process Pending Domain", $cmd, $result );
	}


	if (!$values["error"]) {
		delete_query( "mod_opensrs", array( "domain" => $params["sld"] . "." . $params["tld"] ) );
		insert_query( "mod_opensrs", array( "domain" => $params["sld"] . "." . $params["tld"], "username" => $reselloneusername, "password" => $resellonepassword ) );
	}

	return $values;
}


function resellone_RenewDomain($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;

	$domain = $params["sld"] . "." . $params["tld"];
	$resellone_username = resellone_getusername( $domain );
	$resellone_password = resellone_getpassword( $params["domainid"], $domain );
	$result = select_query( "tbldomains", "expirydate", array( "id" => $params["domainid"] ) );
	$data = mysql_fetch_array( $result );
	$expirydate = $data["expirydate"];
	$expiryyear = substr( $expirydate, 0, 4 );
	$cmd = array( "action" => "renew", "object" => "DOMAIN", "attributes" => array( "auto_renew" => "0", "currentexpirationyear" => $expiryyear, "handle" => "process", "domain" => $domain, "period" => $params["regperiod"] ) );
	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Renew Domain", $cmd, $result );

	if ($result["is_success"] != "1") {
		$values["error"] = $result["response_text"];
	}

	return $values;
}


function resellone_GetContactDetails($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;

	$domain = $params["sld"] . "." . $params["tld"];
	$resellone_username = resellone_getusername( $domain );
	$resellone_password = resellone_getpassword( $params["domainid"], $domain );
	$cmd = array( "action" => "GET_DOMAINS_CONTACTS", "object" => "DOMAIN", "attributes" => array( "domain_list" => array( $domain ) ) );
	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Get Contact Details", $cmd, $result );

	if ($result["is_success"] != "1") {
		$values["error"] = $result["response_text"];
	}

	$ownerdata = $result["attributes"][$domain]["contact_set"]["owner"];
	$admindata = $result["attributes"][$domain]["contact_set"]["admin"];
	$billingdata = $result["attributes"][$domain]["contact_set"]["billing"];
	$techdata = $result["attributes"][$domain]["contact_set"]["tech"];
	$values["Owner"]["First Name"] = $ownerdata["first_name"];
	$values["Owner"]["Last Name"] = $ownerdata["last_name"];
	$values["Owner"]["Organisation Name"] = $ownerdata["org_name"];
	$values["Owner"]["Email"] = $ownerdata["email"];
	$values["Owner"]["Address 1"] = $ownerdata["address1"];
	$values["Owner"]["Address 2"] = $ownerdata["address2"];
	$values["Owner"]["City"] = $ownerdata["city"];
	$values["Owner"]["State"] = $ownerdata["state"];
	$values["Owner"]["Postcode"] = $ownerdata["postal_code"];
	$values["Owner"]["Country"] = $ownerdata["country"];
	$values["Owner"]["Phone"] = $ownerdata["phone"];
	$values["Owner"]["Fax"] = $ownerdata["fax"];
	$values["Admin"]["First Name"] = $admindata["first_name"];
	$values["Admin"]["Last Name"] = $admindata["last_name"];
	$values["Admin"]["Organisation Name"] = $admindata["org_name"];
	$values["Admin"]["Email"] = $admindata["email"];
	$values["Admin"]["Address 1"] = $admindata["address1"];
	$values["Admin"]["Address 2"] = $admindata["address2"];
	$values["Admin"]["City"] = $admindata["city"];
	$values["Admin"]["State"] = $admindata["state"];
	$values["Admin"]["Postcode"] = $admindata["postal_code"];
	$values["Admin"]["Country"] = $admindata["country"];
	$values["Admin"]["Phone"] = $admindata["phone"];
	$values["Admin"]["Fax"] = $admindata["fax"];
	$values["Billing"]["First Name"] = $billingdata["first_name"];
	$values["Billing"]["Last Name"] = $billingdata["last_name"];
	$values["Billing"]["Organisation Name"] = $billingdata["org_name"];
	$values["Billing"]["Email"] = $billingdata["email"];
	$values["Billing"]["Address 1"] = $billingdata["address1"];
	$values["Billing"]["Address 2"] = $billingdata["address2"];
	$values["Billing"]["City"] = $billingdata["city"];
	$values["Billing"]["State"] = $billingdata["state"];
	$values["Billing"]["Postcode"] = $billingdata["postal_code"];
	$values["Billing"]["Country"] = $billingdata["country"];
	$values["Billing"]["Phone"] = $billingdata["phone"];
	$values["Billing"]["Fax"] = $billingdata["fax"];
	$values["Technical"]["First Name"] = $techdata["first_name"];
	$values["Technical"]["Last Name"] = $techdata["last_name"];
	$values["Technical"]["Organisation Name"] = $techdata["org_name"];
	$values["Technical"]["Email"] = $techdata["email"];
	$values["Technical"]["Address 1"] = $techdata["address1"];
	$values["Technical"]["Address 2"] = $techdata["address2"];
	$values["Technical"]["City"] = $techdata["city"];
	$values["Technical"]["State"] = $techdata["state"];
	$values["Technical"]["Postcode"] = $techdata["postal_code"];
	$values["Technical"]["Country"] = $techdata["country"];
	$values["Technical"]["Phone"] = $techdata["phone"];
	$values["Technical"]["Fax"] = $techdata["fax"];
	return $values;
}


function resellone_SaveContactDetails($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	$domain = $params["sld"] . "." . $params["tld"];
	$resellone_username = resellone_getusername( $domain );
	$resellone_password = resellone_getpassword( $params["domainid"], $domain );

	if (!$opensrscookie) {
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $resellone_username, "reg_password" => $resellone_password ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Save Contact Details (Set Cookie)", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$cmd = array( "object" => "domain", "action" => "modify", "cookie" => $opensrscookie, "registrant_ip" => $server_ip, "attributes" => array( "data" => "contact_info", "affect_domains" => "0", "lang_pref" => "EN", "report_email" => $params["Owner"]["Email"], "contact_set" => array( "owner" => array( "first_name" => $params["contactdetails"]["Owner"]["First Name"], "state" => $params["contactdetails"]["Owner"]["State"], "country" => $params["contactdetails"]["Owner"]["Country"], "address1" => $params["contactdetails"]["Owner"]["Address 1"], "address2" => $params["contactdetails"]["Owner"]["Address 2"], "last_name" => $params["contactdetails"]["Owner"]["Last Name"], "address3" => "", "city" => $params["contactdetails"]["Owner"]["City"], "fax" => $params["contactdetails"]["Owner"]["Fax"], "postal_code" => $params["contactdetails"]["Owner"]["Postcode"], "email" => $params["contactdetails"]["Owner"]["Email"], "phone" => $params["contactdetails"]["Owner"]["Phone"], "org_name" => $params["contactdetails"]["Owner"]["Organisation Name"], "lang_pref" => "EN" ), "admin" => array( "first_name" => $params["contactdetails"]["Admin"]["First Name"], "state" => $params["contactdetails"]["Admin"]["State"], "country" => $params["contactdetails"]["Admin"]["Country"], "address1" => $params["contactdetails"]["Admin"]["Address 1"], "address2" => $params["contactdetails"]["Admin"]["Address 2"], "last_name" => $params["contactdetails"]["Admin"]["Last Name"], "address3" => "", "city" => $params["contactdetails"]["Admin"]["City"], "fax" => $params["contactdetails"]["Admin"]["Fax"], "postal_code" => $params["contactdetails"]["Admin"]["Postcode"], "email" => $params["contactdetails"]["Admin"]["Email"], "phone" => $params["contactdetails"]["Admin"]["Phone"], "org_name" => $params["contactdetails"]["Admin"]["Organisation Name"], "lang_pref" => "EN" ), "billing" => array( "first_name" => $params["contactdetails"]["Billing"]["First Name"], "state" => $params["contactdetails"]["Billing"]["State"], "country" => $params["contactdetails"]["Billing"]["Country"], "address1" => $params["contactdetails"]["Billing"]["Address 1"], "address2" => $params["contactdetails"]["Billing"]["Address 2"], "last_name" => $params["contactdetails"]["Billing"]["Last Name"], "address3" => "", "city" => $params["contactdetails"]["Billing"]["City"], "fax" => $params["contactdetails"]["Billing"]["Fax"], "postal_code" => $params["contactdetails"]["Billing"]["Postcode"], "email" => $params["contactdetails"]["Billing"]["Email"], "phone" => $params["contactdetails"]["Billing"]["Phone"], "org_name" => $params["contactdetails"]["Billing"]["Organisation Name"], "lang_pref" => "EN" ), "tech" => array( "first_name" => $params["contactdetails"]["Technical"]["First Name"], "state" => $params["contactdetails"]["Technical"]["State"], "country" => $params["contactdetails"]["Technical"]["Country"], "address1" => $params["contactdetails"]["Technical"]["Address 1"], "address2" => $params["contactdetails"]["Technical"]["Address 2"], "last_name" => $params["contactdetails"]["Technical"]["Last Name"], "address3" => "", "city" => $params["contactdetails"]["Technical"]["City"], "fax" => $params["contactdetails"]["Technical"]["Fax"], "postal_code" => $params["contactdetails"]["Technical"]["Postcode"], "email" => $params["contactdetails"]["Technical"]["Email"], "phone" => $params["contactdetails"]["Technical"]["Phone"], "org_name" => $params["contactdetails"]["Technical"]["Organisation Name"], "lang_pref" => "EN" ) ) ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Save Contact Details (Modify Domain)", $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"] . " - " . $result["attributes"]["details"][$domain]["response_text"];
		}
	}

	return $values;
}


function resellone_GetEPPCode($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => resellone_getusername( $domain ), "reg_password" => resellone_getpassword( $params["domainid"], $domain ) ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Get EPP Code (Set Cookie)", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$cmd = array( "action" => "get", "object" => "domain", "registrant_ip" => $server_ip, "cookie" => $opensrscookie, "attributes" => array( "type" => "domain_auth_info" ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Get EPP Code (Get Domain)", $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$epp = $result["attributes"]["domain_auth_info"];
			$values["eppcode"] = $epp;
		}
	}

	return $values;
}


function resellone_getusername($domain) {
	$result = select_query( "mod_opensrs", "username", array( "domain" => $domain ) );
	$data = mysql_fetch_array( $result );
	$username = $data["username"];

	if ($username) {
		return $username;
	}

	$username = preg_replace( "/[^a-zA-Z]/", "", $domain );
	$username = substr( $username, 0, 8 );
	return $username;
}


function resellone_getpassword($domainid, $domain) {
	$result = select_query( "mod_opensrs", "password", array( "domain" => $domain ) );
	$data = mysql_fetch_array( $result );
	$password = $data["password"];

	if ($password) {
		return $password;
	}

	$password = md5( ltrim( $domainid, "0" ) );
	$password = substr( $password, 0, 10 );
	return $password;
}


function resellone_RegisterNameserver($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => resellone_getusername( $domain ), "reg_password" => resellone_getpassword( $params["domainid"], $domain ) ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Register NS (Set Cookie)", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$cmd = array( "action" => "create", "object" => "nameserver", "cookie" => $opensrscookie, "attributes" => array( "name" => $params["nameserver"], "ipaddress" => $params["ipaddress"] ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Register NS (Create NS)", $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
	}

	return $values;
}


function resellone_DeleteNameserver($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => resellone_getusername( $domain ), "reg_password" => resellone_getpassword( $params["domainid"], $domain ) ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Delete NS (Set Cookie)", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$cmd = array( "action" => "delete", "object" => "nameserver", "cookie" => $opensrscookie, "attributes" => array( "name" => $params["nameserver"], "ipaddress" => $params["ipaddress"] ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Delete NS (Delete NS)", $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
	}

	return $values;
}


function resellone_ModifyNameserver($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	global $opensrscookie;
	global $server_ip;

	if (!$opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$resellone_username = resellone_getusername( $domain );
		$resellone_password = resellone_getpassword( $params["domainid"], $domain );
		$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => resellone_getusername( $domain ), "reg_password" => resellone_getpassword( $params["domainid"], $domain ) ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Modify NS (Set Cookie)", $cmd, $result, "", array( $resellone_username, $resellone_password ) );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
		else {
			$opensrscookie = $result["attributes"]["cookie"];
		}
	}


	if ($opensrscookie) {
		$domain = $params["sld"] . "." . $params["tld"];
		$cmd = array( "action" => "modify", "object" => "nameserver", "cookie" => $opensrscookie, "attributes" => array( "name" => $params["nameserver"], "new_name" => $params["nameserver"], "ipaddress" => $params["newipaddress"] ) );
		$result = $O->send_cmd( $cmd );
		logModuleCall( "resellone", "Modify NS (Modify NS)", $cmd, $result );

		if ($result["is_success"] != "1") {
			$values["error"] = $result["response_text"];
		}
	}

	return $values;
}


function resellone_Sync($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	$server_ip = (isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$domainid = $params["domainid"];
	$domain = $params["domain"];
	$username = resellone_getusername( $domain );
	$password = resellone_getpassword( $domainid, $domain );
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	$opensrscookie = "";
	$error = "";
	$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $username, "reg_password" => $password ) );
	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Get Domain Info", $cmd, $result, "", array( $username, $password ) );

	if ($result["is_success"] != "1") {
		return array( "error" => $result["response_text"] );
	}

	$opensrscookie = $result["attributes"]["cookie"];
	$expirydate = $result["attributes"]["expiredate"];
	$expirydate = explode( " ", $expirydate );
	$expirydate = $expirydate[0];
	$rtn = array();
	$rtn["active"] = true;
	$rtn["expirydate"] = $expirydate;
	return $rtn;
}


function resellone_TransferSync($params) {
	$regusername = $params["Username"];
	$regpassword = $params["Password"];
	$regprivatekey = $params["PrivateKey"];

	if ($params["TestMode"]) {
		$mode = "test";
		$protocol = "XCP";
	}
	else {
		$mode = "live";
		$protocol = "XCP";
	}

	$server_ip = (isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
	require_once dirname( __FILE__ ) . "/resellone_base.php";
	$domainid = $params["domainid"];
	$domain = $params["domain"];
	$username = resellone_getusername( $domain );
	$password = resellone_getpassword( $domainid, $domain );
	$O = new resellone_base( $mode, $protocol, $regusername, $regprivatekey );
	$opensrscookie = "";
	$error = "";
	$cmd = array( "object" => "COOKIE", "action" => "SET", "registrant_ip" => $server_ip, "attributes" => array( "domain" => $domain, "reg_username" => $username, "reg_password" => $password ) );
	$result = $O->send_cmd( $cmd );
	logModuleCall( "resellone", "Get Domain Info", $cmd, $result, "", array( $username, $password ) );

	if ($result["is_success"] != "1") {
		return array( "error" => $result["response_text"] );
	}

	$opensrscookie = $result["attributes"]["cookie"];
	$expirydate = $result["attributes"]["expiredate"];
	$expirydate = explode( " ", $expirydate );
	$expirydate = $expirydate[0];
	$rtn = array();
	$rtn["active"] = true;
	$rtn["expirydate"] = $expirydate;
	return $rtn;
}


global $opensrscookie;
global $server_ip;

$server_ip = (isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
?>