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

function netearthone_GetConfigArray() {
	$vals = resellerclub_GetConfigArray();
	$vals["FriendlyName"]["Value"] = "NetEarthOne";
	unset( $vals["Description"] );
	return $vals;
}


function netearthone_GetNameservers($params) {
	return resellerclub_GetNameservers( $params );
}


function netearthone_SaveNameservers($params) {
	return resellerclub_SaveNameservers( $params );
}


function netearthone_GetRegistrarLock($params) {
	return resellerclub_GetRegistrarLock( $params );
}


function netearthone_SaveRegistrarLock($params) {
	return resellerclub_SaveRegistrarLock( $params );
}


function netearthone_RegisterDomain($params) {
	return resellerclub_RegisterDomain( $params );
}


function netearthone_TransferDomain($params) {
	return resellerclub_TransferDomain( $params );
}


function netearthone_RenewDomain($params) {
	return resellerclub_RenewDomain( $params );
}


function netearthone_GetContactDetails($params) {
	return resellerclub_GetContactDetails( $params );
}


function netearthone_SaveContactDetails($params) {
	return resellerclub_SaveContactDetails( $params );
}


function netearthone_GetEPPCode($params) {
	return resellerclub_GetEPPCode( $params );
}


function netearthone_RegisterNameserver($params) {
	return resellerclub_RegisterNameserver( $params );
}


function netearthone_ModifyNameserver($params) {
	return resellerclub_ModifyNameserver( $params );
}


function netearthone_DeleteNameserver($params) {
	return resellerclub_DeleteNameserver( $params );
}


function netearthone_RequestDelete($params) {
	return resellerclub_RequestDelete( $params );
}


function netearthone_GetDNS($params) {
	return resellerclub_GetDNS( $params );
}


function netearthone_SaveDNS($params) {
	return resellerclub_SaveDNS( $params );
}


function netearthone_GetEmailForwarding($params) {
	return resellerclub_GetEmailForwarding( $params );
}


function netearthone_SaveEmailForwarding($params) {
	return resellerclub_SaveEmailForwarding( $params );
}


function netearthone_ReleaseDomain($params) {
	return resellerclub_ReleaseDomain( $params );
}


function netearthone_IDProtectToggle($params) {
	return resellerclub_IDProtectToggle( $params );
}


function netearthone_Sync($params) {
	return resellerclub_Sync( $params );
}


function netearthone_TransferSync($params) {
	return resellerclub_TransferSync( $params );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}


if (!function_exists( "resellerclub_GetConfigArray" )) {
	require ROOTDIR . "/modules/registrars/resellerclub/resellerclub.php";
}

?>