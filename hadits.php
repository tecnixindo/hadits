<?php
//Source code: https://github.com/tecnixindo/

include_once "functions.inc.php";
if ($_GET['q'] == '') {
?>
<form action="" method="get">
	<input name="q" type="text" size="22"> <input name="submit" type="submit" id="submit">
</form>
<!-- https://github.com/tecnixindo/hadits -->
<?php
} 
if ($_GET['q'] != '') {
	$list_hadits = file_list('db');
	foreach($list_hadits as $hadits_db) {
		$row_hadits = search_db('db/'.$hadits_db,$_GET['q']);
		$nama_hadits = ucwords(str_replace('_',' ',in_string('','.txt',$hadits_db)));
		foreach($row_hadits as $column_hadits) {
?>
		<strong>[<?=$nama_hadits?> <?=$column_hadits[1]?>]</strong><br>
        <?=$column_hadits[2]?><br><br>
<?php	
		}
	}
}
?>