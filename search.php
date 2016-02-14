<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'] || !$permissions['search'])
	{
		redirect("errors/permissions.html");
	}

	setPageInfo("Search", "");

	if($_POST['search'])
	{
		echo
		"<div class='categoryTitle'>
			<table>
				<tr>
					<td width='540'>
						Search Results
					</td>

					<td>
						Type
					</td>
				</tr>
			</table>
		</div>";

		if($_POST['threadtitles'] == "on")
		{
			$mQuery = $mysql->query("SELECT `id`, `poster`, `title`, `date`, `locked`, `hidden` FROM `threads` WHERE `title` LIKE '%" . escape($_POST['search']) . "%'");

			while($mData = $mQuery->fetch_assoc())
			{
				if(!$mData['hidden'] || $permissions['viewhiddenthreads'])
				{
					$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
					$accountData = $accountQuery->fetch_assoc();

					$sectionIcon = ($mData['locked']) ? "" : "";

					if($mData['hidden'])
					{
						$sectionIcon = "";
					}

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
											Started by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
										</div>
									</td>

									<td>
										Thread Title
									</td>
								</tr>
							</table>
						</div>
					</a>";
				}
			}
		}

		if($_POST['threadcomments'] == "on")
		{
			$mQuery = $mysql->query("SELECT `id`, `poster`, `thread`, `date`, `hidden` FROM `comments` WHERE `comment` LIKE '%" . escape($_POST['search']) . "%'");

			while($mData = $mQuery->fetch_assoc())
			{
				if(!$mData['hidden'] || $permissions['viewhiddencomments'] || ($permissions['viewownhiddencomments'] && $mData['poster'] == $_SESSION['accountid']))
				{
					$threadQuery = $mysql->query("SELECT `title` FROM `threads` WHERE `id` = '" . $mData['thread'] . "'");
					$threadData = $threadQuery->fetch_assoc();

					$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
					$accountData = $accountQuery->fetch_assoc();

					echo
					"<a href='comment?id=" . $mData['id'] . "'>
						<div class='sectionContainer'>
							<table>
								<tr>
									<td width='540'>
										<div class='sectionName'>
											" . $threadData['title'] . "
										</div>

										<div class='sectionDescription'>
											Comment by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
										</div>
									</td>

									<td>
										Thread Comment
									</td>
								</tr>
							</table>
						</div>
					</a>";
				}
			}
		}

		if($_POST['postsbyuser'] == "on")
		{
			$accountQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `displayname` = '" . escape($_POST['search']) . "'");
			$accountData = $accountQuery->fetch_assoc();
			$searchUser = $accountData['id'];

			$mQuery = $mysql->query("SELECT `id`, `poster`, `title`, `date`, `locked`, `hidden` FROM `threads` WHERE `poster` = '$searchUser'");

			while($mData = $mQuery->fetch_assoc())
			{
				if(!$mData['hidden'] || $permissions['viewhiddenthreads'])
				{
					$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
					$accountData = $accountQuery->fetch_assoc();

					$sectionIcon = ($mData['locked']) ? "" : "";

					if($mData['hidden'])
					{
						$sectionIcon = "";
					}

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
											Started by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
										</div>
									</td>

									<td>
										Thread By User
									</td>
								</tr>
							</table>
						</div>
					</a>";
				}
			}

			$mQuery = $mysql->query("SELECT `id`, `poster`, `thread`, `date`, `hidden` FROM `comments` WHERE `poster` = '$searchUser'");

			while($mData = $mQuery->fetch_assoc())
			{
				if(!$mData['hidden'] || $permissions['viewhiddencomments'] || ($permissions['viewownhiddencomments'] && $mData['poster'] == $_SESSION['accountid']))
				{
					$threadQuery = $mysql->query("SELECT `title` FROM `threads` WHERE `id` = '" . $mData['thread'] . "'");
					$threadData = $threadQuery->fetch_assoc();

					$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
					$accountData = $accountQuery->fetch_assoc();

					echo
					"<a href='comment?id=" . $mData['id'] . "'>
						<div class='sectionContainer'>
							<table>
								<tr>
									<td width='540'>
										<div class='sectionName'>
											" . $threadData['title'] . "
										</div>

										<div class='sectionDescription'>
											Comment by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
										</div>
									</td>

									<td>
										Comment By User
									</td>
								</tr>
							</table>
						</div>
					</a>";
				}
			}
		}

		if($_POST['memberlist'] == "on")
		{
			$mQuery = $mysql->query("SELECT `id`, `displayname`, `usertitle` FROM `accounts` WHERE `displayname` LIKE '%" . escape($_POST['search']) . "%'");

			while($mData = $mQuery->fetch_assoc())
			{
				$accountData['usertitle'] = "";

				if($accountQuery->num_rows)
				{
					$accountData = $accountQuery->fetch_assoc();
					$commentPoster = $accountData['displayname'];
				}
				else
				{
					$commentPoster = "Guest";
				}

				if(!$accountData['usertitle'])
				{
					$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['id'] . "' ORDER BY `primary` DESC");

					if($userTitleQuery->num_rows)
					{
						while($userTitleData = $userTitleQuery->fetch_assoc())
						{
							$userGroupQuery = $mysql->query("SELECT `title` FROM `usergroups` WHERE `id` = '" . $userTitleData['usergroup'] . "'");
							$userGroupData = $userGroupQuery->fetch_assoc();

							if($accountData['usertitle'] && $userGropuData['title'])
							{
								$accountData['usertitle'] .= "<br>";
							}

							$accountData['usertitle'] .= $userGroupData['title'];
						}
					}
				}

				echo
				"<a href='user?id=" . $mData['id'] . "' data-tooltip='View Profile: " . $accountData['displayname'] . "'>
					<div class='sectionContainer'>
						<table>
							<tr>
								<td width='540'>
									<div class='sectionName bold'>
										" . userNameTags($mData['id'], $mData['displayname']) . "
									</div>

									<br>

									" . $accountData['usertitle'] . "
								</td>

								<td>
									Member
								</td>
							</tr>
						</table>
					</div>
				</a>";
			}	
		}

		if($_POST['sectionnames'] == "on")
		{
			$mQuery = $mysql->query("SELECT `id`, `name`, `description` FROM `sections` WHERE `name` LIKE '%" . escape($_POST['search']) . "%'");

			while($mData = $mQuery->fetch_assoc())
			{
				echo
				"<a href='thread?id=" . $mData['id'] . "'>
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

								<td>
									Section
								</td>
							</tr>
						</table>
					</div>
				</a>";
			}
		}

		if($_POST['sectiondescriptions'] == "on")
		{
			$mQuery = $mysql->query("SELECT `id`, `name`, `description` FROM `sections` WHERE `description` LIKE '%" . escape($_POST['search']) . "%'");

			while($mData = $mQuery->fetch_assoc())
			{
				echo
				"<a href='thread?id=" . $mData['id'] . "'>
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

								<td>
									Section
								</td>
							</tr>
						</table>
					</div>
				</a>";
			}
		}

		echo "<br>";
	}

?>

<form action='' method='POST'>
	<div class='box'>
		<div class='boxHeading'>
			Search
		</div>

		<div class='boxMain'>
			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Search:</td>
						<td><input type='text' name='search' placeholder='Search' class='boxFormInput' autofocus required></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>In:</td>
						<td>
							<input type='checkbox' name='threadtitles' checked> Thread Titles <br>
							<input type='checkbox' name='threadcomments' checked> Thread Comments <br>
							<input type='checkbox' name='postsbyuser'> Posts By User <br>
							<input type='checkbox' name='memberlist' checked> Member List <br>
							<input type='checkbox' name='sectionnames' checked> Section Names <br>
							<input type='checkbox' name='sectiondescriptions'> Section Descriptions
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<div align='right'>
		<input type='submit' name='dosearch' value='Search' class='boxButton'>
	</div>
</form>

<?php

	require_once("includes/footer.php");

?>