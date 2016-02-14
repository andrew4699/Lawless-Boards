<?php

	require_once("configuration/main.php");

	setPageNavigation("index");
	setPageInfo("Lawless Boards", "The new and revolutionary way of creating your forums.");

	if($permissions['viewforum'])
	{
		$mQuery = $mysql->query("SELECT * FROM `categories` ORDER BY `order` ASC");

		while($mData = $mQuery->fetch_assoc())
		{
			echo
			"<div class='categoryTitle'>
				<table>
					<tr>
						<td width='540'>
							<a href='category?id=" . $mData['id'] . "'>
								" . $mData['name'] . "
							</a>
						</td>

						<td width='130'>
							Statistics
						</td>

						<td width='256'>
							Last Post
						</td>

						<td data-category='" . $mData['id'] . "' class='hidden categoryCollapse noselect'>
							
						</td>
					</td>
				</table>
			</div>";

			if($mData['description'])
			{
				echo
				"<div class='categoryDescription'>
					" . $mData['description'] . "
				</div>";
			}

			echo "<div id='CATEGORY-" . $mData['id'] . "'>";

			$sectionQuery = $mysql->query("SELECT * FROM `sections` WHERE `category` = '" . $mData['id'] . "' AND `parent` IS NULL ORDER BY `order` ASC");

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

								<td width='130' class='sectionStats'>
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

			echo "</div> <br>";
		}
	}
	else
	{
		echo "You are not allowed to view categories, sections, or threads.";
	}

	if($permissions['viewonline'])
	{
		if($permissions['viewhidden'])
		{
			$mQuery = $mysql->query("SELECT `id`, `displayname`, `usertitle`, `avatar` FROM `accounts` WHERE UNIX_TIMESTAMP() - `lastactivity` <= '600' ORDER BY `displayname`");
		}
		else
		{
			$mQuery = $mysql->query("SELECT `id`, `displayname`, `usertitle`, `avatar` FROM `accounts` WHERE UNIX_TIMESTAMP() - `lastactivity` <= '600' AND `hidden` != '1' ORDER BY `displayname`");
		}

		$usersLoggedIn = ($mQuery->num_rows == 1) ? "1 user is logged in" : "" . $mQuery->num_rows . " users are logged in";

		echo
		"<div class='box'>
			<div class='boxHeading'>
				Users Online
			</div>

			<div class='boxSubHeading'>
				$usersLoggedIn
			</div>

			<div class='boxMain'>
				<h3>";

				while($mData = $mQuery->fetch_assoc())
				{
					if($firstDone)
					{
						echo ", ";
					}
					else
					{
						$firstDone = true;
					}

					if(!$mData['usertitle'])
					{
						$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['id'] . "' ORDER BY `primary` DESC");

						if($userTitleQuery->num_rows)
						{
							while($userTitleData = $userTitleQuery->fetch_assoc())
							{
								$userGroupQuery = $mysql->query("SELECT `title` FROM `usergroups` WHERE `id` = '" . $userTitleData['usergroup'] . "'");
								$userGroupData = $userGroupQuery->fetch_assoc();

								if($mData['usertitle'] && $userGroupData['title'])
								{
									$mData['usertitle'] .= "<br>";
								}

								$mData['usertitle'] .= $userGroupData['title'];
							}
						}
					}

					echo
					"<a href='user?id=" . $mData['id'] . "' data-avatar='" . $mData['avatar'] . "' data-title='" . $mData['usertitle'] . "' class='onlineUser'>
						" . userNameTags($mData['id'], $mData['displayname']) . "
					</a>";
				}

				echo
				"</h3>
			</div>
		</div>";
	}

?>

<script>
	$(document).ready(function()
	{
		$('.categoryCollapse').show();

		$('.categoryCollapse').click(function()
		{
			if($(this).data("hidden"))
			{
				$(this).data("hidden", 0).text("");
			}
			else
			{
				$(this).data("hidden", 1).text("");
			}

			$('#CATEGORY-' + $(this).data("category") + '').stop().slideToggle(500);
		});

		$('.onlineUser').each(function()
		{
			$(this).hovercard(
			{
				detailsHTML: $(this).data("title"),
				width: 400,
				cardImgSrc: $(this).data("avatar")
			})
		});
	});
</script>

<?php

	require_once("includes/footer.php");

?>