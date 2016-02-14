<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `title`, `section` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		setPageInfo("Rate Thread", $mData['title']);
	}
	else
	{
		die("You have followed an invalid link.");
	}

	if(!$permissions['ratethreads'])
	{
		redirect("errors/permissions.html");
	}

	$rateQuery = $mysql->query("SELECT `id` FROM `rating` WHERE `thread` = '" . escape($_GET['thread']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");

	if($rateQuery->num_rows)
	{
		die("You have already rated this thread.");
	}

	if($_GET['rating'] != 1 && $_GET['rating'] != 2 && $_GET['rating'] != 3 && $_GET['rating'] != 4 && $_GET['rating'] != 5)
	{
		die("You have followed an invalid link.");
	}

	$mysql->query("INSERT INTO `rating` (`thread`, `rating`, `user`) VALUES ('" . escape($_GET['thread']) . "', '" . escape($_GET['rating']) . "', '" . $_SESSION['accountid'] . "')");

	redirect("section?id=" . $mData['section'] . "");

?>

<?php

	require_once("includes/footer.php");

?>