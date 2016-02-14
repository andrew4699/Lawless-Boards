<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'])
	{
		redirect("errors/permissions.html");
	}

	if($_GET['thread'])
	{
		$mQuery = $mysql->query("SELECT `section`, `poster`, `title`, `body` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['editpost'] && (!$permissions['editownposts'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		if($_POST['editthread'])
		{
			if(strlen($_POST['title']) >= 3)
			{
				if(strlen($_POST['body']) >= 3)
				{
					$mysql->query("UPDATE `threads` SET `title` = '" . escape($_POST['title']) . "', `body` = '" . escape($_POST['body']) . "' WHERE `id` = '" . escape($_GET['thread']) . "'");

					$mQuery = $mysql->query("SELECT `id`, `text` FROM `polls` WHERE `thread` = '" . escape($_GET['thread']) . "' LIMIT 1");
					$mData = $mQuery->fetch_assoc();

					if($mData['text'] != $_POST['polltext'])
					{
						$mysql->query("DELETE FROM `polls` WHERE `id` = '" . $mData['id'] . "'");
						$mysql->query("DELETE FROM `polloptions` WHERE `poll` = '" . $mData['id'] . "'");

						if($permissions['postpolls'] && $_POST['polltext'] && $_POST['polloptions'])
						{
							$mysql->query("INSERT INTO `polls` (`thread`, `text`) VALUES ('" . escape($_GET['thread']) . "', '" . escape($_POST['polltext']) . "')");

							$pollID = $mysql->insert_id;

							foreach(explode("\n", $_POST['polloptions']) as $pollOption)
							{
								$mysql->query("INSERT INTO `polloptions` (`poll`, `text`, `votes`) VALUES ('$pollID', '" . escape($pollOption) . "', '0')");
							}
						}
					}

					if($permissions['uploadattachments'])
					{
						$nextUploadIndex = file_get_contents("attachments/next") + 1;

						mkdir("attachments/$nextUploadIndex");

						file_put_contents("attachments/next", $nextUploadIndex);

						foreach($_FILES['attachments']['name'] as $fileIndex => $fileName)
						{
							if($_FILES['attachments']['size'][$fileIndex] <= $permissions['attachmentspacelimit'])
							{
								$totalUploadedFiles++;

								if(!move_uploaded_file($_FILES['attachments']['tmp_name'][$fileIndex], "attachments/$nextUploadIndex/" . $_FILES['attachments']['name'][$fileIndex] . ""))
								{
									continue;
								}
								
								$mysql->query("INSERT INTO `attachments` (`thread`, `post`, `path`) VALUES ('1', '" . escape($_GET['thread']) . "', 'attachments/$nextUploadIndex/" . escape($_FILES['attachments']['name'][$fileIndex]) . "')");
							}
						}

						if(!$totalUploadedFiles)
						{
							rmdir("attachments/$nextUploadIndex");

							file_put_contents("attachments/next", $nextUploadIndex - 1);
						}
					}

					echo "You have edited the thread.";
					redirect("thread?id=" . $_GET['thread'] . "", 2);
				}
				else
				{
					echo
					"<div class='box'>
						<div class='boxHeading'>
							The body must be at least 3 characters long.
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
						The title must be at least 3 characters long.
					</div>
				</div>

				<br>";
			}
		}

		setPageInfo("Edit Thread", $mData['title']);

		if($_GET['removeattachment'])
		{
			$mysql->query("DELETE FROM `attachments` WHERE `id` = '" . escape($_GET['removeattachment']) . "' AND `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "'");
		}

		echo
		"<form action='editpost?thread=" . $_GET['thread'] . "' method='POST' enctype='multipart/form-data'>
			<div id='commentBox' class='box'>
				<div class='boxHeading'>
					Edit Thread
				</div>

				<div class='boxMain'>
					<div class='boxArea'>
						<table>
							<tr>
								<td width='100%'>Title:</td>
								<td><input type='text' name='title' placeholder='Title' value='" . $mData['title'] . "' maxlength='200' class='boxFormInput' required></td>
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

						<textarea id='body' name='body' placeholder=' Body' maxlength='20000' class='boxTextArea' required>" . $mData['body'] . "</textarea>
					</div>
				</div>";

				if($permissions['postpolls'])
				{
					$pollQuery = $mysql->query("SELECT `id`, `text` FROM `polls` WHERE `thread` = '" . escape($_GET['thread']) . "'");
					$pollData = $pollQuery->fetch_assoc();
					$pollText = $pollData['text'];

					$pollQuery = $mysql->query("SELECT `text` FROM `polloptions` WHERE `poll` = '" . $pollData['id'] . "'");

					if($pollQuery->num_rows)
					{
						while($pollData = $pollQuery->fetch_assoc())
						{
							$pollOptions .= $pollData['text']."\n";
						}

						$pollOptions = substr($pollOptions, 0, -2);
					}

					echo
					"<div class='boxSubHeading'>
						Poll
					</div>

					<div class='boxMain'>
						<div class='boxArea'>
							<table>
								<tr>
									<td width='300'>Poll Text:</td>
									<td><input type='text' name='polltext' placeholder='Poll Text' value='$pollText' maxlength='200' class='boxFormInput'></td>
								</tr>
							</table>
						</div>

						<div class='boxArea'>
							<table>
								<tr>
									<td width='300'>Poll Options (1 line = 1 option):</td>
									<td><textarea name='polloptions' placeholder='Poll Options' class='boxTextArea'>$pollOptions</textarea></td>
								</tr>
							</table>
						</div>
					</div>";
				}

				if($permissions['uploadattachments'])
				{
					echo
					"<div class='boxSubHeading'>
						Attachments
					</div>

					<div class='boxMain'>
						<div class='boxArea'>";

						$attachmentsQuery = $mysql->query("SELECT `id`, `path` FROM `attachments` WHERE `thread` = '1' AND `post` = '" . escape($_GET['thread']) . "'");

						while($attachmentsData = $attachmentsQuery->fetch_assoc())
						{
							echo
							"<div class='boxHeading'>
								<div align='left' class='inlineBlock'>
									" . basename($attachmentsData['path']) . "
								</div>

								<div align='right' class='inlineBlock floatRight'>
									<a href='editpost?thread=" . $_GET['thread'] . "&removeattachment=" . $attachmentsData['id'] . "'>
										X
									</a>
								</div>
							</div>

							<br>";
						}

						echo
						"</div>

						<div class='boxArea'>
							<input type='hidden' name='MAX_FILE_SIZE' value='" . $permissions['attachmentspacelimit'] . "'>
							<input type='file' name='attachments[]' multiple>
						</div>
					</div>";
				}

			echo	
			"</div>

			<div align='right'>
				<input type='submit' name='editthread' value='Edit Thread' class='boxButton'>
			</div>
		</form>";
	}
	else if($_GET['comment'])
	{
		$mQuery = $mysql->query("SELECT `thread`, `poster`, `comment` FROM `comments` WHERE `id` = '" . escape($_GET['comment']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['editpost'] && (!$permissions['editownposts'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		if($_POST['editcomment'])
		{
			if(strlen($_POST['body']) >= 3)
			{
				$mysql->query("UPDATE `comments` SET `comment` = '" . escape($_POST['body']) . "' WHERE `id` = '" . escape($_GET['comment']) . "'");

				if($permissions['uploadattachments'])
				{
					$nextUploadIndex = file_get_contents("attachments/next") + 1;

					mkdir("attachments/$nextUploadIndex");

					file_put_contents("attachments/next", $nextUploadIndex);

					foreach($_FILES['attachments']['name'] as $fileIndex => $fileName)
					{
						if($_FILES['attachments']['size'][$fileIndex] <= $permissions['attachmentspacelimit'])
						{
							$totalUploadedFiles++;

							if(!move_uploaded_file($_FILES['attachments']['tmp_name'][$fileIndex], "attachments/$nextUploadIndex/" . $_FILES['attachments']['name'][$fileIndex] . ""))
							{
								continue;
							}
							
							$mysql->query("INSERT INTO `attachments` (`thread`, `post`, `path`) VALUES ('0', '" . escape($_GET['comment']) . "', 'attachments/$nextUploadIndex/" . escape($_FILES['attachments']['name'][$fileIndex]) . "')");
						}
					}

					if(!$totalUploadedFiles)
					{
						rmdir("attachments/$nextUploadIndex");

						file_put_contents("attachments/next", $nextUploadIndex - 1);
					}
				}

				echo "You have edited the comment.";
				redirect("thread?id=" . $mData['thread'] . "#COMMENT=" . $_GET['comment'] . "", 2);
			}
			else
			{
				echo
				"<div class='box'>
					<div class='boxHeading'>
						The comment must be at least 3 characters long.
					</div>
				</div>

				<br>";
			}
		}

		setPageInfo("Edit Comment", "");

		if($_GET['removeattachment'])
		{
			$mysql->query("DELETE FROM `attachments` WHERE `id` = '" . escape($_GET['removeattachment']) . "' AND `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "'");
		}

		echo
		"<form action='editpost?comment=" . $_GET['comment'] . "' method='POST'>
			<div id='commentBox' class='box'>
				<div class='boxHeading'>
					Edit Comment
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

					<textarea id='body' name='body' placeholder=' Comment' maxlength='10000' class='boxTextArea' required>" . $mData['comment'] . "</textarea>
				</div>";

				if($permissions['uploadattachments'])
				{
					echo
					"<div class='boxSubHeading'>
						Attachments
					</div>

					<div class='boxMain'>
						<div class='boxArea'>";

						$attachmentsQuery = $mysql->query("SELECT `id`, `path` FROM `attachments` WHERE `thread` = '0' AND `post` = '" . escape($_GET['comment']) . "'");

						while($attachmentsData = $attachmentsQuery->fetch_assoc())
						{
							echo
							"<div class='boxHeading'>
								<div align='left' class='inlineBlock'>
									" . basename($attachmentsData['path']) . "
								</div>

								<div align='right' class='inlineBlock floatRight'>
									<a href='editpost?comment=" . $_GET['comment'] . "&removeattachment=" . $attachmentsData['id'] . "'>
										X
									</a>
								</div>
							</div>

							<br>";
						}

						echo
						"</div>

						<div class='boxArea'>
							<input type='hidden' name='MAX_FILE_SIZE' value='" . $permissions['attachmentspacelimit'] . "'>
							<input type='file' name='attachments[]' multiple>
						</div>
					</div>";
				}

			echo
			"</div>

			<div align='right'>
				<input type='submit' name='editcomment' value='Edit Comment' class='boxButton'>
			</div>
		</form>";
	}
	else if($_GET['profilemessage'])
	{
		$mQuery = $mysql->query("SELECT `user`, `poster`, `message` FROM `profilemessages` WHERE `id` = '" . escape($_GET['profilemessage']) . "'");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
		}
		else
		{
			die("You have followed an invalid link.");
		}

		if(!$permissions['editpost'] && (!$permissions['editownprofilemessage'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		if($_POST['editprofilemessage'])
		{
			if(strlen($_POST['body']) >= 3)
			{
				$mysql->query("UPDATE `profilemessages` SET `message` = '" . escape($_POST['body']) . "' WHERE `id` = '" . escape($_GET['profilemessage']) . "'");

				echo "You have edited the profile message.";
				redirect("user?id=" . $mData['user'] . "", 2);
			}
			else
			{
				echo
				"<div class='box'>
					<div class='boxHeading'>
						The profile message must be at least 3 characters long.
					</div>
				</div>

				<br>";
			}
		}

		setPageInfo("Edit Profile Message", "");

		echo
		"<form action='editpost?profilemessage=" . $_GET['profilemessage'] . "' method='POST'>
			<div id='commentBox' class='box'>
				<div class='boxHeading'>
					Edit Profile Message
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

					<textarea id='body' name='body' placeholder=' Profile Message' maxlength='5000' class='boxTextArea' required>" . $mData['message'] . "</textarea>
				</div>
			</div>

			<div align='right'>
				<input type='submit' name='editprofilemessage' value='Edit Profile Message' class='boxButton'>
			</div>
		</form>";
	}
	else
	{
		die("You have followed an invalid link.");
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
			var textSelection = $('#body').getSelection(), selectionStart = textSelection.start, selectionEnd = textSelection.end;
			$('#body').val(insertString($('#body').val(), after, selectionEnd)).val(insertString($('#body').val(), before, selectionStart));
			$('#body').setSelection(selectionStart + before.length);
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
	});
</script>

<?php

	require_once("includes/footer.php");

?>