<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.16
 * @ Author   : MTIMER
 * @ Release on : 2014-01-22
 * @ Website  : http://www.mtimer.cn
 *
 * */

class ThinMPI {

    //var to do all the security stuff
    var $security;

    //holds the data of the configuration file
    var $conf;

    /**
     * creates a new ThinMPI core Object
     *
     */
    function ThinMPI () {
        global $config;
        $this->security = & new Security();
        $this->conf = LoadConfiguration();
    }

    /**
     * strips all whitespaces from the given message
     */    
    function strip( $message ) {
        $message = str_replace( " ", "", $message );
        $message = str_replace( "\t", "", $message );
        $message = str_replace( "\n", "", $message );
        return $message;
    }
    
    /**
     * encoding of special chars: <>&"'
     * first decode (maybe some chars are encoded)
     * htmlspecialchars_decode >= PHP 5.1.0RC1
     */    
    function encode_html($text) {
        $trans = array ("&amp;" => "&", "&quot;" => "\"", "&#039;" => "'", "&lt;" => "<", "&gt;" => ">");
        return htmlspecialchars(strtr($text, $trans), ENT_QUOTES);
        // return htmlspecialchars(html_entity_decode($text, ENT_QUOTES), ENT_QUOTES);
    }

    /**
     * This function sends a Post Request with the data we want to send
     * @param host (acceptor server adress)
     * @param port (what port are you using 80, 8080)
     * @param path (path where to put the data)
     * @param data_to_send (the xml we want to sent)
     * @return res (response from server)
     */
    function PostToHost($url, $timeout, $data_to_send) {
    	$idx = strrpos($url, ":");
    	$host = substr($url, 0, $idx);
    	$url = substr($url, $idx + 1);
    	$idx = strpos($url, "/");
    	$port = substr($url, 0, $idx);
    	$path = substr($url, $idx);
    	//print $host . " - " . $port . " - " . $path . ".";
	$this->log("sending to " . $host . ":" . $port . $path . ": " . $data_to_send);
        $fsp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if($fsp) {
            fputs($fsp, "POST $path HTTP/1.0\r\n");
            fputs($fsp, "Accept: text/html\r\n");
            fputs($fsp, "Accept: charset=ISO-8859-1\r\n");
            fputs($fsp, "Content-Length:".strlen($data_to_send)."\r\n");
            fputs($fsp, "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n");
            fputs($fsp, $data_to_send, strlen($data_to_send));

            while(!feof($fsp)) {
                $res .= fgets($fsp, 128);
            }
            fclose($fsp);
            
            $this->log("receiving from " . $host . ":" . $port . $path . ": " . $res);
            
	    return $res;
        }
        else {
            $this->log("error from " . $host . ":" . $port . $path . ": " . $errstr);
            return "Error: " . $errstr;
        }
    }
    
    function PostToHostProxy($proxy, $url, $timeout, $data_to_send) {
    	$idx = strrpos($proxy, ":");
    	$host = substr($proxy, 0, $idx);
    	$idx = strpos($proxy, ":");
    	$port = substr($proxy, $idx+1);
    	
	$this->log("sending to " . $host . ":" . $port . ": " . $data_to_send);
        $fsp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if($fsp) {
            
            fputs($fsp, "POST $url HTTP/1.0\r\n");
            //fputs($fsp, "Host:www.acrm.de:443\r\n");
            fputs($fsp, "Accept: text/html\r\n");
            fputs($fsp, "Connection: Close\r\n");
            fputs($fsp, "Accept: charset=ISO-8859-1\r\n");
            fputs($fsp, "Content-Length:".strlen($data_to_send)."\r\n");
            fputs($fsp, "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n");
            fputs($fsp, $data_to_send, strlen($data_to_send));

            while(!feof($fsp)) {
                $res .= fgets($fsp, 128);
            }
    
            while(!feof($fsp)) {
                $res .= fgets($fsp, 128);
            }
            
            fclose($fsp);
            
            $this->log("receiving from " . $host . ":" . $port . $path . ": " . $res);
	    return $res;
        }
        else {
            $this->log("error from " . $host . ":" . $port . $path . ": " . $errstr);
            return "Error: " . $errstr;
        }
    }

    /**
     * This method logs the message given to a file.
     */
    function log($message) {
    	if ($this->conf["LOGFILE"] == "")
    		return;
    			
	$file = fopen($this->conf["LOGFILE"], 'a');
	fputs($file, $message, strlen($message));
        fputs($file, "\r\n\r\n");
        fclose($file);
    }

    /**
     * This method extracts a single value from a given xml-file
     */
    function parseFromXml ($key, $xml) {
        $begin = 0;
        $end = 0;

        $begin = strpos($xml, "<" . $key . ">");
        if($begin===false) {
          return false;
        }

        $begin += strlen($key) + 2;
        $end = strpos($xml, "</" . $key . ">");

        if($end===false) {
          return false;
        }

        $result = substr($xml, $begin, $end - $begin);
        $result = str_replace( "&amp;", "&", $result );

        return utf8_decode( $result );
    }

    function parseError($answer, $res) {
	$errorMsg = $this->parseFromXml("errorMessage", $answer);
	$errorCode = $this->parseFromXml("errorCode", $answer);
	$errorDetail = $this->parseFromXml("errorDetail", $answer);
	$sugAction = $this->parseFromXml("suggestedAction", $answer);
	$sugExpPeriod = $this->parseFromXml("suggestedExpirationPeriod", $answer);
	$consMsg = $this->parseFromXml("consumerMessage", $answer);
	
	$res->setErrorMessage( $errorMsg );
	$res->setErrorCode( $errorCode );
	$res->setErrorDetail( $errorDetail );
	$res->setSuggestedAction( $sugAction );
	$res->setSuggestedExpirationPeriod( $sugExpPeriod );
	$res->setConsumerMessage( $consMsg );
	
	$res->setOk(false);
	return $res;
    }
    
    /**
     * this method processes a request regardless of the type. 
    */
    function ProcessRequest( $requesttype ) {
        if(is_a($requesttype, "DirectoryRequest")) {
          return $this->processDirRequest( $requesttype );
        } else if( is_a($requesttype, "AcquirerStatusRequest") ) {
          return $this->processStatusRequest( $requesttype );
        } else if( is_a($requesttype, "AcquirerTrxRequest") ) {
            return $this->processTrxRequest( $requesttype );
        }
    }

    /**
     * This method sends HTTP XML DirectoryRequest to the Acquirer system.
     * Befor calling, all mandatory properties have to be set in the Request object
     * by calling the associated setter methods.
     * If the request was successful, the response Object is returned.
     * @param Request Object filled with necessary data for the XML Request
     * @return Response Object with the data of the XML response.
     */
    function processDirRequest( $req ) {
    	
    	if ($req->getMerchantID() == "")
    		$req->setMerchantID($this->conf["MERCHANTID"]);
    	if ($req->getSubID() == "")
		$req->setSubID($this->conf["SUBID"]);
	if ($req->getAuthentication() == "")
		$req->setAuthentication($this->conf["AUTHENTICATIONTYPE"]);

        $res = & new DirectoryResponse();

        if (!$req->checkMandatory()) {
            $res->setErrorMessage("required fields missing.");
            return $res;
        }

	// build concatenated string
	$timestamp = gmdate(Y) . "-" . gmdate(m) . "-" . gmdate(d) . "T" . gmdate(H) . ":" . gmdate(i) . ":" . gmdate(s) . ".000Z";
	$token = "";
	$tokenCode = "";
        if ("SHA1_RSA" == $req->getAuthentication()) {
        	$message =  $timestamp . $req->getMerchantID() . $req->getSubID();
        	$message = $this->strip( $message );

        	//build fingerprint of your own certificate
        	$token = $this->security->createCertFingerprint($this->conf["PRIVATECERT"]);

		//sign the part of the message that need to be signed
            	$tokenCode = $this->security->signMessage( $this->conf["PRIVATEKEY"], $this->conf["PRIVATEKEYPASS"], $message );
		//encode with base64
            	$tokenCode = base64_encode( $tokenCode );
        }

	$reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
		. "<DirectoryReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
		. "<createDateTimeStamp>" . utf8_encode( $timestamp ) . "</createDateTimeStamp>\n"
		. "<Merchant>\n"
		. "<merchantID>" . utf8_encode( $this->encode_html( $req->getMerchantID() ) ) . "</merchantID>\n"
		. "<subID>" . utf8_encode( $req->getSubID() ) . "</subID>\n"
		. "<authentication>" . utf8_encode( $req->getAuthentication() ) . "</authentication>\n"
		. "<token>" . utf8_encode( $token ) . "</token>\n"
		. "<tokenCode>" . utf8_encode( $tokenCode ) . "</tokenCode>\n"
		. "</Merchant>\n"
		. "</DirectoryReq>";

        if ($this->conf["PROXY"] == "") {
        	$answer = $this->PostToHost($this->conf["ACQUIRERURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg);
        }  else {
        	$answer = $this->PostToHostProxy($this->conf["PROXY"], $this->conf["PROXYACQURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg); 
        }
             
	if( strpos($answer, "Error: ") != false) {
		$res->setErrorMessage(substr($answer, 7));
		return $res;
	}

        if( $this->parseFromXml("errorCode", $answer) ) {
        	return $this->parseError($answer, $res);
        }

        $acquirerID = $this->parseFromXml("acquirerID", $answer);
        $res->setAcqirerID( $acquirerID );

        while (strpos($answer, "<issuerID>")) {
                $issuerID = $this->parseFromXml("issuerID", $answer);
                $issuerName = $this->parseFromXml("issuerName", $answer);
                $issuerList = $this->parseFromXml("issuerList", $answer);

                $bean = & new IssuerBean();
                $bean->setIssuerID($issuerID);
                $bean->setIssuerName($issuerName);
                $bean->setIssuerList($issuerList);
                $res->addIssuer($bean);
                $answer = substr($answer, strpos($answer, "</issuerList>") + 13);
	}

        $res->setOk(true);
        return $res;
    }

    /**
     * This method sends HTTP XML AcquirerTrxRequest to the Acquirer system.
     * Befor calling, all mandatory properties have to be set in the Request object
     * by calling the associated setter methods.
     * If the request was successful, the response Object is returned.
     * @param Request Object filled with necessary data for the XML Request
     * @return Response Object with the data of the XML response.
     */
    function processTrxRequest($req) {

    	if ($req->getMerchantID() == "")
    		$req->setMerchantID($this->conf["MERCHANTID"]);
    	if ($req->getSubID() == "")
		$req->setSubID($this->conf["SUBID"]);
	if ($req->getAuthentication() == "")
		$req->setAuthentication($this->conf["AUTHENTICATIONTYPE"]);
	if ($req->getMerchantReturnURL() == "")
		$req->setMerchantReturnURL($this->conf["MERCHANTRETURNURL"]);
	if ($req->getCurrency() == "")
		$req->setCurrency($this->conf["CURRENCY"]);
	if ($req->getExpirationPeriod() == "")
		$req->setExpirationPeriod($this->conf["EXPIRATIONPERIOD"]);
	if ($req->getLanguage() == "")
		$req->setLanguage($this->conf["LANGUAGE"]);
	if ($req->getEntranceCode() == "")
		$req->setEntranceCode($this->conf["ENTRANCECODE"]);
	if ($req->getDescription() == "")
		$req->setDescription($this->conf["DESCRIPTION"]);
		
        $res = new AcquirerTrxResponse();

        if (!$req->checkMandatory()) {
            $res->setErrorMessage ("required fields missing.");
            return $res;
        }

	// build concatenated string
	$timestamp = gmdate(Y) . "-" . gmdate(m) . "-" . gmdate(d) . "T" . gmdate(H) . ":" . gmdate(i) . ":" . gmdate(s) . ".000Z";
	$token = "";
	$tokenCode = "";
        if ( "SHA1_RSA" == $req->getAuthentication() ) {
		$message = $timestamp
                        . $req->getIssuerID()
                        . $req->getMerchantID()
                        . $req->getSubID()
                        . $req->getMerchantReturnURL()
                        . $req->getPurchaseID()
                        . $req->getAmount()
                        . $req->getCurrency()
                        . $req->getLanguage()
                        . $req->getDescription()
                        . $req->getEntranceCode();
		$message = $this->strip( $message );

        	//create fingerprint so the receiver knows what certificate to use
        	$token = $this->security->createCertFingerprint($this->conf["PRIVATECERT"]);

        	//sign the message
        	$tokenCode = $this->security->signMessage( $this->conf["PRIVATEKEY"], $this->conf["PRIVATEKEYPASS"], $message );
        	//encode it with base64
        	$tokenCode = base64_encode( $tokenCode );
    	}
        $reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                . "<AcquirerTrxReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
                . "<createDateTimeStamp>" . utf8_encode( $timestamp ) .  "</createDateTimeStamp>\n"
                . "<Issuer>" . "<issuerID>" . utf8_encode( $this->encode_html( $req->getIssuerID() ) ) . "</issuerID>\n"
                . "</Issuer>\n" 
                . "<Merchant>" . "<merchantID>" . utf8_encode( $this->encode_html( $req->getMerchantID() ) ) . "</merchantID>\n"
                . "<subID>" . utf8_encode( $req->getSubID() ) . "</subID>\n"
                . "<authentication>" . utf8_encode( $req->getAuthentication() ) . "</authentication>\n"
                . "<token>" . utf8_encode( $token ) . "</token>\n" 
                . "<tokenCode>" . utf8_encode( $tokenCode ) . "</tokenCode>\n"
                . "<merchantReturnURL>" . utf8_encode( $this->encode_html( $req->getMerchantReturnURL() ) ) . "</merchantReturnURL>\n" 
                . "</Merchant>\n" 
                . "<Transaction>" . "<purchaseID>" . utf8_encode( $this->encode_html( $req->getPurchaseID() ) ) . "</purchaseID>\n" 
                . "<amount>" . utf8_encode( $req->getAmount() ) . "</amount>\n" 
                . "<currency>" . utf8_encode( $req->getCurrency() ) . "</currency>\n" 
                . "<expirationPeriod>" . utf8_encode( $req->getExpirationPeriod() ) . "</expirationPeriod>\n" 
                . "<language>" . utf8_encode( $req->getLanguage() ) . "</language>\n" 
                . "<description>" . utf8_encode( $this->encode_html( $req->getDescription() ) ) . "</description>\n" 
                . "<entranceCode>" . utf8_encode( $this->encode_html( $req->getEntranceCode() ) ) . "</entranceCode>\n" 
                . "</Transaction>" . "</AcquirerTrxReq>";
                
                
        if ($this->conf["PROXY"] == "") {
        	$answer = $this->PostToHost($this->conf["ACQUIRERURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg);
        }  else {
        	$answer = $this->PostToHostProxy($this->conf["PROXY"], $this->conf["PROXYACQURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg); 
        }

            
	if( strpos($answer, "Error: ") != false) {
		$res->setErrorMessage(substr($answer, 7));
		return $res;
	}

        if( $this->parseFromXml("errorCode", $answer) ) {
        	return $this->parseError($answer, $res);
        }

	$issuerUrl = $this->ParseFromXml("issuerAuthenticationURL", $answer);
	$transactionID = $this->parseFromXml("transactionID", $answer);
       	$res->setIssuerAuthenticationURL($issuerUrl);
       	$res->setTransactionID($transactionID);
	$res->setOk(true);
	return $res;
    }

    /**
     * This method sends HTTP XML AcquirerStatusRequest to the Acquirer system.
     * Befor calling, all mandatory properties have to be set in the Request object
     * by calling the associated setter methods.
     * If the request was successful, the response Object is returned.
     * @param Request Object filled with necessary data for the XML Request
     * @return Response Object with the data of the XML response.
     */
    function processStatusRequest($req) {

    	if ($req->getMerchantID() == "")
    		$req->setMerchantID($this->conf["MERCHANTID"]);
    	if ($req->getSubID() == "")
		$req->setSubID($this->conf["SUBID"]);
	if ($req->getAuthentication() == "")
		$req->setAuthentication($this->conf["AUTHENTICATIONTYPE"]);

        $res = & new AcquirerStatusResponse();

        if (!$req->checkMandatory()) {
            $res->setErrorMessage("required fields missing.");
            return $res;
        }

        // build concatenated string
	$timestamp = gmdate(Y) . "-" . gmdate(m) . "-" . gmdate(d) . "T" . gmdate(H) . ":" . gmdate(i) . ":" . gmdate(s) . ".000Z";
	$token = "";
	$tokenCode = "";
        if ("SHA1_RSA" == $req->getAuthentication()) {
            $message = $timestamp . $req->getMerchantID() . $req->getSubID() . $req->getTransactionID();
            $message = $this->strip( $message );
                            
            //create fingerprint of your own certificate
            $token = $this->security->createCertFingerprint($this->conf["PRIVATECERT"]);
            //sign the message
            $tokenCode = $this->security->signMessage( $this->conf["PRIVATEKEY"], $this->conf["PRIVATEKEYPASS"], $message );
	    //encode with base64
            $tokenCode = base64_encode( $tokenCode );
	}
        $reqMsg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
		. "<AcquirerStatusReq xmlns=\"http://www.idealdesk.com/Message\" version=\"1.1.0\">\n"
		. "<createDateTimeStamp>" . utf8_encode( $timestamp ) . "</createDateTimeStamp>\n"
		. "<Merchant>" . "<merchantID>" . utf8_encode( $this->encode_html( $req->getMerchantID() ) ) . "</merchantID>\n"
		. "<subID>" . utf8_encode( $req->getSubID() ) . "</subID>\n" 
		. "<authentication>" . utf8_encode( $req->getAuthentication() ) . "</authentication>\n" 
		. "<token>" . utf8_encode( $token ) . "</token>\n" 
		. "<tokenCode>" . utf8_encode( $tokenCode ) . "</tokenCode>\n"
		. "</Merchant>\n" 
		. "<Transaction>" . "<transactionID>" . utf8_encode( $this->encode_html( $req->getTransactionID() ) ) . "</transactionID>\n" 
		. "</Transaction>" . "</AcquirerStatusReq>";
		
        if ($this->conf["PROXY"] == "") {
        	$answer = $this->PostToHost($this->conf["ACQUIRERURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg);
        }  else {
        	$answer = $this->PostToHostProxy($this->conf["PROXY"], $this->conf["PROXYACQURL"], $this->conf["ACQUIRERTIMEOUT"], $reqMsg); 
        }
        
        //echo utf8_decode( $answer );
        
	if( strpos($answer, "Error: ") != false) {
		$res->setErrorMessage(substr($answer, 7));
		return $res;
	}

        if( $this->parseFromXml("errorCode", $answer) ) {
        	return $this->parseError($answer, $res);
        }

        $status = $this->parseFromXml("status", $answer); 
        if ( strtoupper("Success") == strtoupper($status) ) {
               	$res->setAuthenticated(true);
        } else {
        	$res->setAuthenticated(false);
        }
	$creationTime = $this->ParseFromXml("createDateTimeStamp", $answer);
	$transactionID = $this->ParseFromXml("transactionID", $answer);
	$consumerAccountNumber = $this->parseFromXml("consumerAccountNumber", $answer);
	$consumerName = $this->ParseFromXml("consumerName", $answer);
	$consumerCity = $this->ParseFromXml("consumerCity", $answer);
	$res->setTransactionID($transactionID);
	$res->setConsumerAccountNumber($consumerAccountNumber);
	$res->setConsumerName($consumerName);
	$res->setConsumerCity($consumerCity);

        // now check the signature
        //create signed message string
        $message = $creationTime . $transactionID . $status . $consumerAccountNumber;
        $message = $this->strip( $message );
           
	//now we want to check the signature that has been sent
        $signature64 = $this->ParseFromXml("signatureValue", $answer);
        //decode the base64 encoded signature
        $sig = base64_decode($signature64);

        //get the fingerprint out of the response
        $fingerprint = $this->ParseFromXml("fingerprint", $answer);

        //search for the certificate file with the given fingerprint
        $certfile = $this->security->getCertificateName($fingerprint, $this->conf);

	if($certfile == false) {
		$res->setAuthenticated(false);
		$res->setErrorMessage("Fingerprint unknown!");
		return $res;
	}
            
        $valid = $this->security->verifyMessage($certfile, $message, $sig );
            
        if( $valid != 1 ) {
                $res->setAuthenticated(false);
                $res->setErrorMessage("Bad signature!");
        	return $res;
        }

        $res->setOk(true);
        return $res;
    }

}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

require_once "myideal_lib.php";
?>