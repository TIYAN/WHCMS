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
 * */

function heartinternet_getConfigArray() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Heart Internet" ), "Username" => array( "Type" => "text", "Size" => "25", "Description" => "Your Domain Reseller API Username as given at https://customer.heartinternet.co.uk/manage/api" ), "Password" => array( "Type" => "text", "Size" => "25", "Description" => "Your Domain Reseller API Password as given at https://customer.heartinternet.co.uk/manage/api" ), "TestMode" => array( "Type" => "yesno", "Description" => "Tick to enable test mode" ) );
	return $configarray;
}


function heartinternet_RegisterDomain($params) {
	$cltrid = md5( date( "YmdHis" ) );
	$registrantid = heartinternetreg_createContact( $params );
	$values = array();

	if (is_array( $registrantid )) {
		$values->error .= "Failed to create contact" . serialize( $registrantid );
	}

	$domainxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<create>
<domain:create>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
<domain:period unit=\"y\">" . $params["regperiod"] . "</domain:period>
<domain:registrant>" . $registrantid . "</domain:registrant>
<domain:authInfo>
<domain:ext>
<ext-domain:null/>
</domain:ext>
</domain:authInfo>
</domain:create>
</create>
<extension>
<ext-domain:createExtension>";

	if ($params["idprotection"]) {
		$domainxml .= "<ext-domain:privacy/>";
	}

	$domainxml .= "<ext-domain:registrationMechanism>credits</ext-domain:registrationMechanism>
</ext-domain:createExtension>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $domainxml, "off", $params );

	if (trim( $xmldata["EPP"]["RESPONSE"]["RESULT"]["MSG"] ) != "Command completed successfully") {
		$values->error .= $xmldata["EPP"]["RESPONSE"]["RESULT"]["MSG"];
	}

	return $values;
}


function heartinternet_TransferDomain($params) {
	$cltrid = md5( date( "YmdHis" ) );
	$registrantid = heartinternetreg_createContact( $params );
	$values = array();

	if (is_array( $registrantid )) {
		$values->error .= "Failed to create contact" . serialize( $registrantid );
	}

	$transferxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\">
<command>
<transfer op=\"request\">
<domain:transfer>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
<domain:authInfo>
<domain:ext>
<ext-domain:null/>
</domain:ext>
</domain:authInfo>
</domain:transfer>
</transfer>
<extension>
<ext-domain:transferExtension>
<ext-domain:registrant>" . $registrantid . "</ext-domain:registrant>
<ext-domain:keepNameservers/>
</ext-domain:transferExtension>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $transferxml, "on", $params );

	if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1001") {
		$values->error .= $xmldata["epp"]["response"]["result"]["msg"]["value"];
	}

	return $values;
}


function heartinternet_RenewDomain($params) {
	$regperiod = $params["regperiod"];
	$cltrid = md5( date( "YmdHis" ) );
	$values = array();
	$current_expiry_date = mysql_fetch_assoc( select_query( "tbldomains", "expirydate", array( "domain" => $params["sld"] . "." . $params["tld"] ) ) );
	$current_expiry_date = $current_expiry_date["expirydate"];
	$cltrid = md5( date( "YmdHis" ) . date( "sHdmiY" ) );
	$renewxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\">
<command>
<renew>
<domain:renew>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
<domain:curExpDate>" . $current_expiry_date . "</domain:curExpDate>
<domain:period unit=\"y\">" . $regperiod . "</domain:period>
</domain:renew>
</renew>
<extension>
<ext-domain:renewExtension>
<ext-domain:registrationMechanism>credits</ext-domain:registrationMechanism>
</ext-domain:renewExtension>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $renewxml, "on", $params );

	if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
		$values->error .= $xmldata["epp"]["response"]["result"]["msg"]["value"];
	}

	return $values;
}


function heartinternet_GetNameservers($params) {
	$cltrid = md5( date( "YmdHis" ) );
	$values = array();
	$infoxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<info>
<domain:info>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
</domain:info>
</info>
<extension>
<ext-domain:info xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\"/>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $infoxml, "on", $params );

	if (!is_array( $xmldata )) {
		return array( "error" => $xmldata );
	}


	if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
		$values->error .= $xmldata["epp"]["response"]["result"]["msg"]["value"];
	}
	else {
		$values["ns1"] = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:ns"]["domain:hostAttr"][0]["domain:hostName"]["value"];
		$values["ns2"] = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:ns"]["domain:hostAttr"][1]["domain:hostName"]["value"];
		$values["ns3"] = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:ns"]["domain:hostAttr"][2]["domain:hostName"]["value"];
		$values["ns4"] = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:ns"]["domain:hostAttr"][3]["domain:hostName"]["value"];
		$values["ns5"] = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:ns"]["domain:hostAttr"][4]["domain:hostName"]["value"];
	}

	return $values;
}


function heartinternet_SaveNameservers($params) {
	$cltrid = md5( date( "YmdHis" ) );
	$values = array();
	$heartns = heartinternet_GetNameservers( $params );
	print_r( $heartns );
	$addns = $removens = array();
	$i = 9;

	while ($i <= 5) {
		if (!in_array( $params["ns" . $i], $heartns )) {
			$addns[] = $params["ns" . $i];
		}

		++$i;
	}

	foreach ($heartns as $v) {

		if (!in_array( $v, $params )) {
			$removens[] = $v;
			continue;
		}
	}

	$addnsxml = $removensxml = "";

	if (count( $addns )) {
		$addnsxml = "<domain:add><domain:ns>";
		foreach ($addns as $ns) {
			$addnsxml .= "<domain:hostAttr><domain:hostName>" . $ns . "</domain:hostName></domain:hostAttr>";
		}

		$addnsxml .= "</domain:ns></domain:add>";
	}


	if (count( $removens )) {
		$removensxml = "<domain:rem><domain:ns>";
		foreach ($removens as $ns) {
			$removensxml .= "<domain:hostAttr><domain:hostName>" . $ns . "</domain:hostName></domain:hostAttr>";
		}

		$removensxml .= "</domain:ns></domain:rem>";
	}


	if ($addnsxml) {
		$updatexml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.3\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<update>
<domain:update>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
" . $addnsxml . "
</domain:update>
</update>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
		$xmldata = heartinternetreg_curlcall( $updatexml, "on", $params );

		if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
			$values->error .= $xmldata["epp"]["response"]["result"]["msg"]["value"];
		}
	}


	if ($removensxml) {
		$updatexml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.3\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<update>
<domain:update>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
" . $removensxml . "
</domain:update>
</update>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
		$xmldata = heartinternetreg_curlcall( $updatexml, "on", $params );

		if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
			$values->error .= $xmldata["epp"]["response"]["result"]["msg"]["value"];
		}
	}

	return $values;
}


function heartinternet_ClientArea($params) {
	$cltrid = md5( date( "YmdHis" ) );
	$values = array();
	$infoxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<info>
<domain:info>
<domain:name>" . $params["sld"] . "." . $params["tld"] . "</domain:name>
</domain:info>
</info>
<extension>
<ext-domain:preAuthenticate/>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $infoxml, "on", $params );

	if (!is_array( $xmldata )) {
		return array( "error" => $xmldata );
	}


	if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
		$result = "";
	}
	else {
		$url = $xmldata["epp"]["response"]["resData"]["ext-domain:redirectURL"]["value"];
		$result = "<a href=\"" . $url . "\" target=\"_blank\">Login to Control Panel</a>";
	}

	return $result;
}


function heartinternet_Sync($params) {
	$cltrid = md5( date( "YmdHis" ) . rand( 1000, 9999 ) );
	$infoxml = "<?xml version=\"1.0\"?>\n<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\" xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
<command>
<info>
<domain:info>
<domain:name>" . $params["domain"] . "</domain:name>
</domain:info>
</info>
<extension>
<ext-domain:info xmlns:ext-domain=\"http://www.heartinternet.co.uk/whapi/ext-domain-1.2\"/>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $infoxml, "on", $params );
	$rtn = array();

	if (trim( $xmldata["epp"]["response"]["result"]["attr"]["code"] ) != "1000") {
		$rtn["error"] = $xmldata["epp"]["response"]["result"]["msg"]["value"];
	}
	else {
		$expirydate = $xmldata["epp"]["response"]["resData"]["domain:infData"]["domain:exDate"]["value"];

		if (trim( $expirydate )) {
			$expirydate = substr( $expirydate, 0, 10 );
			$rtn["active"] = true;
			$rtn["expirydate"] = $expirydate;
		}
	}

	return $rtn;
}


function heartinternetreg_curlcall($xml, $verbose = "off", $params) {
	if (!class_exists( "HeartInternetReg_API" )) {
		require ROOTDIR . "/modules/registrars/heartinternet/heartinternet.class.php";
	}

	$hi_api = new HeartInternetReg_API();

	if ($params["TestMode"] == "on") {
		$hi_api->connect( true );
	}
	else {
		$hi_api->connect();
	}

	$objects = array( "urn:ietf:params:xml:ns:contact-1.0", "urn:ietf:params:xml:ns:domain-1.0", "http://www.heartinternet.co.uk/whapi/null-1.1" );
	$extensions = array( "http://www.heartinternet.co.uk/whapi/ext-domain-1.2", "http://www.heartinternet.co.uk/whapi/ext-contact-1.0", "http://www.heartinternet.co.uk/whapi/ext-host-1.0", "http://www.heartinternet.co.uk/whapi/ext-null-1.0", "http://www.heartinternet.co.uk/whapi/ext-whapi-1.0" );
	try{
	$hi_api->logIn( $params["Username"], $params["Password"], $objects, $extensions );
	}
	catch ( Exception $e ) {
		return "Caught exception: " . $e->getMessage();
	}

	$data = $hi_api->sendMessage( $xml, true );
	logModuleCall( "heartinternet", $action, $xml, $data, "", array( $params["Username"], $params["Password"] ) );

	if ($verbose == "on") {
		return heartinternetreg_xml2array( $data );
	}

	return XMLtoArray( $data );
}


function heartinternetreg_createContact($params) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$cltrid = md5( date( "YmdHis" ) );
	$xml = "<?xml version=\"1.0\"?><epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:ext-contact=\"http://www.heartinternet.co.uk/whapi/ext-contact-1.0\" xmlns:contact=\"urn:ietf:params:xml:ns:contact-1.0\">
<command>
<create>
<contact:create>
<contact:id>IGNORED</contact:id>
<contact:postalInfo type=\"loc\">
<contact:name>" . $params["firstname"] . " " . $params["lastname"] . "</contact:name>
<contact:addr>
<contact:street>" . $params["address1"] . "</contact:street>
<contact:city>" . $params["city"] . "</contact:city>
<contact:sp>" . $params["state"] . "</contact:sp>
<contact:pc>" . $params["postcode"] . "</contact:pc>
<contact:cc>" . $params["country"] . "</contact:cc>
</contact:addr>
</contact:postalInfo>
<contact:voice>+" . $countrycallingcodes[$params["country"]] . "." . preg_replace( "/[^0-9]/", "", $params["phonenumber"] ) . "</contact:voice>
<contact:email>" . $params["email"] . "</contact:email>
<contact:authInfo>
<contact:ext>
<ext-contact:null/>
</contact:ext>
</contact:authInfo>
</contact:create>
</create>
<extension>
  <ext-contact:createExtension>
	<ext-contact:person>
	  <ext-contact:salutation gender=\"male\">Mr</ext-contact:salutation>
	  <ext-contact:surname>" . $params["lastname"] . "</ext-contact:surname>
	  <ext-contact:otherNames>" . $params["firstname"] . "</ext-contact:otherNames>
      <ext-contact:dateOfBirth>1980-12-20</ext-contact:dateOfBirth>
	</ext-contact:person>
	<ext-contact:telephone type=\"mobile\">+" . $countrycallingcodes[$params["country"]] . "." . preg_replace( "/[^0-9]/", "", $params["phonenumber"] ) . "</ext-contact:telephone>
  </ext-contact:createExtension>
</extension>
<clTRID>" . $cltrid . "</clTRID>
</command>
</epp>";
	$xmldata = heartinternetreg_curlcall( $xml, "off", $params );

	if (trim( $xmldata["EPP"]["RESPONSE"]["RESULT"]["MSG"] ) == "Command completed successfully") {
		$registrantid = trim( $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:CREDATA"]["CONTACT:ID"] );
	}
	else {
		$registrantid = $xmldata["error"];
	}

	return $registrantid;
}


function heartinternetreg_xml2array($contents, $get_attributes = 1) {
	if (!$contents) {
		return array();
	}


	if (!function_exists( "xml_parser_create" )) {
		return array();
	}

	$parser = xml_parser_create();
	xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct( $parser, $contents, $xml_values );
	xml_parser_free( $parser );

	if (!$xml_values) {
		return null;
	}

	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;

	foreach ($xml_values as $data) {
		unset( $attributes );
		unset( $value );
		extract( $data );
		$result = "";

		if ($get_attributes) {
			$result = array();

			if (isset( $value )) {
				$result["value"] = $value;
			}


			if (isset( $attributes )) {
				foreach ($attributes as $attr => $val) {

					if ($get_attributes == 1) {
						$result["attr"][$attr] = $val;
						continue;
					}
				}
			}
		}
		else {
			if (isset( $value )) {
				$result = $value;
			}
		}


		if ($type == "open") {
			$parent[$level - 1] = &$current;

			if (( !is_array( $current ) || !in_array( $tag, array_keys( $current ) ) )) {
				$current[$tag] = $result;
				$current = &$current[$tag];

				continue;
			}


			if (isset( $current[$tag][0] )) {
				array_push( $current[$tag], $result );
			}
			else {
				$current[$tag] = array( $current[$tag], $result );
			}

			$last = count( $current[$tag] ) - 1;
			$current = &$current[$tag][$last];

			continue;
		}


		if ($type == "complete") {
			if (!isset( $current[$tag] )) {
				$current[$tag] = $result;
				continue;
			}


			if (( ( is_array( $current[$tag] ) && $get_attributes == 0 ) || ( ( isset( $current[$tag][0] ) && is_array( $current[$tag][0] ) ) && $get_attributes == 1 ) )) {
				array_push( $current[$tag], $result );
				continue;
			}

			$current[$tag] = array( $current[$tag], $result );
			continue;
		}


		if ($type == "close") {
			$current = &$parent[$level - 1];

			continue;
		}
	}

	return $xml_array;
}


?>