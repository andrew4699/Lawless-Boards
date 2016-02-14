<?php

	require_once("configuration/main.php");

	if($_SESSION['accountid'])
	{
		redirect("index");
	}

	setPageInfo("Login", "");

	if($_POST['login'])
	{
		if($_POST['username'])
		{
			if($_POST['password'])
			{
				$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `username` = '" . escape($_POST['username']) . "' AND `password` = '" . password($_POST['password']) . "' AND `verified` = '1'");

				if($mQuery->num_rows)
				{
					$mData = $mQuery->fetch_assoc();
					$_SESSION['accountid'] = $mData['id'];

					cookie("lb_accountid", $mData['id']);
					cookie("lb_username", $_POST['username']);
					cookie("lb_password", password($_POST['password']));

					echo
					"<div class='box'>
						<div class='boxHeading'>
							You have successfully logged in.
						</div>
					</div>";

					redirect("index", 2);
				}
				else
				{
					echo
					"<div class='box'>
						<div class='boxHeading'>
							The account information you have entered is invalid or the account is not verified.
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
						You have entered an invalid password.
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
					You have entered an invalid username.
				</div>
			</div>

			<br>";
		}
	}

?>

<form action='login' method='POST'>
	<div class='box'>
		<div class='boxHeading'>
			Login
		</div>

		<div class='boxMain'>
			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>User Name:</td>
						<td><input type='text' name='username' placeholder='User Name' maxlength='50' class='boxFormInput' autofocus required></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Password:</td>
						<td><input type='password' name='password' placeholder='Password' class='boxFormInput' required></td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<div align='right'>
		<input type='submit' name='login' value='Login' class='boxButton'>
	</div>
</form>

<?php

	require_once("includes/footer.php");

?>