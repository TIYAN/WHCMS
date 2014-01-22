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

require "../../../init.php";
$whmcs->load_function( "client" );

if ($CONFIG['SupportModule'] != "kayako") {
	exit( "Kayako Module not Enabled in General Settings > Support" );
}

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$remote_ip = $_REQUEST['ipaddress'];

if (validateClientLogin( $username, $password )) {
	$result = select_query( "tblclients", "", array( "id" => $_SESSION['uid'] ) );
	$data = mysql_fetch_array( $result );
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$email = $data['email'];
	$phonenumber = $data['phonenumber'];
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<loginshare>
    <result>1</result>
    <user>
        <usergroup>Registered</usergroup>
        <fullname><![CDATA[" . $firstname . " " . $lastname . "]]></fullname>
        <emails>
            <email>" . $email . "</email>
        </emails>
        <phone>" . $phonenumber . "</phone>
    </user>
</loginshare>";
}
else {
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<loginshare>
    <result>0</result>
    <message>Invalid Username or Password</message>
</loginshare>";
}

echo $xml;
?>