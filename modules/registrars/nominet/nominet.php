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

function nominet_getConfigArray() {
	$configarray = array( "Description" => array( "Type" => "System", "Value" => "The Official UK Domain Registry Module" ), "Username" => array( "Type" => "text", "Size" => "25", "Description" => "" ), "Password" => array( "Type" => "password", "Size" => "25", "Description" => "" ), "TestMode" => array( "Type" => "yesno" ), "AllowClientTAGChange" => array( "Type" => "yesno", "Description" => "Tick to allow clients to change TAGs on domains" ), "DeleteOnTransfer" => array( "Type" => "yesno", "Description" => "Tick this box if you want the domain to be deleted entirely on RELEASE" ) );
	return $configarray;
}


function nominet_GetNameservers($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$xml = "  <command>
            <info>
              <domain:info
                xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
                <domain:name hosts=\"all\">" . $nominet->getDomain() . "</domain:name>
                </domain:info>
            </info>
            <clTRID>ABC-12345</clTRID>
         </command>
       </epp>";
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			$x = 6;
			$values = array();
			$xmldata = $nominet->getResponseArray();
			foreach ($xmldata["EPP"]["RESPONSE"]["RESDATA"]["DOMAIN:INFDATA"]["DOMAIN:NS"]["DOMAIN:HOSTOBJ"] as $discard => $nsdata) {
				$values["ns" . $x] = $nsdata;
				++$x;
			}

			return $values;
		}

		return array( "error" => $nominet->getLastError() );
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_SaveNameservers($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$removeNS = array();
		$removeNS = nominet_GetNameservers( $params );

		if (0 < count( $removeNS )) {
			$removeXML = "
                            <domain:rem>
                                   <domain:ns>
                        ";
			foreach ($removeNS as $rm) {
				$removeXML .= "<domain:hostObj>" . $rm . "</domain:hostObj>
                                ";
			}

			$removeXML .= " </domain:ns>
                                      </domain:rem>
                        ";
		}
		else {
			$removeXML = "";
		}

		$ns = array();
		$ns[1] = $params["ns1"];
		$ns[2] = $params["ns2"];
		$xml = "  <command>
                    <update>
                    <domain:update
                    xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\"
                    xsi:schemaLocation=\"urn:ietf:params:xml:ns:domain-1.0
                    domain-1.0.xsd\">
                      <domain:name>" . $nominet->getDomain() . ( "</domain:name>
                      <domain:add>
                        <domain:ns>
                          <domain:hostObj>" . $params["ns1"] . "</domain:hostObj>
                          <domain:hostObj>" . $params["ns2"] . "</domain:hostObj>
               " );

		if ($params["ns3"]) {
			$ns[3] = $params["ns3"];
			$xml .= "<domain:hostObj>" . $params["ns3"] . "</domain:hostObj>
                    ";
		}


		if ($params["ns4"]) {
			$ns[4] = $params["ns4"];
			$xml .= "<domain:hostObj>" . $params["ns4"] . "</domain:hostObj>
                    ";
		}


		if ($params["ns5"]) {
			$ns[5] = $params["ns5"];
			$xml .= "<domain:hostObj>" . $params["ns5"] . "</domain:hostObj>
                    ";
		}

		$xml .= "</domain:ns>
                </domain:add>" . $removeXML . "
               </domain:update>
             </update>
           <clTRID>ABC-12345</clTRID>
         </command>
        </epp>";
		nominet_createHost( $nominet, $ns );
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			$x = 7;
			$values = array();
			$xmldata = $nominet->getResponseArray();
			foreach ($xmldata["EPP"]["RESPONSE"]["RESDATA"]["DOMAIN:INFDATA"]["DOMAIN:NS"]["DOMAIN:HOSTOBJ"] as $discard => $nsdata) {
				$values["ns" . $x] = $nsdata;
				++$x;
			}

			return $values;
		}

		return array( "error" => $nominet->getLastError() );
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_getLegalTypeID($LegalType) {
	if ($LegalType == "Individual") {
		$LegalTypeID = "IND";
	}
	else {
		if ($LegalType == "UK Limited Company") {
			$LegalTypeID = "LTD";
		}
		else {
			if ($LegalType == "UK Public Limited Company") {
				$LegalTypeID = "PLC";
			}
			else {
				if ($LegalType == "UK Partnership") {
					$LegalTypeID = "PTNR";
				}
				else {
					if ($LegalType == "Sole Trader") {
						$LegalTypeID = "STRA";
					}
					else {
						if ($LegalType == "UK Limited Liability Partnership") {
							$LegalTypeID = "LLP";
						}
						else {
							if ($LegalType == "UK Industrial/Provident Registered Company") {
								$LegalTypeID = "IP";
							}
							else {
								if ($LegalType == "UK School") {
									$LegalTypeID = "SCH";
								}
								else {
									if ($LegalType == "UK Registered Charity") {
										$LegalTypeID = "RCHAR";
									}
									else {
										if ($LegalType == "UK Government Body") {
											$LegalTypeID = "GOV";
										}
										else {
											if ($LegalType == "UK Corporation by Royal Charter") {
												$LegalTypeID = "CRC";
											}
											else {
												if ($LegalType == "UK Statutory Body") {
													$LegalTypeID = "STAT";
												}
												else {
													if ($LegalType == "UK Entity (other)") {
														$LegalTypeID = "OTHER";
													}
													else {
														if ($LegalType == "Non-UK Individual (representing self)") {
															$LegalTypeID = "OTHER";
														}
														else {
															if ($LegalType == "Foreign Organization") {
																$LegalTypeID = "FCORP";
															}
															else {
																if ($LegalType == "Other foreign organizations") {
																	$LegalTypeID = "FOTHER";
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

	return $LegalTypeID;
}


function nominet_RegisterDomain($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$RegistrantName = $params["additionalfields"]["Registrant Name"];

		if (!$RegistrantName) {
			$RegistrantName = $params["additionalfields"]["Company Name"];
		}


		if (!trim( $RegistrantName )) {
			return array( "error" => "Registrant Name is missing. Please check field on domains tab" );
		}

		$LegalType = $params["additionalfields"]["Legal Type"];
		$CompanyIDNumber = $params["additionalfields"]["Company ID Number"];
		$WhoisOptOut = $params["additionalfields"]["WHOIS Opt-out"];
		$LegalTypeID = nominet_getLegalTypeID( $LegalType );

		if (!$LegalTypeID) {
			return array( "error" => "Legal Type is missing. Please check field on domains tab" );
		}


		if ($LegalTypeID != "IND") {
			$WhoisOptOut = "";
		}

		$contactID = nominet_createContact( $nominet, $params );

		if (is_array( $contactID )) {
			return $contactID;
		}

		$ns = array();
		$ns[1] = $params["ns1"];
		$ns[2] = $params["ns2"];
		$xml = "
            <command>
              <create>
                <domain:create
                 xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\"
                 xsi:schemaLocation=\"urn:ietf:params:xml:ns:domain-1.0
                 domain-1.0.xsd\">
                   <domain:name>" . $nominet->getDomain() . ( "</domain:name>
                   <domain:period unit=\"y\">" . $params["regperiod"] . "</domain:period>
                     <domain:ns>
                      <domain:hostObj>" . $ns["1"] . "</domain:hostObj>
                      <domain:hostObj>" . $ns["2"] . "</domain:hostObj>
                     " );

		if ($params["ns3"]) {
			$ns[3] = $params["ns3"];
			$xml .= "<domain:hostObj>" . $params["ns3"] . "</domain:hostObj>
                                            ";
		}


		if ($params["ns4"]) {
			$ns[4] = $params["ns4"];
			$xml .= "<domain:hostObj>" . $params["ns4"] . "</domain:hostObj>
                                            ";
		}


		if ($params["ns5"]) {
			$ns[5] = $params["ns5"];
			$xml .= "<domain:hostObj>" . $params["ns5"] . "</domain:hostObj>
                                            ";
		}

		$xml .= " </domain:ns>
                     <domain:registrant>" . $contactID . "</domain:registrant>
                     <domain:authInfo>
                       <domain:pw></domain:pw>
                     </domain:authInfo>
                  </domain:create>
               </create>
            <clTRID>ABC-12345</clTRID>
          </command>
        </epp>
            ";
		nominet_createHost( $nominet, $ns );
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}
		}
		else {
			return array( "error" => $nominet->getLastError() );
		}
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_TransferDomain($params) {
}


function nominet_RenewDomain($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$expiry = get_query_val( "tbldomains", "expirydate", array( "id" => $params["domainid"] ) );
		$xml = "  <command>
                <renew>
		  <domain:renew
		  xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\"
		  xsi:schemaLocation=\"urn:ietf:params:xml:ns:domain-1.0
		  domain-1.0.xsd\">
                    <domain:name>" . $nominet->getDomain() . ( "</domain:name>
                    <domain:curExpDate>" . $expiry . "</domain:curExpDate>
                    <domain:period unit=\"y\">" . $params["regperiod"] . "</domain:period>
                  </domain:renew>
                </renew>
         <clTRID>ABC-12345</clTRID>
       </command>
     </epp>" );
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			return array();
		}

		return array( "error" => $nominet->getLastError() );
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_GetContactDetails($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$xml = "  <command>
            <info>
              <domain:info
                xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
                <domain:name hosts=\"all\">" . $nominet->getDomain() . "</domain:name>
                </domain:info>
            </info>
            <clTRID>ABC-12345</clTRID>
         </command>
       </epp>";
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			$xmldata = $nominet->getResponseArray();
			$contactID = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["DOMAIN:INFDATA"]["DOMAIN:REGISTRANT"];
			$xml = "  <command>
                        <info>
                        <contact:info xmlns:contact=\"urn:ietf:params:xml:ns:contact-1.0\"
                          xsi:schemaLocation=\"urn:ietf:params:xml:ns:contact-1.0
                          contact-1.0.xsd\">
                            <contact:id>" . $contactID . "</contact:id>
                          </contact:info>
                        </info>
                      <clTRID>ABC-12345</clTRID>
                    </command>
                  </epp>";
			$success = $nominet->call( $xml );

			if ($success) {
				if ($nominet->isErrorCode()) {
					return array( "error" => $nominet->getErrorDesc() );
				}

				$xmldata = $nominet->getResponseArray();
				$values = array();
				$values["Registrant"]["Contact Name"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:NAME"];
				$values["Registrant"]["Street"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:ADDR"]["CONTACT:STREET"];
				$values["Registrant"]["City"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:ADDR"]["CONTACT:CITY"];
				$values["Registrant"]["County"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:ADDR"]["CONTACT:SP"];
				$values["Registrant"]["Postcode"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:ADDR"]["CONTACT:PC"];
				$values["Registrant"]["Country"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:POSTALINFO"]["CONTACT:ADDR"]["CONTACT:CC"];
				$values["Registrant"]["Phone Number"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:VOICE"];
				$values["Registrant"]["Email Address"] = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:INFDATA"]["CONTACT:EMAIL"];
				return $values;
			}

			return array( "error" => $nominet->getLastError() );
		}

		return array( "error" => $nominet->getLastError() );
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_SaveContactDetails($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$xml = "  <command>
            <info>
              <domain:info
                xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
                <domain:name hosts=\"all\">" . $nominet->getDomain() . "</domain:name>
                </domain:info>
            </info>
            <clTRID>ABC-12345</clTRID>
         </command>
       </epp>";
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			$xmldata = $nominet->getResponseArray();
			$contactID = $xmldata["EPP"]["RESPONSE"]["RESDATA"]["DOMAIN:INFDATA"]["DOMAIN:REGISTRANT"];
			$xml = "  <command>
                        <update>
                          <contact:update
                          xmlns:contact=\"urn:ietf:params:xml:ns:contact-1.0\"
                          xsi:schemaLocation=\"urn:ietf:params:xml:ns:contact-1.0
                          contact-1.0.xsd\">
                          <contact:id>" . $contactID . "</contact:id>
                            <contact:chg>
                              <contact:postalInfo type=\"loc\">
                              <contact:name>" . $params["contactdetails"]["Registrant"]["Contact Name"] . "</contact:name>
                              <contact:addr>
                                <contact:street>" . $params["contactdetails"]["Registrant"]["Street"] . "</contact:street>
                                <contact:city>" . $params["contactdetails"]["Registrant"]["City"] . "</contact:city>
                                <contact:sp>" . $params["contactdetails"]["Registrant"]["County"] . "</contact:sp>
                                <contact:pc>" . strtoupper( $params["contactdetails"]["Registrant"]["Postcode"] ) . "</contact:pc>
                                <contact:cc>" . $params["contactdetails"]["Registrant"]["Country"] . "</contact:cc>
                               </contact:addr>
                              </contact:postalInfo>
                            <contact:voice>" . $params["contactdetails"]["Registrant"]["Phone Number"] . "</contact:voice>
                            <contact:email>" . $params["contactdetails"]["Registrant"]["Email Address"] . "</contact:email>
                            </contact:chg>
                          </contact:update>
                         </update>
                         <clTRID>ABC-12345</clTRID>
                       </command>
                     </epp>";
			$success = $nominet->call( $xml );

			if ($success) {
				if ($nominet->isErrorCode()) {
					return array( "error" => $nominet->getErrorDesc() );
				}

				return array();
			}
		}
		else {
			return array( "error" => $nominet->getLastError() );
		}
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_ReleaseDomain($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$transfertag = $params["transfertag"];
		$xml = "  <command>
	        <update>
		<r:release
		xmlns:r=\"http://www.nominet.org.uk/epp/xml/std-release-1.0\"
		xsi:schemaLocation=\"http://www.nominet.org.uk/epp/xml/std-release-1.0
		std-release-1.0.xsd\">
		<r:domainName>" . $nominet->getDomain() . ( "</r:domainName>
		<r:registrarTag>" . $transfertag . "</r:registrarTag>
		</r:release>
		</update>
               <clTRID>ABC-12345</clTRID>
              </command>
            </epp>" );
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}


			if ($params["DeleteOnTransfer"]) {
				delete_query( "tbldomains", array( "id" => $params["domainid"] ) );
				return null;
			}

			update_query( "tbldomains", array( "status" => "Cancelled" ), array( "id" => $params["domainid"] ) );
			return null;
		}

		return array( "error" => $nominet->getLastError() );
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_Sync($params) {
	$nominet = WHMCS_Nominet::init( $params );

	if ($nominet->connectAndLogin()) {
		$xml = "  <command>
                <info>
		<domain:info
		xmlns:domain=\"urn:ietf:params:xml:ns:domain-1.0\">
                  <domain:name hosts = \"all\">" . $nominet->getDomain() . "</domain:name>
                </domain:info>
                </info>
                <clTRID>ABC-12345</clTRID>
              </command>
            </epp>";
		$success = $nominet->call( $xml );

		if ($success) {
			if ($nominet->isErrorCode()) {
				return array( "error" => $nominet->getErrorDesc() );
			}

			$xmldata = $nominet->getResponseArray();
			$expirydate = trim( $xmldata["EPP"]["RESPONSE"]["RESDATA"]["DOMAIN:INFDATA"]["DOMAIN:EXDATE"] );
			$expirydate = substr( $expirydate, 0, 10 );

			if ($expirydate) {
				$rtn = array();
				$rtn["expirydate"] = $expirydate;

				if (date( "Ymd" ) <= str_replace( "-", "", $expirydate )) {
					$rtn["active"] = true;
				}
				else {
					$rtn["expired"] = true;
				}

				return $rtn;
			}
		}
		else {
			return array( "error" => $nominet->getLastError() );
		}
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_createContact($nominet, $params) {
	$name = trim( $params["additionalfields"]["Registrant Name"] );

	if (!( isset( $name ) && 0 < strlen( $name ) )) {
		$name = $params["firstname"] . " " . $params["lastname"];
	}

	$company = $params["companyname"];
	$street = $params["address1"];
	$city = $params["city"];
	$county = $params["state"];
	$postcode = $params["postcode"];
	$country = $params["country"];
	$phonenumber = $params["fullphonenumber"];
	$email = $params["email"];
	$LegalType = $params["additionalfields"]["Legal Type"];
	$CompanyIDNumber = $params["additionalfields"]["Company ID Number"];
	$WhoisOptOut = $params["additionalfields"]["WHOIS Opt-out"];
	$LegalTypeID = nominet_getLegalTypeID( $LegalType );

	if ($LegalTypeID != "IND") {
		$WhoisOptOut = "";
	}

	$WhoisOptOut = ($WhoisOptOut ? "Y" : "N");
	$contactID = "WHMCS" . $params["domainid"] . rand( 1000, 9999 );
	$xml = "  <command>
	     <create>
	     <contact:create
		     xmlns:contact=\"urn:ietf:params:xml:ns:contact-1.0\"
		     xsi:schemaLocation=\"urn:ietf:params:xml:ns:contact-1.0
		     contact-1.0.xsd\">
			     <contact:id>" . $contactID . "</contact:id>
			     <contact:postalInfo type=\"loc\">
				 <contact:name>" . $name . "</contact:name>
				 ";

	if (( isset( $company ) && 0 < strlen( $company ) )) {
		$xml .= "<contact:org>" . $company . "</contact:org>
			";
	}

	$xml .= "<contact:addr>
				 <contact:street>" . $street . "</contact:street>
				 <contact:city>" . $city . "</contact:city>
				 <contact:sp>" . $county . "</contact:sp>
				 <contact:pc>" . $postcode . "</contact:pc>
				<contact:cc>" . $country . "</contact:cc>
				     </contact:addr>
			     </contact:postalInfo>
				     <contact:voice>" . $phonenumber . "</contact:voice>
				     <contact:email>" . $email . "</contact:email>
				     <contact:authInfo>
				 <contact:pw>" . substr( sha1( time() ), 0, 15 ) . "</contact:pw>
				 </contact:authInfo>
			     </contact:create>
			   </create>
<extension>
<contact-ext:create
xmlns:contact-ext=\"http://www.nominet.org.uk/epp/xml/contact-nom-ext-1.0\">
";

	if (( isset( $name ) && 0 < strlen( $name ) )) {
		$xml .= "<contact-ext:trad-name>" . $name . "</contact-ext:trad-name>
";
	}

	$xml .= "<contact-ext:type>" . $LegalTypeID . "</contact-ext:type>
";

	if (( isset( $companyIDNumber ) && 0 < strlen( $companyIDNumber ) )) {
		$xml .= "<contact-ext:co-no>" . $companyIDNumber . "</contact-ext:co-no>
";
	}

	$xml .= "<contact-ext:opt-out>" . $WhoisOptOut . "</contact-ext:opt-out>
</contact-ext:create>
</extension>
			<clTRID>ABC-12345</clTRID>
		   </command>
		 </epp>
	";
	$success = $nominet->call( $xml );

	if ($success) {
		if ($nominet->isErrorCode()) {
			if ($nominet->getResultCode() == 2302) {
				++$params["contactCreateCount"];

				if (10 < $params["contactCreateCount"]) {
					return array( "error" => "Failed to create contact. Please contact support." );
				}

				return nominet_createContact( $nominet, $params );
			}

			return array( "error" => $nominet->getErrorDesc() );
		}

		$xmldata = $nominet->getResponseArray();
		return $xmldata["EPP"]["RESPONSE"]["RESDATA"]["CONTACT:CREDATA"]["CONTACT:ID"];
	}

	return array( "error" => $nominet->getLastError() );
}


function nominet_createHost($nominet, $ns = array()) {
	foreach ($ns as $server) {
		$xml = "  <command>
	        <create>
		  <host:create xmlns:host=\"urn:ietf:params:xml:ns:host-1.0\"
		  xsi:schemaLocation=\"urn:ietf:params:xml:ns:host-1.0
		  host-1.0.xsd\">
		  ";
		$xml .= "<host:name>" . $server . "</host:name>
		  </host:create>
		</create>
              <clTRID>ABC-12345</clTRID>
	    </command>
	  </epp>
	  ";
		$result = $nominet->call( $xml );
	}

}


require dirname(__FILE__) . "/class.Nominet.php";
?>