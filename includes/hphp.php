<?php
if(isset($_GET['ad']))
	{  $ckid=$_GET['ad'];
    setcookie("adwords_ad_google", "$ckid", time() + (86400 * 7), "/");
	}
?>