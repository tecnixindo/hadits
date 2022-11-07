<?php
include_once "functions.inc.php";

$row_pingpong = read_db('db/server.txt',1,99);

if ($_POST['hadits_ping'] == '') {
	$data_ping = 'nama_web=Mesin Pencari Hadits&hadits_ping='.$abs_url;


	shuffle($row_pingpong);
	$aksi_pingpong = trim($row_pingpong[0][1])."pingpong.php"; 


// proses ping
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $aksi_pingpong);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; (teTuku v2012) +http://www.tetuku.com");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data_ping);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_ENCODING, "");
	$curlData = curl_exec($curl);
	curl_close($curl);
		
		
		//hapus link menu jika website sudah tidak aktif
		if (!stristr($curlData,'hadits_pong') && $jml_pingpong > 3) {
			$id_pingpong = preg_replace('/[^0-9]/', '',$column_pingpong[0]);
			del_db('db/server.txt',$id_pingpong);	
			}
		//echo $aksi_pingpong;
}

if ($_POST['hadits_ping'] != '') {
$cek_pingpong = serialize($row_pingpong);
$cek_url = access_url($_POST['hadits_ping']."/pingpong.php");
if (!stristr($cek_pingpong,$_POST['hadits_ping']) && stristr($cek_url,'hadits_pong') && !stristr($_POST['hadits_ping'],'localhost')) {$server[1] = $_POST['hadits_ping']; $server[2] = $_POST['nama_web'];  replace_db('db/server.txt',$server,$server[1]);}
}

?>
<!-- hadits_pong -->
