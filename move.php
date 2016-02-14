<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `poster`, `title`, `section` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		setPageInfo("Move Thread", $mData['title']);
	}
	else
	{
		die("You have followed an invalid link.");
	}

	if(!$permissions['viewotherthreads'] && $mData['poster'] != $_SESSION['accountid'])
	{
		redirect("errors/permissions.html");
	}

	if(!$permissions['movethreads'] && (!$permissions['moveownthreads'] || $mData['poster'] != $_SESSION['accountid']))
	{
		redirect("errors/permissions.html");
	}

	function listSubSections($sectionID, $parentIndex)
	{
		global $mysql;

		$sectionQuery = $mysql->query("SELECT `id`, `name` FROM `sections` WHERE `parent` = '$sectionID' ORDER BY `order` ASC");

		while($sectionData = $sectionQuery->fetch_assoc())
		{
			echo "<option value='" . $sectionData['id'] . "'>" . getParentIndex($parentIndex) . " " . $sectionData['name'] . "</option>";

			$parentQuery = $mysql->query("SELECT `id` FROM `sections` WHERE `parent` = '" . $sectionData['id'] . "' ORDER BY `order` ASC");

			if($parentQuery->num_rows)
			{
				listSubSections($sectionData['id'], $parentIndex + 1);
			}
		}
	}

	if($_POST['sectionList'])
	{
		if($_POST['sectionList'] == "invalid")
		{
			die("You have followed an invalid link.");
		}

		$mysql->query("UPDATE `threads` SET `section` = '" . escape($_POST['sectionList']) . "' WHERE `id` = '" . escape($_GET['thread']) . "'");

		echo "The thread has been moved";

		redirect("thread?id=" . $_GET['thread'] . "", 2);
	}

	echo "<form id='selectSection' action='?thread=" . $_GET['thread'] . "' method='POST'> <select id='sectionList' name='sectionList' class='sectionList'> <option selected disabled>Select a section...</option>";

	$mQuery = $mysql->query("SELECT * FROM `categories` ORDER BY `order` ASC");

	while($mData = $mQuery->fetch_assoc())
	{
		echo "<option value='invalid' disabled>" . $mData['name'] . "</option>";

		$sectionQuery = $mysql->query("SELECT * FROM `sections` WHERE `category` = '" . $mData['id'] . "' AND `parent` IS NULL ORDER BY `order` ASC");

		while($sectionData = $sectionQuery->fetch_assoc())
		{
			$parentIndex = 1;

			echo "<option value='" . $sectionData['id'] . "'>" . getParentIndex($parentIndex) . " " . $sectionData['name'] . "</option>";

			listSubSections($sectionData['id'], 2);
		}
	}

	echo "</select> </form>";

?>

<script>
	$(document).ready(function()
	{
		$('#sectionList').change(function()
		{
			jConfirm("Are you sure you want to move this thread to:\r\n" + $('#sectionList option:selected').text().trim() + "", "Move Thread", function(isConfirmed)
			{
				if(isConfirmed)
				{
					$('#selectSection').submit();
				}
			});
		});
	});
</script>

<?php

	require_once("includes/footer.php");

?>