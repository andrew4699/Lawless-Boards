<?php

	require_once("configuration/main.php");

	if(!$permissions['viewprofile'])
	{
		redirect("errors/permissions.html");
	}

	if($_GET['id'] == "me")
	{
		redirect("user?id=" . $_SESSION['accountid'] . "");
	}

	$mQuery = $mysql->query("SELECT * FROM `accounts` WHERE `id` = '" . escape($_GET['id']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		setPageInfo("User Profile", $mData['displayname']);

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
	}
	else
	{
		die("You have followed an invalid link.");
	}

	if($_POST['postprofilemessage'])
	{
		if(strlen($_POST['profilemessage']) >= 3)
		{
			if(time() - $_SESSION['lastcomment'] >= 60)
			{
				$mysql->query("INSERT INTO `profilemessages` (`user`, `poster`, `date`, `message`) VALUES ('" . escape($_GET['id']) . "', '" . $_SESSION['accountid'] . "', '" . time() . "', '" . escape($_POST['profilemessage']) . "')");
			
				$_SESSION['lastcomment'] = time();
			}
			else
			{
				$timeBeforeComment = 60 - (time() - $_SESSION['lastcomment']);

				echo
				"<div class='box'>
					<div class='boxHeading'>
						You must wait $timeBeforeComment seconds before posting another thread, comment, profile message, or private message.
					</div>
				</div>

				<br>";
			}
		}
		else
		{
			echo
			"<div class='box'>
				<div class='boxHeading'>
					Your profile message must be at least 3 characters long.
				</div>
			</div>

			<br>";
		}
	}

	echo
	"<div class='userBox'>
		<table>
			<tr>
				<td width='400'>
					<div class='userName bold'>
						" . userNameTags($mData['id'], $mData['displayname']) . "
					</div>

					<div class='userTitles'>
						" . $mData['usertitle'] . "
					</div>
				</td>

				<td width='500'>
					<span class='bold'>Posts:</span> " . getPostCount($mData['id']) . " <br>
					<span class='bold'>Country:</span> " . $mData['country'] . "
				</td>

				<td>
					<img src='" . $mData['avatar'] . "' data-noenlarge='true' class='userAvatar'>
				</td>
			</tr>
		</table>
	</div>

	<br>

	<h2>Profile Messages</h2>";

	$mQuery = $mysql->query("SELECT `id`, `poster`, `date`, `message` FROM `profilemessages` WHERE `user` = '" . escape($_GET['id']) . "' ORDER BY `date` DESC");

	while($mData = $mQuery->fetch_assoc())
	{
		$accountQuery = $mysql->query("SELECT `displayname`, `avatar` FROM `accounts` WHERE `id` = '" . $mData['poster'] . "'");
		$accountData = $accountQuery->fetch_assoc();

		$bbParser->parse($mData['message']);

		echo
		"<div data-user='" . $accountData['displayname'] . "' class='mentionUser userMessageName bold inlineBlock'>
			" . $accountData['displayname'] . "
		</div>

		<div class='userMessageName bold inlineBlock'>
			 - " . customDate($mData['date']) . "
		</div>

		<div class='userMessageBox'>
			" . nl2br(unescape($bbParser->getAsHtml())) . " <br><br><br><br><br><br> <div align='right'>";

			if($permissions['editpost'] || ($permissions['editownprofilemessage'] && $mData['poster'] == $_SESSION['accountid']))
			{
				echo
				"<a href='editpost?profilemessage=" . $mData['id'] . "' data-tooltip='Edit Profile Message' class='commentControlEx'>
					
				</a>";
			}

			if($permissions['deletepost'] || ($permissions['deleteownprofilemessage'] && $mData['poster'] == $_SESSION['accountid']))
			{
				echo
				"<a href='deletepost?profilemessage=" . $mData['id'] . "' data-tooltip='Delete Profile Message' data-warning='Are you sure you want to delete this profile message? Once it is deleted, you will not be able to restore it.' class='commentControlEx'>
					
				</a>";
			}

			echo
			"</div>
		</div>

		<br><br>";
	}

	if($permissions['postprofilemessage'] || ($permissions['postownprofilemessage'] && $_GET['id'] == $_SESSION['accountid']))
	{
		echo
		"<br>

		<form action='user?id=" . $_GET['id'] . "' method='POST'>
			<div id='commentBox' class='box'>
				<div class='boxHeading'>
					Post Profile Message
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

					<textarea id='profilemessage' name='profilemessage' placeholder=' Profile Message' maxlength='5000' class='boxTextArea' required></textarea>
				</div>
			</div>

			<div align='right'>
				<input type='submit' name='postprofilemessage' value='Post Profile Message' class='boxButton'>
			</div>
		</form>

		<br>";
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
			var textSelection = $('#profilemessage').getSelection(), selectionStart = textSelection.start, selectionEnd = textSelection.end;
			$('#profilemessage').val(insertString($('#profilemessage').val(), after, selectionEnd)).val(insertString($('#profilemessage').val(), before, selectionStart));
			$('#profilemessage').setSelection(selectionStart + before.length);
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