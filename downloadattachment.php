<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if(!$permissions['downloadattachments'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `thread`, `post`, `path` FROM `attachments` WHERE `id` = '" . escape($_GET['id']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		if($mData['thread'])
		{
			$attachmentThread = $mData['post'];
		}
		else
		{
			$commentQuery = $mysql->query("SELECT `thread` FROM `comments` WHERE `id` = '" . escape($mData['post']) . "'");
			$commentData = $commentQuery->fetch_assoc();

			$attachmentThread = $commentData['thread'];
		}

		header("Pragmaes: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: private", false);
	    header("Content-type: application/force-download");
	    header("Content-Transfer-Encoding: Binary");
	    header("Content-length: " . filesize($mData['path']));
	    header("Content-disposition: attachment; filename=\"" . basename($mData['path']) . "\"");

	    readfile($mData['path']);
	}
	else
	{
		redirect("errors/permissions.html");
	}

?>

<?php

	require_once("includes/footer.php");

?>