<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if(!$_GET['id'])
	{
		die("You have followed an invalid link.");
	}

	$mQuery = $mysql->query("SELECT * FROM `threads` WHERE `id` = '" . escape($_GET['id']) . "'");
	$mData = $mQuery->fetch_assoc();

	if(!$permissions['viewotherthreads'] && $mData['poster'] != $_SESSION['accountid'])
	{
		redirect("errors/permissions.html");
	}

	if($mData['hidden'] && !$permissions['viewhiddenthreads'])
	{
		redirect("errors/permissions.html");
	}

	$threadLocked = $mData['locked'];
	$threadPoster = $mData['poster'];

	setPageNavigation("thread", $_GET['id']);
	setPageInfo($mData['title'], "" . $mData['views'] . " views");

	$mysql->query("UPDATE `threads` SET `views` = `views` + '1' WHERE `id` = '" . escape($_GET['id']) . "'");

	if($_SESSION['accountid'])
	{
		$readQuery = $mysql->query("SELECT `id` FROM `read` WHERE `thread` = '" . escape($_GET['id']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");

		if(!$readQuery->num_rows)
		{
			$mysql->query("INSERT INTO `read` (`thread`, `user`) VALUES ('" . escape($_GET['id']) . "', '" . $_SESSION['accountid'] . "')");
		}

		if(!$threadLocked)
		{
			echo "<button id='commentScrollPost' class='commentScrollPost boxButton'>Comment</button>";
		}
	}

	echo "<br>";

	if($permissions['votepolls'] && $_POST['votepoll'])
	{
		$pollQuery = $mysql->query("SELECT `id`, `voters` FROM `polls` WHERE `thread` = '" . escape($_GET['id']) . "'");
		$pollData = $pollQuery->fetch_assoc();

		if(strpos($pollData['voters'], "[" . $_SESSION['accountid'] . "]") === false)
		{
			$mysql->query("UPDATE `polloptions` SET `votes` = `votes` + '1' WHERE `id` = '" . escape($_POST['polloption']) . "' AND `poll` = '" . $pollData['id'] . "'");
			$mysql->query("UPDATE `polls` SET `voters` = '" . $pollData['voters'] . "[" . $_SESSION['accountid'] . "]' WHERE `id` = '" . $pollData['id'] . "'");
		}
	}

	$pollQuery = $mysql->query("SELECT `id`, `text`, `voters` FROM `polls` WHERE `thread` = '" . escape($_GET['id']) . "' LIMIT 1");

	if($pollQuery->num_rows)
	{
		$pollData = $pollQuery->fetch_assoc();

		$userHasVoted = (strpos($pollData['voters'], "[" . $_SESSION['accountid'] . "]") !== false) ? 1 : 0;

		$voteQuery = $mysql->query("SELECT `votes` FROM `polloptions` WHERE `poll` = '" . $pollData['id'] . "'");

		while($voteData = $voteQuery->fetch_assoc())
		{
			$totalVotes += $voteData['votes'];
		}

		echo
		"<form action='' method='POST'>
			<div class='box'>
				<div class='boxHeading'>
					Poll: " . $pollData['text'] . "
				</div>

				<div class='boxMain'>";

				$pollQuery = $mysql->query("SELECT `id`, `text`, `votes` FROM `polloptions` WHERE `poll` = '" . $pollData['id'] . "'");

				while($pollData = $pollQuery->fetch_assoc())
				{
					$votePercent = ($pollData['votes'] / $totalVotes) * 100;

					$tooltip = ($pollData['votes'] == 1) ? " has" : "s have";

					echo
					"<div class='boxArea'>
						<table>
							<tr>
								<td width='390'>";

									if(!$userHasVoted)
									{
										echo "<input type='radio' name='polloption' value='" . $pollData['id'] . "'>";
									}

									echo
									"" . $pollData['text'] . "
								</td>

								<td width='640'>
									$votePercent%

									<div data-tooltip='" . $pollData['votes'] . " user$tooltip voted for this option' class='progressBarOuter'>
										<div class='progressBarInner' data-percent='$votePercent%'>
											&nbsp;
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>";
				}

				echo
				"</div>
			</div>";

			if($permissions['votepolls'])
			{
				echo
				"<div align='right'>
					<input type='submit' name='votepoll' value='Vote' class='boxButton'>
				</div>";
			}

		echo	
		"</form>

		<br><br>";
	}

	echo "<div align='right'>";

	if($permissions['lockthreads'] || ($permissions['lockownthreads'] && $mData['poster'] == $_SESSION['accountid']))
	{
		if($mData['locked'])
		{
			echo
			"<a href='lock?thread=" . $_GET['id'] . "' data-tooltip='Unlock Thread' class='commentControl'>
				
			</a>";
		}
		else
		{
			echo
			"<a href='lock?thread=" . $_GET['id'] . "' data-tooltip='Lock Thread' class='commentControl'>
				
			</a>";
		}
	}

	if($permissions['movethreads'] || ($permissions['moveownthreads'] && $mData['poster'] == $_SESSION['accountid']))
	{
		echo
		"<a href='move?thread=" . $_GET['id'] . "' data-tooltip='Move Thread' class='commentControl'>
			
		</a>";
	}

	if($permissions['hidepost'] || ($permissions['hideownposts'] && $mData['poster'] == $_SESSION['accountid']))
	{
		if($mData['hidden'])
		{
			echo
			"<a href='hidepost?thread=" . $_GET['id'] . "' data-tooltip='Show Thread' class='commentControl'>
				
			</a>";
		}
		else
		{
			echo
			"<a href='hidepost?thread=" . $_GET['id'] . "' data-tooltip='Hide Thread' class='commentControl'>
				
			</a>";
		}
	}

	if($permissions['deletepost'] || ($permissions['deleteownthreads'] && $mData['poster'] == $_SESSION['accountid']))
	{
		echo
		"<a href='deletepost?thread=" . $_GET['id'] . "' data-tooltip='Delete Thread' data-warning='Are you sure you want to delete this thread? Once it is deleted, you will not be able to restore it.' class='commentControl'>
			
		</a>";
	}

	echo "</div> <br>";

	$accountQuery = $mysql->query("SELECT `displayname`, `country`, `usertitle`, `avatar`, `ip`, `signature` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");

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
		$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['poster'] . "' ORDER BY `primary` DESC");

		if($userTitleQuery->num_rows)
		{
			while($userTitleData = $userTitleQuery->fetch_assoc())
			{
				$userGroupQuery = $mysql->query("SELECT `title` FROM `usergroups` WHERE `id` = '" . $userTitleData['usergroup'] . "'");
				$userGroupData = $userGroupQuery->fetch_assoc();

				if($accountData['usertitle'] && $userGroupData['title'])
				{
					$accountData['usertitle'] .= "<br>";
				}

				$accountData['usertitle'] .= $userGroupData['title'];
			}
		}
	}

	$likesQuery = $mysql->query("SELECT `id` FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['id']) . "' AND `like` = '1'");
	$dislikesQuery = $mysql->query("SELECT `id` FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['id']) . "' AND `like` = '0'");

	$userLikesQuery = $mysql->query("SELECT `like` FROM `likes` WHERE `thread` = '1' AND `post` = '" . escape($_GET['id']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");
	$userLikesData = $userLikesQuery->fetch_assoc();

	if($mData['hidden'])
	{
		$commentHiddenStyle = "Hidden";
	}
	else
	{
		$commentHiddenStyle = "";
	}

	if($permissions['viewip'])
	{
		$viewIP = " - IP: ".$accountData['ip'];
	}

	$bbParser->parse($mData['body']);

	echo
	"<div class='commentContainer'>
		<div class='commentDate$commentHiddenStyle'>
			" . customDate($mData['date']) . " $viewIP
		</div>

		<div class='commentMain'>
			<table>
				<tr>
					<td width='200'>
						<a href='user?id=" . $mData['poster'] . "' data-tooltip='View Profile: $commentPoster'>
							<div class='commentUser'>
								<div class='bold'>
									" . userNameTags($mData['poster'], $commentPoster) . "
								</div>

								<br>

								" . $accountData['usertitle'] . "
							</div>
						</a>

						<br> <br>

						<img src='" . $accountData['avatar'] . "' data-noenlarge='true' " . getAvatarStyle($mData['poster']) . ">

						<br> <br> <br>

						<span class='bold'>Posts:</span> " . getPostCount($mData['poster']) . " <br>
						<span class='bold'>Country:</span> " . $accountData['country'] . "
					</td>

					<td width='20'></td>

					<td width='900'>
						<div class='commentText'>
							" . nl2br(unescape($bbParser->getAsHtml())) . "";

							storePermissions($mData['poster']);

							if($temporaryPermissions['allowsignature'] && $accountData['signature'] && strlen($accountData['signature']) <= $temporaryPermissions['maxsignature'] && substr_count($accountData['signature'], "\n") <= $temporaryPermissions['maxsignaturelines'])
							{
								echo "<br><br> ____________________________________________________________________ <br><br>";

								if($temporaryPermissions['signaturebbcode'])
								{
									$bbParser->parse($accountData['signature']);

									if($temporaryPermissions['signatureimage'])
									{
										echo nl2br(unescape($bbParser->getAsHtml()));
									}
									else
									{
										echo str_replace("<img", "[IMG", nl2br(unescape($bbParser->getAsHtml())));
									}
								}
							}

							echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br> <div align='left'>";

							if($permissions['downloadattachments'])
							{
								$attachmentsQuery = $mysql->query("SELECT `id`, `path` FROM `attachments` WHERE `thread` = '1' AND `post` = '" . escape($_GET['id']) . "'");

								while($attachmentsData = $attachmentsQuery->fetch_assoc())
								{
									echo
									"<a href='downloadattachment?id=" . $attachmentsData['id'] . "' target='_blank'>
										<div data-tooltip='Attachment: " . round(filesize($attachmentsData['path']) / 1024, 1) . " KB' class='attachmentsBox'>
											" . basename($attachmentsData['path']) . "";

											if($permissions['deleteattachment'])
											{
												echo " <a href='deleteattachment?id=" . $attachmentsData['id'] . "'>[Delete]</a>";
											}

										echo
										"</div>	
									</a>";
								}

								echo "<br><br><br>";
							}

							echo "</div> <div align='right'>";

							if($userLikesQuery->num_rows)
							{
								if($permissions['like'])
								{
									if($userLikesData['like'])
									{
										$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this thread" : "" . $likesQuery->num_rows . " people like this thread";

										echo
										"<span data-tooltip='$tooltip' class='commentLikeButtonActive'>
											 " . $likesQuery->num_rows . "
										</span>";
									}
									else
									{
										$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this thread" : "" . $likesQuery->num_rows . " people like this thread";

										echo
										"<span data-tooltip='$tooltip' class='commentLikeButton'>
											<a href='like?thread=" . $_GET['id'] . "&like'>
												 " . $likesQuery->num_rows . "
											</a>
										</span>";
									}
								}

								if($permissions['dislike'])
								{
									if($userLikesData['like'])
									{
										$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this thread" : "" . $dislikesQuery->num_rows . " people dislike this thread";

										echo
										"<span data-tooltip='$tooltip' class='commentLikeButton'>
											<a href='like?thread=" . $_GET['id'] . "&dislike'>
												 " . $dislikesQuery->num_rows . "
											</a>
										</span>";
									}
									else
									{
										$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this thread" : "" . $dislikesQuery->num_rows . " people dislike this thread";

										echo
										"<span data-tooltip='$tooltip' class='commentLikeButtonActive'>
											 " . $dislikesQuery->num_rows . "
										</span>";
									}
								}
							}
							else
							{
								if($permissions['like'])
								{
									$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this thread" : "" . $likesQuery->num_rows . " people like this thread";

									echo
									"<span data-tooltip='$tooltip' class='commentLikeButton'>
										<a href='like?thread=" . $_GET['id'] . "&like'>
											 " . $likesQuery->num_rows . "
										</a>
									</span>";
								}

								if($permissions['dislike'])
								{
									$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this thread" : "" . $dislikesQuery->num_rows . " people dislike this thread";

									echo
									"<span data-tooltip='$tooltip' class='commentLikeButton'>
										<a href='like?thread=" . $_GET['id'] . "&dislike'>
											 " . $dislikesQuery->num_rows . "
										</a>
									</span>";
								}
							}

							if($permissions['editpost'] || ($permissions['editownposts'] && $mData['poster'] == $_SESSION['accountid']))
							{
								echo
								"<a href='editpost?thread=" . $_GET['id'] . "' data-tooltip='Edit Thread' class='commentControlEx'>
									
								</a>";
							}

							if(($permissions['postcomments'] || ($permissions['postowncomments'] && $threadPoster == $_SESSION['accountid'])) && !$threadLocked)
							{
								echo
								"<a href='postcomment?thread=" . $_GET['id'] . "&reply=thread' data-tooltip='Reply' class='commentControlEx'>
									
								</a>";
							}

							echo 	
							"</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<br>";

	$mQuery = $mysql->query("SELECT * FROM `comments` WHERE `thread` = '" . escape($_GET['id']) . "'");

	while($mData = $mQuery->fetch_assoc())
	{
		if(!$mData['hidden'] || $permissions['viewhiddencomments'] || ($permissions['viewownhiddencomments'] && $mData['poster'] == $_SESSION['accountid']))
		{
			$accountQuery = $mysql->query("SELECT `displayname`, `country`, `usertitle`, `avatar`, `ip`, `signature` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");

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
				$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['poster'] . "' ORDER BY `primary` DESC");

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

			$likesQuery = $mysql->query("SELECT `id` FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($mData['id']) . "' AND `like` = '1'");
			$dislikesQuery = $mysql->query("SELECT `id` FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($mData['id']) . "' AND `like` = '0'");

			$userLikesQuery = $mysql->query("SELECT `like` FROM `likes` WHERE `thread` = '0' AND `post` = '" . escape($mData['id']) . "' AND `user` = '" . $_SESSION['accountid'] . "'");
			$userLikesData = $userLikesQuery->fetch_assoc();

			if($mData['hidden'])
			{
				$commentHiddenStyle = "Hidden";
			}
			else
			{
				$commentHiddenStyle = "";
			}

			if($permissions['viewip'])
			{
				$viewIP = " - IP: ".$accountData['ip'];
			}

			$bbParser->parse($mData['comment']);

			echo
			"<div id='COMMENT-" . $mData['id'] . "' class='commentContainer'>
				<div class='commentDate$commentHiddenStyle'>
					<table>
						<tr>
							<td width='874'>
								" . customDate($mData['date']) . " $viewIP
							</td>

							<td>
								<a href='comment?id=" . $mData['id'] . "'>
									View Comment
								</a>
							</td>
						</tr>
					</table>
				</div>

				<div class='commentMain' style='box-shadow: 0px 5px 20px " . getPostShadow($mData['poster']) . ";'>
					<table>
						<tr>
							<td width='200'>
								<a href='user?id=" . $mData['poster'] . "' data-tooltip='View Profile: $commentPoster'>
									<div class='commentUser'>
										<div class='bold'>
											" . userNameTags($mData['poster'], $commentPoster) . "
										</div>

										<br>

										" . $accountData['usertitle'] . "
									</div>
								</a>

								<br> <br>

								<img src='" . $accountData['avatar'] . "' data-noenlarge='true' " . getAvatarStyle($mData['poster']) . ">

								<br> <br> <br>

								<span class='bold'>Posts:</span> " . getPostCount($mData['poster']) . " <br>
								<span class='bold'>Country:</span> " . $accountData['country'] . "
							</td>

							<td width='20'></td>

							<td width='900'>
								<div class='commentText'>
									" . nl2br(unescape($bbParser->getAsHtml())) . "";

									storePermissions($mData['poster']);

									if($temporaryPermissions['allowsignature'] && $accountData['signature'] && strlen($accountData['signature']) <= $temporaryPermissions['maxsignature'] && substr_count($accountData['signature'], "\n") <= $temporaryPermissions['maxsignaturelines'])
									{
										echo "<br><br> ____________________________________________________________________ <br><br>";

										if($temporaryPermissions['signaturebbcode'])
										{
											$bbParser->parse($accountData['signature']);

											if($temporaryPermissions['signatureimage'])
											{
												echo nl2br(unescape($bbParser->getAsHtml()));
											}
											else
											{
												echo str_replace("<img", "[IMG", nl2br(unescape($bbParser->getAsHtml())));
											}
										}
									}

									echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br> <div align='left'>";

									if($permissions['downloadattachments'])
									{
										$attachmentsQuery = $mysql->query("SELECT `id`, `path` FROM `attachments` WHERE `thread` = '0' AND `post` = '" . escape($mData['id']) . "'");

										while($attachmentsData = $attachmentsQuery->fetch_assoc())
										{
											echo
											"<a href='downloadattachment?id=" . $attachmentsData['id'] . "' target='_blank'>
												<div data-tooltip='Attachment: " . round(filesize($attachmentsData['path']) / 1024, 1) . " KB' class='attachmentsBox'>
													" . basename($attachmentsData['path']) . "";

													if($permissions['deleteattachment'])
													{
														echo " <a href='deleteattachment?id=" . $attachmentsData['id'] . "'>[Delete]</a>";
													}

												echo
												"</div>	
											</a>";
										}

										echo "<br><br><br>";
									}

									echo "</div> <div align='right'>";

									if($userLikesQuery->num_rows)
									{
										if($permissions['like'])
										{
											if($userLikesData['like'])
											{
												$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this comment" : "" . $likesQuery->num_rows . " people like this comment";

												echo
												"<span data-tooltip='$tooltip' class='commentLikeButtonActive'>
													 " . $likesQuery->num_rows . "
												</span>";
											}
											else
											{
												$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this comment" : "" . $likesQuery->num_rows . " people like this comment";

												echo
												"<span data-tooltip='$tooltip' class='commentLikeButton'>
													<a href='like?comment=" . $mData['id'] . "&like'>
														 " . $likesQuery->num_rows . "
													</a>
												</span>";
											}
										}

										if($permissions['dislike'])
										{
											if($userLikesData['like'])
											{
												$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this comment" : "" . $dislikesQuery->num_rows . " people dislike this comment";

												echo
												"<span data-tooltip='$tooltip' class='commentLikeButton'>
													<a href='like?comment=" . $mData['id'] . "&dislike'>
														 " . $dislikesQuery->num_rows . "
													</a>
												</span>";
											}
											else
											{
												$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this comment" : "" . $dislikesQuery->num_rows . " people dislike this comment";

												echo
												"<span data-tooltip='$tooltip' class='commentLikeButtonActive'>
													 " . $dislikesQuery->num_rows . "
												</span>";
											}
										}
									}
									else
									{
										if($permissions['like'])
										{
											$tooltip = ($likesQuery->num_rows == 1) ? "1 person likes this comment" : "" . $likesQuery->num_rows . " people like this comment";

											echo
											"<span data-tooltip='$tooltip' class='commentLikeButton'>
												<a href='like?comment=" . $mData['id'] . "&like'>
													 " . $likesQuery->num_rows . "
												</a>
											</span>";
										}

										if($permissions['dislike'])
										{
											$tooltip = ($dislikesQuery->num_rows == 1) ? "1 person dislikes this comment" : "" . $dislikesQuery->num_rows . " people dislike this comment";

											echo
											"<span data-tooltip='$tooltip' class='commentLikeButton'>
												<a href='like?comment=" . $mData['id'] . "&dislike'>
													 " . $dislikesQuery->num_rows . "
												</a>
											</span>";
										}
									}

									if($permissions['editpost'] || ($permissions['editownposts'] && $mData['poster'] == $_SESSION['accountid']))
									{
										echo
										"<a href='editpost?comment=" . $mData['id'] . "' data-tooltip='Edit Comment' class='commentControlEx'>
											
										</a>";
									}

									if($permissions['hidepost'] || ($permissions['hideownposts'] && $mData['poster'] == $_SESSION['accountid']))
									{
										if($mData['hidden'])
										{
											echo
											"<a href='hidepost?comment=" . $mData['id'] . "' data-tooltip='Show Comment' class='commentControlEx'>
												
											</a>";
										}
										else
										{
											echo
											"<a href='hidepost?comment=" . $mData['id'] . "' data-tooltip='Hide Comment' class='commentControlEx'>
												
											</a>";
										}
									}

									if($permissions['deletepost'] || ($permissions['deleteownposts'] && $mData['poster'] == $_SESSION['accountid']))
									{
										echo
										"<a href='deletepost?comment=" . $mData['id'] . "' data-tooltip='Delete Comment' data-warning='Are you sure you want to delete this comment? Once it is deleted, you will not be able to restore it.' class='commentControlEx'>
											
										</a>";
									}

									if(($permissions['postcomments'] || ($permissions['postowncomments'] && $threadPoster == $_SESSION['accountid'])) && !$threadLocked)
									{
										echo
										"<a href='postcomment?thread=" . $_GET['id'] . "&reply=" . $mData['id'] . "' data-tooltip='Reply' class='commentControlEx'>
											
										</a>";
									}

									echo
									"</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<br>";
		}
	}

	if(($permissions['postcomments'] || ($permissions['postowncomments'] && $threadPoster == $_SESSION['accountid'])) && !$threadLocked)
	{
		echo
		"<form action='postcomment?thread=" . $_GET['id'] . "' method='POST'>
			<div id='commentBox' class='box'>
				<div class='boxHeading'>
					Comment
				</div>

				<div class='boxMain'>
					<button type='button' data-tag='B' class='bbcode boxButton'>bold</button>
					<button type='button' data-tag='I' class='bbcode boxButton'>italic</button>
					<button type='button' data-tag='U' class='bbcode boxButton'>underline</button>
					<button type='button' data-tag='LEFT' class='bbcode boxButton'>left</button>
					<button type='button' data-tag='CENTER' class='bbcode boxButton'>center</button>
					<button type='button' data-tag='RIGHT' class='bbcode boxButton'>right</button>
					<button type='button' data-tag='LIST' class='bbcode boxButton'>bullet list</button>
					<button type='button' data-tag='NLIST' class='bbcode boxButton'>number list</button>
					<button type='button' data-tag='LI' class='bbcode boxButton'>list item</button>
					<button type='button' data-tag='EMAIL' class='bbcode boxButton'>email</button>
					<button type='button' data-tag='IMG' class='bbcode boxButton'>image</button>
					<button type='button' data-tag='QUOTE' class='bbcode boxButton'>quote</button>
					<button id='bbcode-link' type='button' data-tooltip='Example: [URL=http://example.com]Click here[/URL]' class='boxButton'>link</button>
					<button id='bbcode-font' type='button' data-tooltip='Example: [FONT=Arial]Hello world![/FONT]' class='boxButton'>font</button>
					<button id='bbcode-size' type='button' data-tooltip='Example: [SIZE=5]Hello world![/SIZE]' class='boxButton'>size</button>
					<button id='bbcode-color' type='button' data-tooltip='Example: [COLOR=RED]Hello[/COLOR] [COLOR=#00FF00]world![/COLOR]' class='boxButton'>color</button> ";

					if($permissions['mentionusers'])
					{
						echo "<button type='button' data-tag='MENTION' data-tooltip='Example: [MENTION]Jimmy[/MENTION]' class='bbcode boxButton'>mention</button>";
					}

					echo
					"<br><br>

					<textarea id='comment' name='comment' placeholder=' Comment' maxlength='10000' class='boxTextArea' required></textarea>
				</div>
			</div>

			<div align='right'>
				<input type='submit' name='postcomment' value='Post Comment' class='boxButton'> <input type='submit' name='advanced' value='Advanced Editor' class='boxButton'>
			</div>
		</form>

		<br>";
	}

	if($permissions['viewreadthread'])
	{
		$mQuery = $mysql->query("SELECT `user` FROM `read` WHERE `thread` = '" . escape($_GET['id']) . "'");

		$usersRead = ($mQuery->num_rows == 1) ? "1 user has read this thread" : "" . $mQuery->num_rows . " users have read this thread";

		echo
		"<div class='box'>
			<div class='boxHeading'>
				Users Who Read This Thraed
			</div>

			<div class='boxSubHeading'>
				$usersRead
			</div>

			<div class='boxMain'>
				<h3>";

				while($mData = $mQuery->fetch_assoc())
				{
					$accountQuery = $mysql->query("SELECT `displayname`, `usertitle`, `avatar` FROM `accounts` WHERE `id` = '" . $mData['user'] . "'");
					$accountData = $accountQuery->fetch_assoc();

					if($firstDone)
					{
						echo ", ";
					}
					else
					{
						$firstDone = true;
					}

					if(!$accountData['usertitle'])
					{
						$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['user'] . "' ORDER BY `primary` DESC");

						if($userTitleQuery->num_rows)
						{
							while($userTitleData = $userTitleQuery->fetch_assoc())
							{
								$userGroupQuery = $mysql->query("SELECT `title` FROM `usergroups` WHERE `id` = '" . $userTitleData['usergroup'] . "'");
								$userGroupData = $userGroupQuery->fetch_assoc();

								if($accountData['usertitle'] && $userGroupData['title'])
								{
									$accountData['usertitle'] .= "<br>";
								}

								$accountData['usertitle'] .= $userGroupData['title'];
							}
						}
					}

					echo
					"<a href='user?id=" . $mData['user'] . "' data-avatar='" . $accountData['avatar'] . "' data-title='" . $accountData['usertitle'] . "' class='readThread'>
						" . userNameTags($mData['user'], $accountData['displayname']) . "
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
		function insertString(string, insert, position)
		{
			return string.substr(0, position) + insert + string.substr(position);
		}

		function wrapText(before, after)
		{
			var textSelection = $('#comment').getSelection(), selectionStart = textSelection.start, selectionEnd = textSelection.end;
			$('#comment').val(insertString($('#comment').val(), after, selectionEnd)).val(insertString($('#comment').val(), before, selectionStart));
			$('#comment').setSelection(selectionStart + before.length);
		}

		$('.bbcode').click(function()
		{
			wrapText("[" + $(this).data("tag") + "]", "[/" + $(this).data("tag") + "]");
		});

		$('#bbcode-link').click(function()
		{
			wrapText("[URL=http://example.com]", "[/URL]");
		});

		$('#bbcode-font').click(function()
		{
			wrapText("[FONT=Arial]", "[/FONT]");
		});

		$('#bbcode-size').click(function()
		{
			wrapText("[SIZE=5]", "[/SIZE]");
		});

		$('#bbcode-color').click(function()
		{
			wrapText("[COLOR=RED]", "[/COLOR]");
		});

		$('#commentScrollPost').click(function()
		{
			$('html, body').animate({scrollTop: $('#commentBox').offset().top}, 1000);
			$('#comment').focus();
		});

		$('.mentionUser').each(function()
		{
			var currentElement = this;

			$(this).hovercard(
			{
				detailsHTML: "<br> <img src='images/loader.gif'>"
			});

			$.get("userdata.php", {displayname: $(this).data("user")}, function(data)
			{
				var userData = JSON.parse(data);

				$(currentElement).html("<a href='user?id=" + userData.id + "'>" + currentElement.innerHTML + "</a>").parent().children('.hc-details').html("<img class='hc-pic' src='" + userData.avatar + "'> " + userData.title + "");
			});
		});

		$('.readThread').each(function()
		{
			$(this).hovercard(
			{
				detailsHTML: $(this).data("title"),
				width: 400,
				cardImgSrc: $(this).data("avatar")
			})
		});

		$('.progressBarInner').each(function()
		{
			$(this).animate({width: $(this).data("percent")}, 750);
		});
	});
</script>

<?php

	require_once("includes/footer.php");

?>