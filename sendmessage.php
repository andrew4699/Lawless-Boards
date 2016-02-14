<?php

	require_once("configuration/main.php");

	if(!$permissions['sendprivatemessage'])
	{
		redirect("errors/permissions.html");
	}

	$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `id` = '" . escape($_GET['to']) . "'");

	if(!$mQuery->num_rows)
	{
		die("You have followed an invalid link.");
	}

	if($_POST['sendmessage'])
	{
		if(strlen($_POST['title']) >= 3)
		{
			if(strlen($_POST['message']) >= 3)
			{
				if(time() - $_SESSION['lastcomment'] >= 60)
				{
					$mysql->query("INSERT INTO `privatemessages` (`to`, `from`, `date`, `title`, `message`) VALUES ('" . escape($_GET['to']) . "', '" . $_SESSION['accountid'] . "', '" . time() . "', '" . escape($_POST['title']) . "', '" . escape($_POST['message']) . "')");

					$_SESSION['lastcomment'] = time();

					echo "Your message has been sent.";
					redirect("settings?view=profile", 2);
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
						Your message must be at least 3 characters long.
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
					Your title must be at least 3 characters long.
				</div>
			</div>

			<br>";
		}
	}

	echo
	"<form action='sendmessage?to=" . $_GET['to'] . "' method='POST'>
		<div class='box'>
			<div class='boxHeading'>
				Send Private Message
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

					<textarea id='message' name='message' placeholder=' Message' maxlength='15000' class='boxTextArea' required>" . $_POST['message'] . "</textarea>
				</div>
			</div>
		</div>

		<div align='right'>
			<input type='submit' name='sendmessage' value='Send Message' class='boxButton'>
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
	});
</script>

<?php

	require_once("includes/footer.php");

?>