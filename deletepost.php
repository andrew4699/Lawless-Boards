<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if($_GET['thread'])
	{
		$mQuery = $mysql->query("SELECT `section`, `poster` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['deletepost'] && (!$permissions['deleteownthreads'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		$mysql->query("DELETE FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

		$commentsQuery = $mysql->query("SELECT `id` FROM `comments` WHERE `thread` = '" . escape($_GET['thread']) . "'");

		while($commentsData = $commentsQuery->fetch_assoc())
		{
			$attachmentsQuery = $mysql->query("SELECT `path` FROM `attachments` WHERE `thread` = '0' AND `post` = '" . escape($commentsData['id']) . "'");

			if($attachmentsQuery->num_rows)
			{
				while($attachmentsData = $attachmentsQuery->fetch_assoc())
				{
					$splitPath = explode("/", $attachmentsData['path']);print_r($splitPath);die("");

					removeDirectory("attachments/" . $splitPath[1] . "");
				}
			}
		}

		$mysql->query("DELETE FROM `comments` WHERE `thread` = '" . escape($_GET['thread']) . "'");
		$mysql->query("DELETE FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "'");
		$mysql->query("DELETE FROM `read` WHERE `thread` = '" . escape($_GET['thread']) . "'");
		$mysql->query("DELETE FROM `rating` WHERE `thread` = '" . escape($_GET['thread']) . "'");

		$attachmentsQuery = $mysql->query("SELECT `path` FROM `attachments` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "' LIMIT 1");

		if($attachmentsQuery->num_rows)
		{
			while($attachmentsData = $attachmentsQuery->fetch_assoc())
			{
				$splitPath = explode("/", $attachmentsData['path']);

				removeDirectory("attachments/" . $splitPath[1] . "");
			}
		}

		$mysql->query("DELETE FROM `attachments` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "'");

		redirect("section?id=" . $mData['section'] . "");
	}
	else if($_GET['comment'])
	{
		$mQuery = $mysql->query("SELECT `thread`, `poster` FROM `comments` WHERE `id` = '" . escape($_GET['comment']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['deletepost'] && (!$permissions['deleteownposts'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		$mysql->query("DELETE FROM `comments` WHERE `id` = '" . escape($_GET['comment']) . "'");
		$mysql->query("DELETE FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "'");

		$attachmentsQuery = $mysql->query("SELECT `path` FROM `attachments` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "' LIMIT 1");

		if($attachmentsQuery->num_rows)
		{
			while($attachmentsData = $attachmentsQuery->fetch_assoc())
			{
				$splitPath = explode("/", $attachmentsData['path']);

				removeDirectory("attachments/" . $splitPath[1] . "");
			}
		}

		$mysql->query("DELETE FROM `attachments` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "'");

		$commentQuery = $mysql->query("SELECT `date` FROM `comments` WHERE `thread` = '" . $mData['thread'] . "' ORDER BY `date` DESC LIMIT 1");

		if($commentQuery->num_rows)
		{
			$commentData = $commentQuery->fetch_assoc();
			$mysql->query("UPDATE `threads` SET `lastpost` = '" . $commentData['date'] . "' WHERE `id` = '" . $mData['thread'] . "'");
		}
		else
		{
			$threadQuery = $mysql->query("UPDATE `threads` SET `lastpost` = `date` WHERE `id` = '" . $mData['thread'] . "'");
		}

		redirect("thread?id=" . $mData['thread'] . "");
	}
	else if($_GET['profilemessage'])
	{
		$mQuery = $mysql->query("SELECT `user`, `poster` FROM `profilemessages` WHERE `id` = '" . escape($_GET['profilemessage']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['deletepost'] && (!$permissions['deleteownprofilemessage'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		$mysql->query("DELETE FROM `profilemessages` WHERE `id` = '" . escape($_GET['profilemessage']) . "'");

		redirect("user?id=" . $mData['user'] . "");
	}
	else
	{
		die("You have followed an invalid link.");
	}

?>

<?php

	require_once("includes/footer.php");

?>