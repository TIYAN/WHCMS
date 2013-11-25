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

class DOTDNS {
	var $PostString = null;
	var $RawData = null;
	var $OutputData = null;
	var $Values = null;

	function DOTDNS() {
		$this->PostString = "";
		$this->RawData = "";
	}


	function AddParam($Name, &$Value) {
		$this->PostString .= $Name . "=" . urlencode( $Value ) . "&";
	}


	function DumpParam($Show) {
		$tmp = $this->PostString;

		if ($Show) {
			echo "###" . $tmp . "###";
			return null;
		}

		return $tmp;
	}


	function DoTransaction() {
		$Values = "";
		$host = "apidns.be";
		$port = 86;
		$address = gethostbyname( $host );
		$socket = fsockopen( $host, 80 );

		if (!$socket) {
			$this->AddError( "socket() failed: " . strerror( $socket ) );
			return null;
		}

		$in = "GET /api/api.php?" . $this->PostString . ( ( " HTTP/1.1
Host: " . $host . "
" ) . "
Connection: Close

" );
		$out = "";
		fputs( $socket, $in );

		if ($out = fread( $socket, 2048 )) {
			$this->RawData .= $out;
		}

		fclose( $socket );

		if (!is_null( $this->RawData )) {
			if (eregi( "HTTP/1.1 200", $this->RawData )) {
				list(,$this->OutputData) = explode( "\n\r\n", $this->RawData );
				$this->OutputData = explode( "\n", $this->OutputData );
				$this->OutputData[0] = "";
				$this->OutputData = trim( implode( "\n", $this->OutputData ) );

				if (substr( $this->OutputData, 0, 5 ) != "<?xml") {
					$this->Values = false;
					return false;
				}
			}
			else {
				$this->Values = false;
				return false;
			}
		}

		$this->Values = false;
		return false;
	}


	function ParseResponse() {
		$content = $this->OutputData;
		$p = xml_parser_create();
		xml_parse_into_struct( $p, $content, $vals, $index );
		xml_parser_free( $p );
		foreach ($vals as $key => $val) {

			if ($val["type"] != "cdata") {
				$n[$key] = $val;
				continue;
			}
		}

		$i = 8;
		foreach ($n as $k => $v) {

			if ($v["type"] == "open") {
				++$i;

				if (is_array( $v["attributes"] )) {
					foreach ($v["attributes"] as $attname => $attvalue) {
						$values[$i]["@" . $attname] = $attvalue;
					}
				}

				$name[$i] = $v["tag"];
				continue;
			}


			if ($v["type"] == "close") {
				$f = $values[$i];
				$n = $name[$i];
				--$i;
				$values[$i][$n] = $f;
				continue;
			}

			$values[$i][$v["tag"]] = $v["value"];
		}

		return $values[0];
	}


}


function dotdns_getConfigArray() {
	$configarray = array( "Username" => array( "Type" => "text", "Size" => "30", "Description" => "Enter your DOTDNS Reseller Account Username here" ), "Password" => array( "Type" => "password", "Size" => "30", "Description" => "Enter your DOTDNS Reseller Account Password here" ) );
	return $configarray;
}


function dotdns_SaveNameservers($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$DotDNS = new DotDNS();
	$DotDNS->AddParam( "username", $username );
	$DotDNS->AddParam( "password", $password );
	$DotDNS->AddParam( "command", "NSUPDATE" );
	$DotDNS->AddParam( "domainname", $sld );
	$DotDNS->AddParam( "domaintld", $tld );
	$DotDNS->AddParam( "Language", "en" );
	$DotDNS->AddParam( "NameServer1", $nameserver1 );
	$DotDNS->AddParam( "NameServer2", $nameserver2 );
	$DotDNS->AddParam( "NameServer3", $nameserver3 );
	$DotDNS->AddParam( "NameServer4", $nameserver4 );
	$DotDNS->AddParam( "NameServer5", $nameserver5 );
	$DotDNS->DoTransaction();

	if ($DotDNS->Values) {
		if ($DotDNS->Values["DOTDNS"]["RESULT"]["@CODE"] != "OK") {
			$values["error"] = $DotDNS->Values["DOTDNS"]["RESULT"]["MSG"];
		}
	}
	else {
		$values["error"] = "Can't connect to the API server.";
	}

	return $values;
}


function dotdns_RegisterDomain($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$RegistrantFirstName = $params["firstname"];
	$RegistrantLastName = $params["lastname"];
	$RegistrantAddress1 = $params["address1"];
	$RegistrantAddress2 = $params["address2"];
	$RegistrantCity = $params["city"];
	$RegistrantStateProvince = $params["state"];
	$RegistrantPostalCode = $params["postcode"];
	$RegistrantEmailAddress = $params["email"];
	$RegistrantCountry = $params["country"];

	if ($params["country"] == "NL") {
		$telcodecode = "+31";
	}
	else {
		if ($params["country"] == "BE") {
			$telcodecode = "+32";
		}
		else {
			if ($params["country"] == "FR") {
				$telcodecode = "+33";
			}
		}
	}

	$mobilenumer = str_replace( "-", "", $params["phonenumber"] );
	$mobilenumer = str_replace( ".", "", $params["phonenumber"] );
	$mobilenumer = str_replace( "+", "", $params["phonenumber"] );
	$mobilenumer = substr( $params["phonenumber"], 1, 20 );
	$RegistrantPhone = "" . $telcodecode . "." . $mobilenumer;
	$AdminFirstName = $params["adminfirstname"];
	$AdminLastName = $params["adminlastname"];
	$AdminAddress1 = $params["adminaddress1"];
	$AdminAddress2 = $params["adminaddress2"];
	$AdminCity = $params["admincity"];
	$AdminStateProvince = $params["adminstate"];
	$AdminPostalCode = $params["adminpostcode"];
	$AdminCountry = $params["admincountry"];
	$AdminEmailAddress = $params["adminemail"];
	$AdminPhone = $params["adminphonenumber"];
	$DotDNS = new DotDNS();
	$DotDNS->AddParam( "username", $username );
	$DotDNS->AddParam( "password", $password );
	$DotDNS->AddParam( "command", "REGISTER" );
	$DotDNS->AddParam( "domainname", $sld );
	$DotDNS->AddParam( "domaintld", $tld );
	$DotDNS->AddParam( "FirstName", $RegistrantFirstName );
	$DotDNS->AddParam( "LastName", $RegistrantLastName );
	$DotDNS->AddParam( "CompanyName", "" );
	$DotDNS->AddParam( "Address1", $RegistrantAddress1 );
	$DotDNS->AddParam( "City", $RegistrantCity );
	$DotDNS->AddParam( "Postal", $RegistrantPostalCode );
	$DotDNS->AddParam( "Country", "" . $RegistrantCountry );
	$DotDNS->AddParam( "Phone", $RegistrantPhone );
	$DotDNS->AddParam( "Email", $RegistrantEmailAddress );
	$DotDNS->AddParam( "Language", "en" );
	$DotDNS->AddParam( "NameServer1", $nameserver1 );
	$DotDNS->AddParam( "NameServer2", $nameserver2 );
	$DotDNS->AddParam( "NameServer3", $nameserver3 );
	$DotDNS->AddParam( "NameServer4", $nameserver4 );
	$DotDNS->AddParam( "NameServer5", $nameserver5 );
	$DotDNS->DoTransaction();

	if ($DotDNS->Values) {
		if ($DotDNS->Values["DOTDNS"]["RESULT"]["@CODE"] != "OK") {
			$values["error"] = $DotDNS->Values["DOTDNS"]["RESULT"]["MSG"];
		}
	}
	else {
		$values["error"] = "Can't connect to the API server.";
	}

	return $values;
}


function dotdns_TransferDomain($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$transfersecret = $params["transfersecret"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$RegistrantFirstName = $params["firstname"];
	$RegistrantLastName = $params["lastname"];
	$RegistrantAddress1 = $params["address1"];
	$RegistrantAddress2 = $params["address2"];
	$RegistrantCity = $params["city"];
	$RegistrantStateProvince = $params["state"];
	$RegistrantPostalCode = $params["postcode"];
	$RegistrantEmailAddress = $params["email"];
	$RegistrantCountry = $params["country"];

	if ($params["country"] == "NL") {
		$telcodecode = "+31";
	}
	else {
		if ($params["country"] == "BE") {
			$telcodecode = "+32";
		}
		else {
			if ($params["country"] == "FR") {
				$telcodecode = "+33";
			}
		}
	}

	$mobilenumer = str_replace( "-", "", $params["phonenumber"] );
	$mobilenumer = str_replace( ".", "", $params["phonenumber"] );
	$mobilenumer = str_replace( "+", "", $params["phonenumber"] );
	$mobilenumer = substr( $params["phonenumber"], 1, 20 );
	$RegistrantPhone = "" . $telcodecode . "." . $mobilenumer;
	$AdminFirstName = $params["adminfirstname"];
	$AdminLastName = $params["adminlastname"];
	$AdminAddress1 = $params["adminaddress1"];
	$AdminAddress2 = $params["adminaddress2"];
	$AdminCity = $params["admincity"];
	$AdminStateProvince = $params["adminstate"];
	$AdminPostalCode = $params["adminpostcode"];
	$AdminCountry = $params["admincountry"];
	$AdminEmailAddress = $params["adminemail"];
	$AdminPhone = $params["adminphonenumber"];
	$DotDNS = new DotDNS();
	$DotDNS->AddParam( "username", $username );
	$DotDNS->AddParam( "password", $password );
	$DotDNS->AddParam( "command", "TRANSFER" );
	$DotDNS->AddParam( "domainname", $sld );
	$DotDNS->AddParam( "domaintld", $tld );
	$DotDNS->AddParam( "Language", "en" );
	$DotDNS->AddParam( "NameServer1", $nameserver1 );
	$DotDNS->AddParam( "NameServer2", $nameserver2 );
	$DotDNS->AddParam( "NameServer3", $nameserver3 );
	$DotDNS->AddParam( "NameServer4", $nameserver4 );
	$DotDNS->AddParam( "NameServer5", $nameserver5 );
	$DotDNS->AddParam( "auth", $transfersecret );
	$DotDNS->DoTransaction();

	if ($DotDNS->Values) {
		if ($DotDNS->Values["DOTDNS"]["RESULT"]["@CODE"] != "OK") {
			$values["error"] = $DotDNS->Values["DOTDNS"]["RESULT"]["MSG"];
		}
	}
	else {
		$values["error"] = "Can't connect to the API server.";
	}

	return $values;
}


function dotdns_SaveContactDetails($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$firstname = $params["contactdetails"]["Registrant"]["First Name"];
	$lastname = $params["contactdetails"]["Registrant"]["Last Name"];
	$city = $params["contactdetails"]["Registrant"]["City"];
	$address = $params["contactdetails"]["Registrant"]["Address 1"];
	$postal = $params["contactdetails"]["Registrant"]["Postal"];
	$country = $params["contactdetails"]["Registrant"]["Country"];
	$phone = $params["contactdetails"]["Registrant"]["Telephone"];
	$email = $params["contactdetails"]["Registrant"]["Email"];
	$DotDNS->AddParam( "username", $username );
	$DotDNS->AddParam( "password", $password );
	$DotDNS->AddParam( "command", "CONTACTUPDATE" );
	$DotDNS->AddParam( "contacttype", "licensee" );
	$DotDNS->AddParam( "domainname", $sld );
	$DotDNS->AddParam( "domaintld", $tld );
	$DotDNS->AddParam( "FirstName", $firstname );
	$DotDNS->AddParam( "LastName", $lastname );
	$DotDNS->AddParam( "Address1", $address );
	$DotDNS->AddParam( "City", $city );
	$DotDNS->AddParam( "Postal", $postal );
	$DotDNS->AddParam( "Country", $country );
	$DotDNS->AddParam( "Phone", $phone );
	$DotDNS->AddParam( "Email", $email );
	$DotDNS->AddParam( "Language", "en" );

	if ($DotDNS->Values) {
		if ($DotDNS->Values["DOTDNS"]["RESULT"]["@CODE"] != "OK") {
			$values["error"] = $DotDNS->Values["DOTDNS"]["RESULT"]["MSG"];
		}
	}
	else {
		$values["error"] = "Can't connect to the API server.";
	}

	return $values;
}


?>