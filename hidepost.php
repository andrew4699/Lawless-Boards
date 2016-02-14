<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if($_GET['thread'])
	{
		$mQuery = $mysql->query("SELECT `section`, `poster`, `hidden` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['hidepost'] && (!$permissions['hideownthreads'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		$hidden = ($mData['hidden']) ? 0 : 1;

		$mysql->query("UPDATE `threads` SET `hidden` = '$hidden' WHERE `id` = '" . escape($_GET['thread']) . "'");

		redirect("section?id=" . $mData['section'] . "");
	}
	else if($_GET['comment'])
	{
		$mQuery = $mysql->query("SELECT `thread`, `poster`, `hidden` FROM `comments` WHERE `id` = '" . escape($_GET['comment']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['hidepost'] && (!$permissions['hideownposts'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		$hidden = ($mData['hidden']) ? 0 : 1;

		$mysql->query("UPDATE `comments` SET `hidden` = '$hidden' WHERE `id` = '" . escape($_GET['comment']) . "'");

		redirect("thread?id=" . $mData['thread'] . "");
	}
	else
	{
		die("You have followed an invalid link.");
	}

?>

<?php

	require_once("includes/footer.php");

?>