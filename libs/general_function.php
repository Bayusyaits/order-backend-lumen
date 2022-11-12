<?php

function timeId()
{
	date_default_timezone_set('Asia/Jakarta');
	return time();
}

function sanitizePostXss($input)
{

	$data  = isset($input) ? $input : $_POST;
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = nestedXssFilter($value);
		} else {
			$data[$key] = cleanXss($value);
		}
	}

	if (!isset($input)) {
		$_POST = $data;
	}

	$input = $data;
	return $data;
}

function nestedXssFilter(&$data = [])
{
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = nestedXssFilter($data[$key]);
		} else {
			$data[$key] = cleanXss($value);
		}
	}

	return $data;
}

function cleanXss($string)
{
	if (!requestHasInvalidJSON($string)) {
		return $string;
	}
	$string = preg_replace('/(<[a-zA-Z]+)|(<\/[a-zA-Z]+>)|>/', '', $string);
	return htmlspecialchars(strip_tags($string));
}

function getRequestHeaders()
{
	$headers = array();
	foreach ($_SERVER as $key => $value) {
		if (substr($key, 0, 5) <> 'HTTP_') {
			continue;
		}
		$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
		$headers[$header] = $value;
	}
	return $headers;
}

function getClientIp()
{
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if (isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = '0';
	return $ipaddress;
}

function requestHasInvalidJSON($data)
{
	if ($data && is_array($data)) {
		return true;
	} else if (!$data || empty($data)) {
		return true;
	}
	json_decode($data);
	return json_last_error() !== JSON_ERROR_NONE;
}

function generateToken($length = null)
{
	if (empty($length)) {
		$l = 20;
	} else {
		$l = $length;
	}
	$buf = '';
	for ($i = 0; $i < $l; ++$i) {
		$buf .= chr(mt_rand(0, 255));
	}
	return bin2hex($buf);
}

function generateRandomNumeric($length = null)
{
	if (empty($length)) {
		$length = 10;
	} else {
		$length = $length;
	}
	$numeric = '0123456789';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $numeric[rand(0, strlen($numeric) - 1)];
	}
	return $randomString;
}
function generateRandomChar($length = null)
{
	if (empty($length)) {
		$length = 10;
	} else {
		$length = $length;
	}
	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

function generateRandomCode($length = null)
{
	if (empty($length)) {
		$length = 6;
	} else {
		$length = $length;
	}
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}
function setHTTPResponse($response) {
	$bool = env('RESPONSE_HTTP', false);
	if (!$bool || empty($response)) {
		$response = 200;
	}
	return $response;
}

function controller($module = null, $class = '', $object = true)
{
	$src = "\App\Http\Controllers\\$class";
	if (!empty($module) && $module) {
		$src = "\Modules\\$module\Http\Controllers\\$class";
	}
	if ($object) {
		return new $src(true);
	}
	return $src;
}

function entity($module = '', $class = '')
{
	$src = "\app\Http\Entities\\$class";
	if (!empty($module)) {
		$src = "\Modules\\$module\Entities\\$class";
	}

	if (!class_exists($src)) {
		return 'not found';
	}

	return $src;
}

function service($module = '', $class = '')
{
	$src = "\app\Http\Services\\$class";
	if (!empty($module)) {
		$src = "\Modules\\$module\Services\\$class";
	}

	if (!class_exists($src)) {
		return 'not found';
	}

	return $src;
}
