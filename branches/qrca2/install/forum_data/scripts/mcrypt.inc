<?php
function decode_strings($strs)
{
	$key = base64_decode(file_get_contents("/home/qrca/.key/crypt_key"));

	$td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, '');
        $iv = str_repeat("\0", mcrypt_enc_get_iv_size($td));
	$ret = array();

	foreach ($strs as $str) {
		$ret[] = decrypt_string($str, $key, $td, $iv);
	}

	mcrypt_module_close($td);

	return $ret;
}
       
function decrypt_string($str, $key, &$td, $iv)
{
        mcrypt_generic_init($td, $key, $iv);
        $dec = trim(mdecrypt_generic($td, base64_decode($str)), "\x00..\x1F");
        mcrypt_generic_deinit($td);

        return $dec;
}
?>