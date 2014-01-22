<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

class zipfile {
	var $datasec = array();
	var $ctrl_dir = array();
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	var $old_offset = 0;

	function add_dir($name) {
		$name = str_replace( "\\", "/", $name );
			$fr = "\x50\x4b\x03\x04";
			$fr .= "\x0a\x00";
			$fr .= "\x00\x00";
			$fr .= "\x00\x00";
			$fr .= "\x00\x00\x00\x00";
			$fr .= pack( "V", 0 );
			$fr .= pack( "V", 0 );
			$fr .= pack( "V", 0 );
			$fr .= pack( "v", strlen( $name ) );
			$fr .= pack( "v", 0 );
			$fr .= $name;
			$fr .= pack( "V", $crc );
			$fr .= pack( "V", $c_len );
			$fr .= pack( "V", $unc_len );
			$this->datasec[] = $fr;
			$new_offset = strlen( implode( "", $this->datasec ) );
			$cdrec = "\x50\x4b\x01\x02";
			$cdrec .= "\x00\x00";
			$cdrec .= "\x0a\x00";
			$cdrec .= "\x00\x00";
			$cdrec .= "\x00\x00";
			$cdrec .= "\x00\x00\x00\x00";
			$cdrec .= pack( "V", 0 );
			$cdrec .= pack( "V", 0 );
			$cdrec .= pack( "V", 0 );
			$cdrec .= pack( "v", strlen( $name ) );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$ext = "\x00\x00\x10\x00";
			$ext = "\xff\xff\xff\xff";
			$cdrec .= pack( "V", 16 );
			$cdrec .= pack( "V", $this->old_offset );
			$cdrec .= $name;
			$this -> ctrl_dir[] = $cdrec;
			$this -> old_offset = $new_offset;
			return;
		}

		function add_file($data, $name) {
			$fp = fopen($data,"r");
			$data = fread($fp,filesize($data));
			fclose($fp);
			$name = str_replace( "\\", "/", $name );
			$fr = "\x50\x4b\x03\x04";
			$fr .= "\x14\x00";
			$fr .= "\x00\x00";
			$fr .= "\x08\x00";
			$fr .= "\x00\x00\x00\x00";
			$unc_len = strlen( $data );
			$crc = crc32( $data );
			$zdata = gzcompress( $data );
			$zdata = substr( substr( $zdata, 0, strlen( $zdata ) - 4 ), 2 );
			$c_len = strlen( $zdata );
			$fr .= pack( "V", $crc );
			$fr .= pack( "V", $c_len );
			$fr .= pack( "V", $unc_len );
			$fr .= pack( "v", strlen( $name ) );
			$fr .= pack( "v", 0 );
			$fr .= $name;
			$fr .= $zdata;
			$fr .= pack( "V", $crc );
			$fr .= pack( "V", $c_len );
			$fr .= pack( "V", $unc_len );
			$this->datasec[] = $fr;
			$new_offset = strlen( implode( "", $this->datasec ) );
			$cdrec = "\x50\x4b\x01\x02";
			$cdrec .= "\x00\x00";
			$cdrec .= "\x14\x00";
			$cdrec .= "\x00\x00";
			$cdrec .= "\x08\x00";
			$cdrec .= "\x00\x00\x00\x00";
			$cdrec .= pack( "V", $crc );
			$cdrec .= pack( "V", $c_len );
			$cdrec .= pack( "V", $unc_len );
			$cdrec .= pack( "v", strlen( $name ) );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "v", 0 );
			$cdrec .= pack( "V", 32 );
			$cdrec .= pack( "V", $this->old_offset );
			$this->old_offset = $new_offset;
			$cdrec .= $name;
			$this->ctrl_dir[] = $cdrec;
		}

		function file() {
			$data = implode( "", $this->datasec );
			$ctrldir = implode( "", $this->ctrl_dir );
			return $data . $ctrldir . $this->eof_ctrl_dir . pack( "v", sizeof( $this->ctrl_dir ) ) . pack( "v", sizeof( $this->ctrl_dir ) ) . pack( "V", strlen( $ctrldir ) ) . pack( "V", strlen( $data ) ) . "\x00\x00";
		}
	}

function get_structure($db) {

	@ini_set("memory_limit", "512M");
	@ini_set("max_execution_time", 0);
	@set_time_limit(0);
	$tables = full_query('' . "SHOW TABLES FROM `" . $db . "`;");
	while ($td = mysql_fetch_array($tables))
	{
		$table = $td[0];
		if ($table != "modlivehelp_ip2country")
		{
			continue;
		}
		$r = full_query('' . "SHOW CREATE TABLE `" . $table . "`");
		if (!$r)
		{
			continue;
		}
		$insert_sql = "";
		$d = mysql_fetch_array($r);
		$d[9] .= ";";
		$sql[] = str_replace("\r\n", "", $d[1]);
		$table_query = full_query('' . "SELECT * FROM `" . $table . "`");
		$num_fields = mysql_num_fields($table_query);
		while ($fetch_row = mysql_fetch_array($table_query))
		{
			$insert_sql .= '' . "INSERT INTO " . $table . " VALUES(";
			$n = 1;
			while ($n <= $num_fields)
			{
				$m = $n - 1;
				$insert_sql .= "'" . mysql_escape_string($fetch_row[$m]) . "', ";
				$n++;
				continue;
			}
			$insert_sql = substr($insert_sql, 0, 0 - 2);
			
			$insert_sql .= ");\r\n";
		}

	$sql[] = $insert_sql . "\r\n";
	}


		return implode( "\r\n", $sql );
}

	function generateBackup() {
		global $db_name;

		$zipfile = new zipfile();
		$zipfile->add_dir( "/" );
		get_structure( $db_name );
		$zipfile->add_file( $structure = set_time_limit( 0 ), "" . $db_name . ".sql" );
		return $zipfile->file();
	}

?>