<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'] || (!$permissions['postcomments'] && !$permissions['postowncomments']))
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `poster`, `title`, `body`, `locked` FROM `threads` WHERE `id` = '" . escape($_GET['thread']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		if(!$permissions['viewotherthreads'] && $mData['poster'] != $_SESSION['accountid'])
		{
			redirect("errors/permissions.html");
		}

		if(!$permissions['postcomments'] && (!$permissions['postowncomments'] || $mData['poster'] != $_SESSION['accountid']))
		{
			redirect("errors/permissions.html");
		}

		if($mData['locked'])
		{
			echo "This thread is locked.";
			redirect("thread?id=" . $_GET['thread'] . "", 2);
		}
	}
	else
	{
		die("You have followed an invalid link.");
	}

	setPageInfo("Post Comment", $mData['title']);

	if($_GET['reply'] && !$_POST['comment'])
	{
		if($_GET['reply'] == "thread")
		{
			$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . escape($mData['poster']) . "'");
			$accountData = $accountQuery->fetch_assoc();

			$_POST['comment'] = "[SIZE=11]Quote by " . $accountData['displayname'] . "[/SIZE]\r\n[QUOTE]".$mData['body']."[/QUOTE]\r\n\r\n";
		}
		else
		{
			$commentQuery = $mysql->query("SELECT `poster`, `comment` FROM `comments` WHERE `id` = '" . escape($_GET['reply']) . "'");

			if($commentQuery->num_rows)
			{
				$commentData = $commentQuery->fetch_assoc();

				$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . escape($commentData['poster']) . "'");
				$accountData = $accountQuery->fetch_assoc();

				$_POST['comment'] = "[SIZE=11]Quote by " . $accountData['displayname'] . "[/SIZE]\r\n[QUOTE]".$commentData['comment']."[/QUOTE]\r\n\r\n";
			}
		}
	}

	if($_POST['postcomment'])
	{
		if(strlen($_POST['comment']) >= 3)
		{
			if(time() - $_SESSION['lastcomment'] >= 60)
			{
				$theTime = time();

				$mysql->query("INSERT INTO `comments` (`thread`, `poster`, `date`, `comment`) VALUES ('" . escape($_GET['thread']) . "', '" . $_SESSION['accountid'] . "', '$theTime', '" . escape($_POST['comment']) . "')");
				
				$newCommentID = $mysql->insert_id;

				if($permissions['uploadattachments'])
				{
					$nextUploadIndex = file_get_contents(ATTACHMENTS_PATH . "/next") + 1;

					mkdir(ATTACHMENTS_PATH . "/$nextUploadIndex");

					file_put_contents(ATTACHMENTS_PATH . "/next", $nextUploadIndex);

					foreach($_FILES['attachments']['name'] as $fileIndex => $fileName)
					{
						if($_FILES['attachments']['size'][$fileIndex] <= $permissions['attachmentspacelimit'])
						{
							$totalUploadedFiles++;

							if(!move_uploaded_file($_FILES['attachments']['tmp_name'][$fileIndex], ATTACHMENTS_PATH . "/$nextUploadIndex/" . $_FILES['attachments']['name'][$fileIndex] . ""))
							{
								continue;
							}

							$mysql->query("INSERT INTO `attachments` (`thread`, `post`, `path`) VALUES ('0', '$newCommentID', '" . ATTACHMENTS_PATH . "/$nextUploadIndex/" . escape($_FILES['attachments']['name'][$fileIndex]) . "')");
						}
					}

					if(!$totalUploadedFiles)
					{
						rmdir(ATTACHMENTS_PATH . "/$nextUploadIndex");

						file_put_contents(ATTACHMENTS_PATH . "/next", $nextUploadIndex - 1);
					}
				}

				$mysql->query("UPDATE `threads` SET `lastpost` = '$theTime' WHERE `id` = '" . escape($_GET['thread']) . "'");

				$_SESSION['lastcomment'] = time();

				echo "Your comment has been posted.";
				redirect("thread?id=" . $_GET['thread'] . "#COMMENT-$newCommentID", 2);
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
					Your comment must be at least 3 characters long.
				</div>
			</div>

			<br>";
		}
	}

	echo
	"<form action='postcomment?thread=" . $_GET['thread'] . "' method='POST' enctype='multipart/form-data'>
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
					
				<textarea id='comment' name='comment' placeholder=' Comment' maxlength='10000' class='boxTextArea' autofocus required>" . $_POST['comment'] . "</textarea>
			</div>";

			if($permissions['uploadattachments'])
			{
				echo
				"<div class='boxSubHeading'>
					Attachments
				</div>

				<div class='boxMain'>
					<div class='boxArea'>
						<input type='hidden' name='MAX_FILE_SIZE' value='" . $permissions['attachmentspacelimit'] . "'>
						<input type='file' name='attachments[]' multiple>
					</div>
				</div>";
			}

		echo
		"</div>

		<div align='right'>
			<input type='submit' name='postcomment' value='Post Comment' class='boxButton'>
		</div>
	</form>";

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
	});
</script>

<?php

	require_once("includes/footer.php");

?>