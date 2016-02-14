<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT * FROM `categories` WHERE `id` = '" . escape($_GET['id']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();
		setPageNavigation("category", $_GET['id']);
		setPageInfo($mData['name'], $mData['description']);
	}
	else
	{
		die("You have followed an invalid link.");
	}

	echo
	"<div class='categoryTitle'>
		<table>
			<tr>
				<td width='540'>
					" . $mData['name'] . "
				</td>

				<td width='100'>
					Statistics
				</td>

				<td>
					Last Post
				</td>
			</tr>
		</table>
	</div>";

	if($mData['description'])
	{
		echo
		"<div class='categoryDescription'>
			" . $mData['description'] . "
		</div>";
	}

	$sectionQuery = $mysql->query("SELECT * FROM `sections` WHERE `category` = '" . escape($_GET['id']) . "' AND `parent` IS NULL ORDER BY `order` ASC");

	while($sectionData = $sectionQuery->fetch_assoc())
	{
		$threadAmount = 0;
		$commentAmount = 0;

		if($permissions['viewotherthreads'])
		{
			if($permissions['viewhiddenthreads'])
			{
				$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $sectionData['id'] . "'");
			}
			else
			{
				$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $sectionData['id'] . "' AND `hidden` != '1'");
			}
		}
		else
		{
			if($permissions['viewhiddenthreads'])
			{
				$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $sectionData['id'] . "' AND `poster` = '" . $_SESSION['accountid'] . "'");
			}
			else
			{
				$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $sectionData['id'] . "' AND `poster` = '" . $_SESSION['accountid'] . "' AND `hidden` != '1'");
			}
		}

		while($threadData = $threadQuery->fetch_assoc())
		{
			$threadAmount++;

			$commentQuery = $mysql->query("SELECT `id` FROM `comments` WHERE `thread` = '" . $threadData['id'] . "'");

			$commentAmount += $commentQuery->num_rows;
		}

		$threadQuery = $mysql->query("SELECT `id`, `poster`, `lastpost`, `title` FROM `threads` WHERE `section` = '" . $sectionData['id'] . "' ORDER BY `lastpost` DESC LIMIT 1");
		$threadData = $threadQuery->fetch_assoc();

		$commentQuery = $mysql->query("SELECT `poster` FROM `comments` WHERE `thread` = '" . $threadData['id'] . "' AND `date` = '" . $threadData['lastpost'] . "'");

		if($commentQuery->num_rows)
		{
			$commentData = $commentQuery->fetch_assoc();
		}
		else
		{
			$commentData['poster'] = $threadData['poster'];
		}

		$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $commentData['poster'] . "'");
		$accountData = $accountQuery->fetch_assoc();

		echo
		"<a href='section?id=" . $sectionData['id'] . "'>
			<div class='sectionContainer'>
				<table>
					<tr>
						<td>
							<div class='sectionIcon'>
								
							</div>
						</td>

						<td width='500'>
							<div class='sectionName'>
								" . $sectionData['name'] . "
							</div>

							<div class='sectionDescription'>
								" . $sectionData['description'] . "
							</div>
						</td>

						<td width='100' class='sectionStats'>
							Comments: $commentAmount<br>
							Threads: $threadAmount
						</td>

						<td class='sectionStats'>";
							
							if($threadQuery->num_rows)
							{
								echo 
								"<a href='thread?id=" . $threadData['id'] . "' class='sectionUser'>
									" . $threadData['title'] . "
								</a>

								<br>

								<div class='sectionDate'>
									" . customDate($threadData['lastpost']) . "

									by

									<a href='user?id=" . $commentData['poster'] . "'>
										" . userNameTags($commentData['poster'], $accountData['displayname']) . "
									</a>
								</div>";
							}
							else
							{
								echo "Nothing to display";
							}

						echo	
						"</td>
					</tr>
				</table>
			</div>
		</a>";
	}

?>

<?php

	require_once("includes/footer.php");

?>