<?php
//Source code: https://github.com/tecnixindo/

include_once "functions.inc.php";
if ($_GET['q'] == '') {
?>
<form action="" method="get">
	<input name="q" type="text" size="22" placeholder="cari hadits"> <input name="submit" type="submit" id="submit">
</form>
<p><!-- https://github.com/tecnixindo/hadits -->
</p>
<p><h3>Paralel server [<a href="hadits.zip">download script hadits</a>]</h3>
<?php
	$row_server = read_db('db/server.txt',1,99);
	foreach ($row_server as $column_server) {
?>
 <a href="<?=$column_server[1]?>"><?=$column_server[1]?></a> &nbsp; 
<?php
	}
?>
</p>
<?php
} 
if ($_GET['q'] != '') {
	$list_hadits = file_list('db');
	foreach($list_hadits as $hadits_db) {
		$row_hadits = search_db('db/'.$hadits_db,$_GET['q']);
		$nama_hadits = ucwords(str_replace('_',' ',in_string('','.txt',$hadits_db)));
		foreach($row_hadits as $column_hadits) {
?>
  <strong>[
  <?=$nama_hadits?> <?=$column_hadits[1]?>]</strong><br>
  <?=$column_hadits[2]?><br><br>
  <?php	
		if (strlen($column_hadits[1].$column_hadits[2]) >= 11 && strlen($hasil <= 0)) {$hasil = "ada";}
		}
	}
if ($hasil != 'ada'){echo "Tak ditemukan..! Ganti atau kurangi kata kunci";}
}

//echo "<hr>".memory_get_peak_usage(1);
include_once "pingpong.php";

if (!file_exists('hadits.zip')) {
	zip_file('hadits.zip');
}
 
?>