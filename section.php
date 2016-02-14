<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `name`, `description` FROM `sections` WHERE `id` = '" . escape($_GET['id']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();
		setPageNavigation("section", $_GET['id']);
		setPageInfo($mData['name'], $mData['description']);
	}
	else
	{
		die("You have followed an invalid link.");
	}

	$mQuery = $mysql->query("SELECT * FROM `sections` WHERE `parent` = '" . escape($_GET['id']) . "' ORDER BY `order` ASC");

	if($mQuery->num_rows)
	{
		echo
		"<div class='categoryTitle'>
			<table>
				<tr>
					<td width='540'>
						Sub-Sections
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

		while($mData = $mQuery->fetch_assoc())
		{
			$threadAmount = 0;
			$commentAmount = 0;

			if($permissions['viewotherthreads'])
			{
				if($permissions['viewhiddenthreads'])
				{
					$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $mData['id'] . "'");
				}
				else
				{
					$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $mData['id'] . "' AND `hidden` != '1'");
				}
			}
			else
			{
				if($permissions['viewhiddenthreads'])
				{
					$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $mData['id'] . "' AND `poster` = '" . $_SESSION['accountid'] . "'");
				}
				else
				{
					$threadQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `section` = '" . $mData['id'] . "' AND `poster` = '" . $_SESSION['accountid'] . "' AND `hidden` != '1'");
				}
			}

			while($threadData = $threadQuery->fetch_assoc())
			{
				$threadAmount++;

				$commentQuery = $mysql->query("SELECT `id` FROM `comments` WHERE `thread` = '" . $threadData['id'] . "'");

				$commentAmount += $commentQuery->num_rows;
			}

			$threadQuery = $mysql->query("SELECT `id`, `poster`, `lastpost`, `title` FROM `threads` WHERE `section` = '" . $mData['id'] . "' ORDER BY `lastpost` DESC LIMIT 1");
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
			"<a href='section?id=" . $mData['id'] . "'>
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
									" . $mData['name'] . "
								</div>

								<div class='sectionDescription'>
									" . $mData['description'] . "
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

		echo "<br>";
	}

	echo
	"<div class='categoryTitle'>
		<table>
			<tr>
				<td width='540'>
					Thread Title
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

	if($permissions['viewotherthreads'])
	{
		if($permissions['viewhiddenthreads'])
		{
			$mQuery = $mysql->query("SELECT * FROM `threads` WHERE `section` = '" . escape($_GET['id']) . "' ORDER BY `lastpost` DESC");
		}
		else
		{
			$mQuery = $mysql->query("SELECT * FROM `threads` WHERE `section` = '" . escape($_GET['id']) . "' AND `hidden` != '1' ORDER BY `lastpost` DESC");
		}
	}
	else
	{
		if($permissions['viewhiddenthreads'])
		{
			$mQuery = $mysql->query("SELECT * FROM `threads` WHERE `section` = '" . escape($_GET['id']) . "' AND `poster` = '" . $_SESSION['accountid'] . "' ORDER BY `lastpost` DESC");
		}
		else
		{
			$mQuery = $mysql->query("SELECT * FROM `threads` WHERE `section` = '" . escape($_GET['id']) . "' AND `poster` = '" . $_SESSION['accountid'] . "' AND `hidden` != '1' ORDER BY `lastpost` DESC");
		}
	}

	if($mQuery->num_rows)
	{
		while($mData = $mQuery->fetch_assoc())
		{
			$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");

			if($accountQuery->num_rows)
			{
				$accountData = $accountQuery->fetch_assoc();
				$threadPoster = $accountData['displayname'];
			}
			else
			{
				$threadPoster = "Guest";
			}

			$sectionIcon = ($mData['locked']) ? "" : "";

			if($mData['hidden'])
			{
				$sectionIcon = "";
			}

			$commentQuery = $mysql->query("SELECT `id` FROM `comments` WHERE `thread` = '" . $mData['id'] . "'");
			$commentAmount = $commentQuery->num_rows;

			if($commentAmount)
			{
				$commentQuery = $mysql->query("SELECT `poster` FROM `comments` WHERE `thread` = '" . $mData['id'] . "' AND `date` = '" . $mData['lastpost'] . "'");
				$commentData = $commentQuery->fetch_assoc();

				$commentPosterQuery = $mysql->query("SELECT `id`, `displayname` FROM `accounts` WHERE `id` = '" . $commentData['poster'] . "'");
			}
			else
			{
				$commentPosterQuery = $mysql->query("SELECT `id`, `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
			}

			$commentPosterData = $commentPosterQuery->fetch_assoc();

			$ratingQuery = $mysql->query("SELECT `rating` FROM `rating` WHERE `thread` = '" . $mData['id'] . "'");

			while($ratingData = $ratingQuery->fetch_assoc())
			{
				$totalRating += $ratingData['rating'];
			}

			$totalRating = round($totalRating / $ratingQuery->num_rows);

			$hasRatedQuery = $mysql->query("SELECT `id` FROm `rating` WHERE `thread` = '" . $mData['id'] . "' AND `user` = '" . $_SESSION['accountid'] . "'");

			echo
			"<a href='thread?id=" . $mData['id'] . "'>
				<div class='sectionContainer'>
					<table>
						<tr>
							<td>
								<div class='sectionIcon'>
									$sectionIcon
								</div>
							</td>

							<td width='500'>
								<div class='sectionName'>
									" . $mData['title'] . "
								</div>

								<div class='sectionDescription'>
									Started by $threadPoster - " . customDate($mData['date']) . "
								</div>
							</td>

							<td width='100' class='sectionStats'>
								Replies: $commentAmount<br>
								Views: " . $mData['views'] . "<br>";

								if($permissions['ratethreads'] && !$hasRatedQuery->num_rows)
								{
									for($ratingIndex = 1; $ratingIndex <= $totalRating; $ratingIndex++)
									{
										echo
										"<a href='rate?thread=" . $mData['id'] . "&rating=$ratingIndex'>
											<span data-tooltip='Thread Rating: " . $totalRating . "/5' class='sectionRatingFill'></span>
										</a>";
									}

									for($ratingIndex = $totalRating + 1; $ratingIndex <= 5; $ratingIndex++)
									{
										echo
										"<a href='rate?thread=" . $mData['id'] . "&rating=$ratingIndex'>
											<span data-tooltip='Thread Rating: " . $totalRating . "/5' class='sectionRatingEmpty'></span>
										</a>";
									}
								}
								else
								{
									for($ratingIndex = 1; $ratingIndex <= $totalRating; $ratingIndex++)
									{
										echo "<span data-tooltip='Thread Rating: " . $totalRating . "/5' class='sectionRatingFill'></span>";
									}

									for($ratingIndex = $totalRating + 1; $ratingIndex <= 5; $ratingIndex++)
									{
										echo "<span data-tooltip='Thread Rating: " . $totalRating . "/5' class='sectionRatingEmpty'></span>";
									}
								}

							echo	
							"</td>

							<td class='sectionStats'>
								<a href='user?id=" . $commentPosterData['id'] . "' class='sectionUser'>
									" . userNameTags($commentPosterData['id'], $commentPosterData['displayname']) . "
								</a>

								<br>

								<div class='sectionDate'>
									" . customDate($mData['lastpost']) . "
								</div>
							</td>
						</tr>
					</table>
				</div>
			</a>";
		}
	}
	else
	{
		echo
		"<div class='sectionContainer'>
			There are no threads to display in this section.
		</div>";
	}

	if($permissions['postthreads'])
	{
		echo
		"<div align='right'>
			<a href='newthread?section=" . $_GET['id'] . "'>
				<button class='sectionNewThread icon'> &nbsp;Create New Thread</button>
			</a>
		</div>";
	}

?>

<?php

	require_once("includes/footer.php");

?>