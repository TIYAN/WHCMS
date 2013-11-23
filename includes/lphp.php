<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

class lphp {
	var $debugging = null;
	var $debugstr = null;

	public function process($data) {
		$using_xml = 0;
		$webspace = 1;

		if (isset($data['webspace'])) {
			if ($data['webspace'] == "false") {
				$webspace = 0;
			}
		}


		if (isset($data['debugging']) || isset($data['debug'])) {
			if ($data['debugging'] == "true" || $data['debug'] == "true") {
				$this->debugging = 1;

				if ($webspace) {
					echo "at process, incoming data: <br>";

					while (list($key, $value) = each($data)) {
						echo htmlspecialchars($key) . " = " . htmlspecialchars($value) . "<BR>
";
					}
				}
				else {
					echo "at process, incoming data:
";

					while (list($key, $value) = each($data)) {
						echo "" . $key . " = " . $value . "\r\n";
					}
				}

				reset($data);
			}
		}


		if (isset($data['xml'])) {
			$using_xml = 1;
			$xml = $data['xml'];
		}
		else {
			$xml = $this->buildXML($data);
		}

		$key = $data['keyfile'];
		$host = $data['host'];
		$port = $data['port'];

		if (!extension_loaded("liblphp")) {
			if (!dl("liblphp.so")) {
				exit("cannot load liblphp.so, bye
");
			}
		}


		if ($this->debugging) {
			if ($webspace) {
				echo "<br>sending xml string:<br>" . htmlspecialchars($xml) . "<br><br>";
			}
			else {
				echo ("
sending xml string:
" . $xml . "\r\n") . "\r\n";
			}
		}

		$retstg = send_stg($xml, $key, $host, $port);

		if (strlen($retstg) < 4) {
			exit("cannot connect to lsgs, exiting");
		}


		if ($this->debugging) {
			if ($this->webspace) {
				echo "<br>server responds:<br>" . htmlspecialchars($retstg) . "<br><br>";
			}
			else {
				echo ("
server responds:
 " . $retstg . "\r\n") . "\r\n";
			}
		}


		if ($using_xml != 1) {
			$retarr = $this->decodeXML($retstg);
			return $retarr;
		}

		return $retstg;
	}

	public function curl_process($data) {
		$using_xml = 0;
		$webspace = 1;

		if (isset($data['webspace'])) {
			if ($data['webspace'] == "false") {
				$webspace = 11;
			}
		}

		while (list($key, $value) = each($data)) {
			if ($key != "cardnumber" && $key != "cvmvalue") {
				$debugstr .= ("" . $key . " => " . $value . "\r\n");
			}
		}

		$this->debugstr = $debugstr;
		reset($data);

		if (isset($data['xml'])) {
			$using_xml = 1;
			$xml = $data['xml'];
		}
		else {
			$xml = $this->buildXML($data);
		}


		if ($this->debugging) {
			if ($webspace) {
				echo "<br>sending xml string:<br>" . htmlspecialchars($xml) . "<br><br>";
			}
			else {
				echo ("
sending xml string:
" . $xml . "\r\n") . "\r\n";
			}
		}

		$key = $data['keyfile'];
		$port = $data['port'];
		$host = "https://" . $data['host'] . ":" . $port . "/LSGSXML";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_SSLCERT, $key);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			$result = "Curl Error: " . curl_error($ch);
			return $result;
		}

		curl_close($ch);

		if (strlen($result) < 2) {
			$result = "Could not connect";
			return $result;
		}


		if ($this->debugging) {
			if ($webspace) {
				echo "<br>server responds:<br>" . htmlspecialchars($result) . "<br><br>";
			}
			else {
				echo ("
server responds:
 " . $result . "\r\n") . "\r\n";
			}
		}


		if ($using_xml) {
			return $result;
		}

		$retarr = $this->decodeXML($result);
		return $retarr;
	}

	public function decodeXML($xmlstg) {
		preg_match_all("/<(.*?)>(.*?)\</", $xmlstg, $out, PREG_SET_ORDER);
		$n = 0;

		while (isset($out[$n])) {
			$retarr[$out[$n][1]] = strip_tags($out[$n][0]);
			++$n;
		}

		return $retarr;
	}

	public function buildXML($pdata) {
		$xml = "<order><orderoptions>";

		if (isset($pdata['ordertype'])) {
			$xml .= "<ordertype>" . $pdata['ordertype'] . "</ordertype>";
		}


		if (isset($pdata['result'])) {
			$xml .= "<result>" . $pdata['result'] . "</result>";
		}

		$xml .= "</orderoptions>";
		$xml .= "<creditcard>";

		if (isset($pdata['cardnumber'])) {
			$xml .= "<cardnumber>" . $pdata['cardnumber'] . "</cardnumber>";
		}


		if (isset($pdata['cardexpmonth'])) {
			$xml .= "<cardexpmonth>" . $pdata['cardexpmonth'] . "</cardexpmonth>";
		}


		if (isset($pdata['cardexpyear'])) {
			$xml .= "<cardexpyear>" . $pdata['cardexpyear'] . "</cardexpyear>";
		}


		if (isset($pdata['cvmvalue'])) {
			$xml .= "<cvmvalue>" . $pdata['cvmvalue'] . "</cvmvalue>";
		}


		if (isset($pdata['cvmindicator'])) {
			$xml .= "<cvmindicator>" . $pdata['cvmindicator'] . "</cvmindicator>";
		}


		if (isset($pdata['track'])) {
			$xml .= "<track>" . $pdata['track'] . "</track>";
		}

		$xml .= "</creditcard>";
		$xml .= "<billing>";

		if (isset($pdata['name'])) {
			$xml .= "<name>" . $pdata['name'] . "</name>";
		}


		if (isset($pdata['company'])) {
			$xml .= "<company>" . $pdata['company'] . "</company>";
		}


		if (isset($pdata['address1'])) {
			$xml .= "<address1>" . $pdata['address1'] . "</address1>";
		}
		else {
			if (isset($pdata['address'])) {
				$xml .= "<address1>" . $pdata['address'] . "</address1>";
			}
		}


		if (isset($pdata['address2'])) {
			$xml .= "<address2>" . $pdata['address2'] . "</address2>";
		}


		if (isset($pdata['city'])) {
			$xml .= "<city>" . $pdata['city'] . "</city>";
		}


		if (isset($pdata['state'])) {
			$xml .= "<state>" . $pdata['state'] . "</state>";
		}


		if (isset($pdata['zip'])) {
			$xml .= "<zip>" . $pdata['zip'] . "</zip>";
		}


		if (isset($pdata['country'])) {
			$xml .= "<country>" . $pdata['country'] . "</country>";
		}


		if (isset($pdata['userid'])) {
			$xml .= "<userid>" . $pdata['userid'] . "</userid>";
		}


		if (isset($pdata['email'])) {
			$xml .= "<email>" . $pdata['email'] . "</email>";
		}


		if (isset($pdata['phone'])) {
			$xml .= "<phone>" . $pdata['phone'] . "</phone>";
		}


		if (isset($pdata['fax'])) {
			$xml .= "<fax>" . $pdata['fax'] . "</fax>";
		}


		if (isset($pdata['addrnum'])) {
			$xml .= "<addrnum>" . $pdata['addrnum'] . "</addrnum>";
		}

		$xml .= "</billing>";
		$xml .= "<shipping>";

		if (isset($pdata['sname'])) {
			$xml .= "<name>" . $pdata['sname'] . "</name>";
		}


		if (isset($pdata['saddress1'])) {
			$xml .= "<address1>" . $pdata['saddress1'] . "</address1>";
		}


		if (isset($pdata['saddress2'])) {
			$xml .= "<address2>" . $pdata['saddress2'] . "</address2>";
		}


		if (isset($pdata['scity'])) {
			$xml .= "<city>" . $pdata['scity'] . "</city>";
		}


		if (isset($pdata['sstate'])) {
			$xml .= "<state>" . $pdata['sstate'] . "</state>";
		}
		else {
			if (isset($pdata['state'])) {
				$xml .= "<state>" . $pdata['sstate'] . "</state>";
			}
		}


		if (isset($pdata['szip'])) {
			$xml .= "<zip>" . $pdata['szip'] . "</zip>";
		}
		else {
			if (isset($pdata['sip'])) {
				$xml .= "<zip>" . $pdata['zip'] . "</zip>";
			}
		}


		if (isset($pdata['scountry'])) {
			$xml .= "<country>" . $pdata['scountry'] . "</country>";
		}


		if (isset($pdata['scarrier'])) {
			$xml .= "<carrier>" . $pdata['scarrier'] . "</carrier>";
		}


		if (isset($pdata['sitems'])) {
			$xml .= "<items>" . $pdata['sitems'] . "</items>";
		}


		if (isset($pdata['sweight'])) {
			$xml .= "<weight>" . $pdata['sweight'] . "</weight>";
		}


		if (isset($pdata['stotal'])) {
			$xml .= "<total>" . $pdata['stotal'] . "</total>";
		}

		$xml .= "</shipping>";
		$xml .= "<transactiondetails>";

		if (isset($pdata['oid'])) {
			$xml .= "<oid>" . $pdata['oid'] . "</oid>";
		}


		if (isset($pdata['ponumber'])) {
			$xml .= "<ponumber>" . $pdata['ponumber'] . "</ponumber>";
		}


		if (isset($pdata['recurring'])) {
			$xml .= "<recurring>" . $pdata['recurring'] . "</recurring>";
		}


		if (isset($pdata['taxexempt'])) {
			$xml .= "<taxexempt>" . $pdata['taxexempt'] . "</taxexempt>";
		}


		if (isset($pdata['terminaltype'])) {
			$xml .= "<terminaltype>" . $pdata['terminaltype'] . "</terminaltype>";
		}


		if (isset($pdata['ip'])) {
			$xml .= "<ip>" . $pdata['ip'] . "</ip>";
		}


		if (isset($pdata['reference_number'])) {
			$xml .= "<reference_number>" . $pdata['reference_number'] . "</reference_number>";
		}


		if (isset($pdata['transactionorigin'])) {
			$xml .= "<transactionorigin>" . $pdata['transactionorigin'] . "</transactionorigin>";
		}


		if (isset($pdata['tdate'])) {
			$xml .= "<tdate>" . $pdata['tdate'] . "</tdate>";
		}

		$xml .= "</transactiondetails>";
		$xml .= "<merchantinfo>";

		if (isset($pdata['configfile'])) {
			$xml .= "<configfile>" . $pdata['configfile'] . "</configfile>";
		}


		if (isset($pdata['keyfile'])) {
			$xml .= "<keyfile>" . $pdata['keyfile'] . "</keyfile>";
		}


		if (isset($pdata['host'])) {
			$xml .= "<host>" . $pdata['host'] . "</host>";
		}


		if (isset($pdata['port'])) {
			$xml .= "<port>" . $pdata['port'] . "</port>";
		}


		if (isset($pdata['appname'])) {
			$xml .= "<appname>" . $pdata['appname'] . "</appname>";
		}

		$xml .= "</merchantinfo>";
		$xml .= "<payment>";

		if (isset($pdata['chargetotal'])) {
			$xml .= "<chargetotal>" . $pdata['chargetotal'] . "</chargetotal>";
		}


		if (isset($pdata['tax'])) {
			$xml .= "<tax>" . $pdata['tax'] . "</tax>";
		}


		if (isset($pdata['vattax'])) {
			$xml .= "<vattax>" . $pdata['vattax'] . "</vattax>";
		}


		if (isset($pdata['shipping'])) {
			$xml .= "<shipping>" . $pdata['shipping'] . "</shipping>";
		}


		if (isset($pdata['subtotal'])) {
			$xml .= "<subtotal>" . $pdata['subtotal'] . "</subtotal>";
		}

		$xml .= "</payment>";

		if (isset($pdata['voidcheck'])) {
			$xml .= "<telecheck><void>1</void></telecheck>";
		}
		else {
			if (isset($pdata['routing'])) {
				$xml .= "<telecheck>";
				$xml .= "<routing>" . $pdata['routing'] . "</routing>";

				if (isset($pdata['account'])) {
					$xml .= "<account>" . $pdata['account'] . "</account>";
				}


				if (isset($pdata['bankname'])) {
					$xml .= "<bankname>" . $pdata['bankname'] . "</bankname>";
				}


				if (isset($pdata['bankstate'])) {
					$xml .= "<bankstate>" . $pdata['bankstate'] . "</bankstate>";
				}


				if (isset($pdata['ssn'])) {
					$xml .= "<ssn>" . $pdata['ssn'] . "</ssn>";
				}


				if (isset($pdata['dl'])) {
					$xml .= "<dl>" . $pdata['dl'] . "</dl>";
				}


				if (isset($pdata['dlstate'])) {
					$xml .= "<dlstate>" . $pdata['dlstate'] . "</dlstate>";
				}


				if (isset($pdata['checknumber'])) {
					$xml .= "<checknumber>" . $pdata['checknumber'] . "</checknumber>";
				}


				if (isset($pdata['accounttype'])) {
					$xml .= "<accounttype>" . $pdata['accounttype'] . "</accounttype>";
				}

				$xml .= "</telecheck>";
			}
		}


		if (isset($pdata['startdate'])) {
			$xml .= "<periodic>";
			$xml .= "<startdate>" . $pdata['startdate'] . "</startdate>";

			if (isset($pdata['installments'])) {
				$xml .= "<installments>" . $pdata['installments'] . "</installments>";
			}


			if (isset($pdata['threshold'])) {
				$xml .= "<threshold>" . $pdata['threshold'] . "</threshold>";
			}


			if (isset($pdata['periodicity'])) {
				$xml .= "<periodicity>" . $pdata['periodicity'] . "</periodicity>";
			}


			if (isset($pdata['pbcomments'])) {
				$xml .= "<comments>" . $pdata['pbcomments'] . "</comments>";
			}


			if (isset($pdata['action'])) {
				$xml .= "<action>" . $pdata['action'] . "</action>";
			}

			$xml .= "</periodic>";
		}


		if (isset($pdata['comments']) || isset($pdata['referred'])) {
			$xml .= "<notes>";

			if (isset($pdata['comments'])) {
				$xml .= "<comments>" . $pdata['comments'] . "</comments>";
			}


			if (isset($pdata['referred'])) {
				$xml .= "<referred>" . $pdata['referred'] . "</referred>";
			}

			$xml .= "</notes>";
		}


		if ($this->debugging) {
			reset($pdata);

			while (list($key, $val) = each($pdata)) {
				if (is_array($val)) {
					$otag = 0;
					$ostag = 0;
					$items_array = $val;
					$xml .= "
<items>
";

					while (list($key1, $val1) = each($items_array)) {
						$xml .= "	<item>
";

						while (list($key2, $val2) = each($val1)) {
							if (!is_array($val2)) {
								$xml .= ("	") . "	<" . $key2 . ">" . $val2 . "</" . $key2 . ">
";
							}
							else {
								if (!$ostag) {
									$xml .= "		<options>
";
									$ostag = 1;
								}

								$xml .= "			<option>
";
								$otag = 1;

								while (list($key3, $val3) = each($val2)) {
									$xml .= ((("	") . "	") . "	") . "	<" . $key3 . ">" . $val3 . "</" . $key3 . ">
";
								}
							}


							if ($otag) {
								$xml .= "			</option>
";
								$otag = 0;
							}
						}


						if ($ostag) {
							$xml .= "		</options>
";
							$ostag = 0;
						}

						$xml .= "	</item>
";
					}

					$xml .= "</items>
";
				}
			}
		}
		else {

			while (list($key, $val) = each($pdata)) {
				if (is_array($val)) {
					$otag = 0;
					$ostag = 0;
					$items_array = $val;
					$xml .= "<items>";

					while (list($key1, $val1) = each($items_array)) {
						$xml .= "<item>";

						while (list($key2, $val2) = each($val1)) {
							if (!is_array($val2)) {
								$xml .= "<" . $key2 . ">" . $val2 . "</" . $key2 . ">";
							}
							else {
								if (!$ostag) {
									$xml .= "<options>";
									$ostag = 1;
								}

								$xml .= "<option>";
								$otag = 1;

								while (list($key3, $val3) = each($val2)) {
									$xml .= "<" . $key3 . ">" . $val3 . "</" . $key3 . ">";
								}
							}


							if ($otag) {
								$xml .= "</option>";
								$otag = 0;
							}
						}


						if ($ostag) {
							$xml .= "</options>";
							$ostag = 0;
						}

						$xml .= "</item>";
					}

					$xml .= "</items>";
				}
			}
		}

		$xml .= "</order>";
		return $xml;
	}
}

?>