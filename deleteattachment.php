<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if(!$permissions['deleteattachment'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `path` FROM `attachments` WHERE `id` = '" . escape($_GET['id']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();
	}
	else
	{
		die("You have followed an invalid link.");
	}

	unlink($mData['path']);

	$mysql->query("DELETE FROM `attachments` WHERE `id` = '" . escape($_GET['id']) . "'");

	redirect("index");

?>

<?php

	require_once("includes/footer.php");

?>