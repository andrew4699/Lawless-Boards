<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `poster`, `locked` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();
	}
	else
	{
		die("You have followed an invalid link.");
	}

	if(!$permissions['viewotherthreads'] && $mData['poster'] != $_SESSION['accountid'])
	{
		redirect("errors/permissions.html");
	}

	if(!$permissions['lockthreads'] && (!$permissions['lockownthreads'] || $mData['poster'] != $_SESSION['accountid']))
	{
		redirect("errors/permissions.html");
	}

	if($mData['locked'])
	{
		$mysql->query("UPDATE `threads` SET `locked` = '0' WHERE `id` = '" . escape($_GET['thread']) . "'");
	}
	else
	{
		$mysql->query("UPDATE `threads` SET `locked` = '1' WHERE `id` = '" . escape($_GET['thread']) . "'");
	}

	redirect("thread?id=" . $_GET['thread'] . "");

?>

<?php

	require_once("includes/footer.php");

?>