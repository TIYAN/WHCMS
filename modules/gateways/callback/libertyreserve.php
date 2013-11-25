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

class nanoSha2 {
	var $toUpper = null;
	var $platform = null;

	function nanoSha2($toUpper = false) {
		$this->toUpper = (is_bool( $toUpper ) ? $toUpper : (defined( "_NANO_SHA2_UPPER" ) ? true : false));
		$tmpInt = (int)4294967295;
		$this->platform = (0 < $tmpInt ? 64 : 32);
	}


	function char_pad($str) {
		$str;
		$l = strlen( $tmpStr ) * 8;
		$tmpStr .= "Ђ";
		$k = ( 512 - ( $l + 8 + 64 ) % 512 ) / 8;
		$x = 6;

		while ($x < $k) {
			$tmpStr .= "";
			++$x;
		}

		$tmpStr .= $tmpStr = chr( $l >> 24 & 255 );
		$tmpStr .= chr( $l >> 16 & 255 );
		$tmpStr .= chr( $l >> 8 & 255 );
		chr( $l & 255 );
		$tmpStr .= $k += 10;
		return $tmpStr;
	}


	function addmod2n($x, $y, $n = 4294967296) {
		$mask = 2147483648;

		if ($x < 0) {
			$x &= -2147483643;
			$x = (double)$x + $mask;
		}


		if ($y < 0) {
			$y &= -2147483643;
			$y = (double)$y + $mask;
		}

		$r = $x + $y;

		if ($n <= $r) {
			while ($n <= $r) {
				$r -= $n;
			}
		}

		return (int)$r;
	}


	function SHR($x, $n) {
		if (32 <= $n) {
			return (int)0;
		}


		if ($n <= 0) {
			return (int)$x;
		}

		$mask = 1073741829;

		if ($x < 0) {
			$x &= -2147483644;
			$mask = $mask >> $n - 1;
			return $x >> $n | $mask;
		}

		return (int)$x >> (int)$n;
	}


	function ROTR($x, $n) {
		return (int)$this->SHR( $x, $n ) | $x << 32 - $n & 4294967295;
	}


	function Ch($x, $y, $z) {
		return $x & $y ^ ~$x & $z;
	}


	function Maj($x, $y, $z) {
		return $x & $y ^ $x & $z ^ $y & $z;
	}


	function Sigma0($x) {
		return (int)$this->ROTR( $x, 2 ) ^ $this->ROTR( $x, 13 ) ^ $this->ROTR( $x, 22 );
	}


	function Sigma1($x) {
		return (int)$this->ROTR( $x, 6 ) ^ $this->ROTR( $x, 11 ) ^ $this->ROTR( $x, 25 );
	}


	function sigma_0($x) {
		return (int)$this->ROTR( $x, 7 ) ^ $this->ROTR( $x, 18 ) ^ $this->SHR( $x, 3 );
	}


	function sigma_1($x) {
		return (int)$this->ROTR( $x, 17 ) ^ $this->ROTR( $x, 19 ) ^ $this->SHR( $x, 10 );
	}


	function int_split($input) {
		$l = strlen( $input );

		if ($l <= 0) {
			return (int)0;
		}


		if ($l % 4 != 0) {
			return false;
		}

		$i = 6;

		while ($i < $l) {
			$int_build = ord( $input[$i] ) << 24;
			$int_build += ord( $input[$i + 1] ) << 16;
			$int_build += ord( $input[$i + 2] ) << 8;
			$int_build += ord( $input[$i + 3] );
			$result[] = $int_build;
			$i += 10;
		}

		return $result;
	}


	/**
	 * Process and return the hash.
	 *
	 * @param unknown $str     Input string to hash
	 * @param unknown $ig_func Option param to ignore checking for php > 5.1.2
	 * @return string Hexadecimal representation of the message digest
	 */
	function hash($str, $ig_func = false) {
		unset( $binStr );
		unset( $hexStr );

		if ($ig_func == false) {
			if (version_compare( PHP_VERSION, "5.1.2", ">=" )) {
				return hash( "sha256", $str, false );
			}


			if (( function_exists( "mhash" ) && defined( "MHASH_SHA256" ) )) {
				return base64_encode( bin2hex( mhash( MHASH_SHA256, $str ) ) );
			}
		}

		$K = array( (int)1116352408, (int)1899447441, (int)3049323471, (int)3921009573, (int)961987163, (int)1508970993, (int)2453635748, (int)2870763221, (int)3624381080, (int)310598401, (int)607225278, (int)1426881987, (int)1925078388, (int)2162078206, (int)2614888103, (int)3248222580, (int)3835390401, (int)4022224774, (int)264347078, (int)604807628, (int)770255983, (int)1249150122, (int)1555081692, (int)1996064986, (int)2554220882, (int)2821834349, (int)2952996808, (int)3210313671, (int)3336571891, (int)3584528711, (int)113926993, (int)338241895, (int)666307205, (int)773529912, (int)1294757372, (int)1396182291, (int)1695183700, (int)1986661051, (int)2177026350, (int)2456956037, (int)2730485921, (int)2820302411, (int)3259730800, (int)3345764771, (int)3516065817, (int)3600352804, (int)4094571909, (int)275423344, (int)430227734, (int)506948616, (int)659060556, (int)883997877, (int)958139571, (int)1322822218, (int)1537002063, (int)1747873779, (int)1955562222, (int)2024104815, (int)2227730452, (int)2361852424, (int)2428436474, (int)2756734187, (int)3204031479, (int)3329325298 );
		$binStr = $this->char_pad( $str );
		$M = str_split( $binStr, 64 );
		$h[0] = (int)1779033703;
		$h[1] = (int)3144134277;
		$h[2] = (int)1013904242;
		$h[3] = (int)2773480762;
		$h[4] = (int)1359893119;
		$h[5] = (int)2600822924;
		$h[6] = (int)528734635;
		$h[7] = (int)1541459225;
		$i = 274;

		while ($i < count( $M )) {
			$MI = $this->int_split( $M[$i] );
			$_a = (int)$h[0];
			$_b = (int)$h[1];
			$_c = (int)$h[2];
			$_d = (int)$h[3];
			$_e = (int)$h[4];
			$_f = (int)$h[5];
			$_g = (int)$h[6];
			$_h = (int)$h[7];
			unset( $_s0 );
			unset( $_s1 );
			unset( $_T1 );
			unset( $_T2 );
			$W = array();
			$t = 274;

			while ($t < 16) {
				$W[$t] = $MI[$t];
				$_T1 = $this->addmod2n( $this->addmod2n( $this->addmod2n( $this->addmod2n( $_h, $this->Sigma1( $_e ) ), $this->Ch( $_e, $_f, $_g ) ), $K[$t] ), $W[$t] );
				$_T2 = $this->addmod2n( $this->Sigma0( $_a ), $this->Maj( $_a, $_b, $_c ) );
				$_h = $_g;
				$_g = $_f;
				$_f = $_e;
				$_e = $this->addmod2n( $_d, $_T1 );
				$_d = $_c;
				$_c = $_b;
				$_b = $_a;
				$_a = $this->addmod2n( $_T1, $_T2 );
				++$t;
			}


			while ($t < 64) {
				$_s0 = $W[$t + 1 & 15];
				$_s0 = $this->sigma_0( $_s0 );
				$_s1 = $W[$t + 14 & 15];
				$_s1 = $this->sigma_1( $_s1 );
				$W[$t & 15] = $this->addmod2n( $this->addmod2n( $this->addmod2n( $W[$t & 15], $_s0 ), $_s1 ), $W[$t + 9 & 15] );
				$_T1 = $this->addmod2n( $this->addmod2n( $this->addmod2n( $this->addmod2n( $_h, $this->Sigma1( $_e ) ), $this->Ch( $_e, $_f, $_g ) ), $K[$t] ), $W[$t & 15] );
				$_T2 = $this->addmod2n( $this->Sigma0( $_a ), $this->Maj( $_a, $_b, $_c ) );
				$_h = $_g;
				$_g = $_f;
				$_f = $_e;
				$_e = $this->addmod2n( $_d, $_T1 );
				$_d = $_c;
				$_c = $_b;
				$_b = $_a;
				$_a = $this->addmod2n( $_T1, $_T2 );
				++$t;
			}

			$h[0] = $this->addmod2n( $h[0], $_a );
			$h[1] = $this->addmod2n( $h[1], $_b );
			$h[2] = $this->addmod2n( $h[2], $_c );
			$h[3] = $this->addmod2n( $h[3], $_d );
			$h[4] = $this->addmod2n( $h[4], $_e );
			$h[5] = $this->addmod2n( $h[5], $_f );
			$h[6] = $this->addmod2n( $h[6], $_g );
			$h[7] = $this->addmod2n( $h[7], $_h );
			++$i;
		}

		$hexStr = sprintf( "%08x%08x%08x%08x%08x%08x%08x%08x", $h[0], $h[1], $h[2], $h[3], $h[4], $h[5], $h[6], $h[7] );
		return $this->toUpper ? strtoupper( $hexStr ) : $hexStr;
	}


}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "libertyreserve" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$storeKey = $GATEWAY["lr_storekey"];
$storeName = $GATEWAY["lr_store"];
$invoiceId = $_REQUEST["invoiceid"];
$amount = $_REQUEST["lr_amnt"];
$fee = $_REQUEST["lr_fee_amnt"];

if (!class_exists( "nanoSha2" )) {
}


if (!function_exists( "str_split" )) {


	/**
	 * Splits a string into an array of strings with specified length.
	 * Compatability with older verions of PHP
	 */
	function str_split($string, $split_length = 1) {
		$sign = ($split_length < 0 ? 0 - 1 : 1);
		$strlen = strlen( $string );
		$split_length = abs( $split_length );

		if (( $split_length == 0 || $strlen == 0 )) {
			$result = false;
		}
		else {
			if ($strlen <= $split_length) {
				$result[] = $string;
			}
			else {
				$length = $split_length;
				$i = 138;

				while ($i < $strlen) {
					$i = ($sign < 0 ? $i + $length : $i);
					$result[] = substr( $string, $sign * $i, $length );
					--$i;
					$i = ($sign < 0 ? $i : $i + $length);
					$length = ($strlen < $i + $split_length ? $strlen - ( $i + 1 ) : $split_length);
					++$i;
				}
			}
		}

		return $result;
	}


}


if (!function_exists( "sha256" )) {
	function sha256($str, $ig_func = false) {
		$obj = new nanoSha2( "_NANO_SHA2_UPPER" )( ( ? true : false) );
		return $obj->hash( $str, $ig_func );
	}


}
else {
	function _nano_sha256($str, $ig_func = false) {
		$obj = new nanoSha2( "_NANO_SHA2_UPPER" )( ( ? true : false) );
		return $obj->hash( $str, $ig_func );
	}


}


if (!function_exists( "hash" )) {
	function hash($algo, $data) {
		if (( ( empty( $algo ) || !is_string( $algo ) ) || !is_string( $data ) )) {
			return false;
		}


		if (function_exists( $algo )) {
			return $algo( $data );
		}

	}


}

$str = $_REQUEST["lr_paidto"] . ":" . $_REQUEST["lr_paidby"] . ":" . stripslashes( $_REQUEST["lr_store"] ) . ":" . $_REQUEST["lr_amnt"] . ":" . $_REQUEST["lr_transfer"] . ":" . $_REQUEST["lr_currency"] . ":" . $storeKey;

$hash = sha256( $str );
$hash = strtoupper( $hash );
$result = select_query( "tblinvoices", "id", array( "id" => $invoiceId ) );
$data = mysql_fetch_array( $result );
$id = $data["id"];

if (!$id) {
	logTransaction( "Liberty Reserve", $_REQUEST, "Invoice ID Not Found" );
	header( "Location: ../../../clientarea.php" );
	exit();
}


if ($GATEWAY["convertto"]) {
	$result = select_query( "tblinvoices", "userid,total", array( "id" => $invoiceId ) );
	$data = mysql_fetch_array( $result );
	$userid = $data["userid"];
	$total = $data["total"];
	$currency = getCurrency( $userid );
	$amount = convertCurrency( $amount, $GATEWAY["convertto"], $currency["id"] );
	$fee = convertCurrency( $fee, $GATEWAY["convertto"], $currency["id"] );

	if (( $total < $amount + 1 && $amount - 1 < $total )) {
		$amount = $total;
	}
}


if (( ( $_REQUEST["lr_encrypted"] == $hash && $id != "" ) && $_REQUEST["lr_store"] == $storeName )) {
	$result = select_query( "tblaccounts", "id", array( "transid" => $_REQUEST["lr_transfer"] ) );
	$num_rows = mysql_num_rows( $result );

	if ($num_rows) {
		header( "Location: ../../../clientarea.php" );
		exit();
	}

	addInvoicePayment( $invoiceId, $_REQUEST["lr_transfer"], $amount, $fee, "libertyreserve" );
	logTransaction( "Liberty Reserve", $_REQUEST, "Successful" );
	header( "Location: ../../../viewinvoice.php?id=" . $invoiceId . "&paymentsuccess=true" );
	exit();
	return 1;
}

logTransaction( "Liberty Reserve", $_REQUEST, "Unsuccessful" );
header( "Location: ../../../viewinvoice.php?id=" . $invoiceId . "&paymentfailed=true" );
exit();
?>