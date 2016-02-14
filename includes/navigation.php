<script>
	$(document).ready(function()
	{
		$('#navigationBar a').hover(function()
		{
			$(this).stop().animate({backgroundColor: "#525252", color: "white"}, 200);
		},
		function()
		{
			$(this).stop().animate({backgroundColor: "#424242", color: "#A9A9A9"}, 200);
		});
	});
</script>

<div id='navigationBar' class='navigationBar'>
	<?php

		$mQuery = $mysql->query("SELECT * FROM `navigation` WHERE `hidden` IS NULL ORDER BY `order` ASC");

		while($mData = $mQuery->fetch_assoc())
		{
			if($mData['loginonly'] && !$_SESSION['accountid'])
			{
				continue;
			}

			if($mData['logoutonly'] && $_SESSION['accountid'])
			{
				continue;
			}

			echo
			"<a href='" . $mData['link'] . "'>
				" . $mData['text'] . "
			</a>";
		}

	?>
</div>

<br>




<!--
	<a href='index'>
		 Home
	</a>

	<?php if(!$_SESSION['accountid']) { ?>

	<a href='register'>
		 Register
	</a>

	<a href='login'>
		 Login
	</a>

	<?php } else { ?>

	<a href='logout'>
		 Log Out
	</a>

	<?php } ?>
-->