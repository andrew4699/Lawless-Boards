<?php

	require_once("configuration/main.php");

	if(!isset($_GET['like']) && !isset($_GET['dislike']))
	{
		exit;
	}

	if(isset($_GET['like']) && !$permissions['like'])
	{
		exit;
	}

	if(isset($_GET['dislike']) && !$permissions['dislike'])
	{
		exit;
	}

	if($_GET['thread'])
	{
		$mQuery = $mysql->query("SELECT `like` FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();

			if(isset($_GET['like']) && $mData['like'])
			{
				exit;
			}

			if(isset($_GET['dislike']) && !$mData['like'])
			{
				exit;
			}

			$mysql->query("DELETE FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");
		}

		$doAction = (isset($_GET['like']));

		$mysql->query("INSERT INTO `likes` (`thread`, `post`, `like`, `user`) VALUES ('1', '" . escape($_GET['thread']) . "', '$doAction', '" . $_SESSION['accountid'] . "')");

		redirect("thread?id=" . $_GET['thread'] . ""); 
	}
	else if($_GET['comment'])
	{
		$commentQuery = $mysql->query("SELECT `thread` FROM `comments` WHERE `id` = '" . escape($_GET['comment']) . "'");
		$commentData = $commentQuery->fetch_assoc();

		$mQuery = $mysql->query("SELECT `like` FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();

			if(isset($_GET['like']) && $mData['like'])
			{
				exit;
			}

			if(isset($_GET['dislike']) && !$mData['like'])
			{
				exit;
			}

			$mysql->query("DELETE FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");
		}

		$doAction = (isset($_GET['like']));

		$mysql->query("INSERT INTO `likes` (`thread`, `post`, `like`, `user`) VALUES ('0', '" . escape($_GET['comment']) . "', '$doAction', '" . $_SESSION['accountid'] . "')");

		redirect("thread?id=" . $commentData['thread'] . "#COMMENT-" . $_GET['comment'] . ""); 
	}
	else
	{
		die("You have followed an invalid link.");
	}

?>

<?php

	require_once("includes/footer.php");

?>