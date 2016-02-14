<?php

	require_once("configuration/main.php");

	$mQuery = $mysql->query("SELECT * FROM `privatemessages` WHERE `id` = '" . escape($_GET['id']) . "' AND `to` = '" . $_SESSION['accountid'] . "'");

	if($mQuery->num_rows)
	{
		setPageInfo("Private Message", "");

		$mData = $mQuery->fetch_assoc();

		$accountQuery = $mysql->query("SELECT `displayname`, `country`, `usertitle`, `avatar`, `ip`, `signature` FROM `accounts` WHERE `id` = '" . $mData['from'] . "'");
		$accountData = $accountQuery->fetch_assoc();

		if(!$accountData['usertitle'])
		{
			$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['from'] . "' ORDER BY `primary` DESC");

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

		if(!$mData['read'])
		{
			$mysql->query("UPDATE `privatemessages` SET `read` = '1' WHERE `id` = '" . escape($_GET['id']) . "'");
		}
	}
	else
	{
		die("You have followed an invalid link.");
	}

	$bbParser->parse($mData['message']);

	echo
	"<div class='box'>
		<div class='boxHeading'>
			" . $mData['title'] . "
		</div>

		<div class='boxSubHeading'>
			Sent by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
		</div>

		<div class='boxMain'>
			<table class='tdAlignTop'>
				<tr>
					<td width='200'>
						<a href='user?id=" . $mData['from'] . "' data-tooltip='View Profile: " . $accountData['displayname'] . "'>
							<div class='commentUser'>
								<div class='bold'>
									" . userNameTags($mData['from'], $accountData['displayname']) . "
								</div>

								<br>

								" . $accountData['usertitle'] . "
							</div>
						</a>

						<br> <br>

						<img src='" . $accountData['avatar'] . "' data-noenlarge='true' " .  getAvatarStyle($mData['from']) . ">

						<br> <br> <br>

						<span class='bold'>Posts:</span> " . getPostCount($mData['from']) . " <br>
						<span class='bold'>Country:</span> " . $accountData['country'] . "
					</td>

					<td width='20'></td>

					<td width='900'>
						<div class='commentText'>
							" . nl2br(unescape($bbParser->getAsHtml())) . "";

							storePermissions($mData['from']);

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
						echo
						"</div>
					</td>
				</tr>
			</table>
		</div>
	</div>";

	if($permissions['sendprivatemessage'])
	{
		echo
		"<br>

		<form action='sendmessage?to=" . $mData['from'] . "' method='POST'>
			<div class='box'>
				<div class='boxHeading'>
					Reply
				</div>

				<div class='boxMain'>
					<div class='boxArea'>
						<table>
							<tr>
								<td width='100%'>Title:</td>
								<td><input type='text' name='title' placeholder='Title' value='" . $_POST['title'] . "' maxlength='200' class='boxFormInput' autofocus required></td>
							</tr>
						</table>
					</div>

					<div class='boxArea'>
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

						<textarea id='message' name='message' placeholder=' Message' maxlength='15000' class='boxTextArea' required></textarea>
					</div>
				</div>
			</div>

			<div align='right'>
				<input type='submit' name='sendmessage' value='Send Message' class='boxButton'>
			</div>
		</form>";
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
			var textSelection = $('#message').getSelection(), selectionStart = textSelection.start, selectionEnd = textSelection.end;
			$('#message').val(insertString($('#message').val(), after, selectionEnd)).val(insertString($('#message').val(), before, selectionStart));
			$('#message').setSelection(selectionStart + before.length);
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