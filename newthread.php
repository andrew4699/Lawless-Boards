<?php

	require_once("configuration/main.php");

	if(!$permissions['viewforum'] || !$permissions['postthreads'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `name` FROM `sections` WHERE `id` = '" . escape($_GET['section']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();
		setPageInfo("Create New Thread", $mData['name']);
	}
	else
	{
		die("You have followed an invalid link.");
	}

	if($_POST['newthread'])
	{
		if(strlen($_POST['title']) >= 3)
		{
			if(strlen($_POST['body']) >= 3)
			{
				if(time() - $_SESSION['lastcomment'] >= 60)
				{
					$mysql->query("INSERT INTO `threads` (`section`, `poster`, `date`, `lastpost`, `title`, `body`) VALUES ('" . escape($_GET['section']) . "', '" . $_SESSION['accountid'] . "', '" . time() . "', '" . time() . "', '" . escape($_POST['title']) ."', '" . escape($_POST['body']) . "')");
					
					$threadID = $mysql->insert_id;

					if($permissions['postpolls'] && $_POST['polltext'] && $_POST['polloptions'])
					{
						$mysql->query("INSERT INTO `polls` (`thread`, `text`) VALUES ('$threadID', '" . escape($_POST['polltext']) . "')");

						$pollID = $mysql->insert_id;

						foreach(explode("\n", $_POST['polloptions']) as $pollOption)
						{
							$mysql->query("INSERT INTO `polloptions` (`poll`, `text`, `votes`) VALUES ('$pollID', '" . escape($pollOption) . "', '0')");
						}
					}

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
								
								$mysql->query("INSERT INTO `attachments` (`thread`, `post`, `path`) VALUES ('1', '$threadID', '" . ATTACHMENTS_PATH . "/$nextUploadIndex/" . escape($_FILES['attachments']['name'][$fileIndex]) . "')");
							}
						}

						if(!$totalUploadedFiles)
						{
							rmdir(ATTACHMENTS_PATH . "/$nextUploadIndex");

							file_put_contents(ATTACHMENTS_PATH . "/next", $nextUploadIndex - 1);
						}
					}

					$_SESSION['lastcomment'] = time();

					echo "Your thread has been created.";
					redirect("thread?id=$threadID", 2);
				}
				else
				{
					$timeBeforePost = 60 - (time() - $_SESSION['lastcomment']);

					echo
					"<div class='box'>
						<div class='boxHeading'>
							You must wait $timeBeforePost seconds before posting another thread, comment, or profile message.
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

	echo
	"<form action='newthread?section=" . $_GET['section'] . "' method='POST' enctype='multipart/form-data'>
		<div class='box'>
			<div class='boxHeading'>
				Create New Thread
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

					<textarea id='body' name='body' placeholder=' Body' maxlength='20000' class='boxTextArea' required>" . $_POST['body'] . "</textarea>
				</div>
			</div>";

			if($permissions['postpolls'])
			{
				echo
				"<div class='boxSubHeading'>
					Poll
				</div>

				<div class='boxMain'>
					<div class='boxArea'>
						<table>
							<tr>
								<td width='300'>Poll Text:</td>
								<td><input type='text' name='polltext' placeholder='Poll Text' value='" . escape($_POST['polltext']) . "' maxlength='200' class='boxFormInput'></td>
							</tr>
						</table>
					</div>

					<div class='boxArea'>
						<table>
							<tr>
								<td width='300'>Poll Options (1 line = 1 option):</td>
								<td><textarea name='polloptions' placeholder='Poll Options' class='boxTextArea'>" . $_POST['polloptions'] . "</textarea></td>
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
					<div class='boxArea'>
						<input type='hidden' name='MAX_FILE_SIZE' value='" . $permissions['attachmentspacelimit'] . "'>
						<input type='file' name='attachments[]' multiple>
					</div>
				</div>";
			}

		echo
		"</div>

		<div align='right'>
			<input type='submit' name='newthread' value='Create Thread' class='boxButton'>
		</div>
	</form>";

?>

<script>
	$(document).ready(function()
	{
		var fileUploaded = new Array();

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