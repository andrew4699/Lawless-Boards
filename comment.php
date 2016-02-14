<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT * FROM `comments` WHERE `id` = '" . escape($_GET['id']) . "'");
	$mData = $mQuery->fetch_assoc();

	if(!$permissions['viewotherthreads'] && $mData['poster'] != $_SESSION['accountid'])
	{
		redirect("errors/permissions.html");
	}

	if($mData['hidden'] && !$permissions['viewhiddencomments'] && (!$permissions['viewownhiddencomments'] || $mData['poster'] != $_SESSION['accountid']))
	{
		redirect("errors/permissions.html");
	}

	setPageInfo("View Comment", "");

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
	"<div class='commentContainer'>
		<div class='commentDate$commentHiddenStyle'>
			<table>
				<tr>
					<td width='887'>
						" . customDate($mData['date']) . " $viewIP
					</td>

					<td>
						<a href='thread?id=" . $mData['thread'] . "#COMMENT-" . $mData['id'] . "'>
							View Thread
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

						<img src='" . $accountData['avatar'] . "' data-noenlarge='true' " .  getAvatarStyle($mData['poster']) . ">

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

							echo
							"</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>";

?>

<script>
	$(document).ready(function()
	{
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
	});
</script>

<?php

	require_once("includes/footer.php");

?>