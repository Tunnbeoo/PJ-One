<style type="text/css">
	.err {
		font-family: tahoma;
		color: #FFFFFF;
		font-size: 12px;
	}
</style>

<script language="javascript">
	function btnLogin_onclick() {
		if (test_empty(document.frmLogin.txtUid.value)) {
			alert(mustInput_Uid);
			document.frmLogin.txtUid.focus();
			return false;
		}
		if (test_empty(document.frmLogin.txtPwd.value)) {
			alert(mustInput_Pwd);
			document.frmLogin.txtPwd.focus();
			return false;
		}
		return true;
	}
</script>

<?php
session_start();
include('db_connection.php'); // Ensure you have this file for DB connection

$errMsg = '';
$flagLogin = false;

$l_notmember = $_lang == 'vn' ? 'Bạn chưa là thành viên' : 'Not member';
$l_member = $_lang == 'vn' ? 'Bạn đã là thành viên' : 'Member';
$l_Uid = $_lang == 'vn' ? 'Tên đăng nhập' : 'Username';
$l_Pwd = $_lang == 'vn' ? 'Mật khẩu' : 'Password';
$l_ForgotPwd = $_lang == 'vn' ? 'Quên mật khẩu' : 'Forgot Password';
$l_btnRegistry = $_lang == 'vn' ? 'Đăng ký' : 'Registry';
$l_btnLogin = $_lang == 'vn' ? 'Đăng nhập' : 'Login';
$l_btnLogout = $_lang == 'vn' ? 'Đăng xuất' : 'Logout';
$l_Welcome = $_lang == 'vn' ? 'Chào' : 'Welcome';
$l_LoginSuccess = $_lang == 'vn' ? 'Bạn đã đăng nhập thành công.' : 'Login Successfully.';

if (isset($_REQUEST['frame']) && $_REQUEST['frame'] == 'logout') {
	unset($_SESSION['member']);
	echo "<script>window.location='./'</script>";
}

if (isset($_SESSION['member']) && $_SESSION['member'] != '') {
	$flagLogin = true;
}

if (isset($_POST['btnLogin'])) {
	$uid = trim($_POST['txtUid']);
	$pwd = trim($_POST['txtPwd']);

	// Prepare and execute the SQL statement
	$stmt = $conn->prepare("SELECT member_id, pwd FROM tbl_member WHERE uid = ?");
	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows < 1) {
		$errMsg = $_lang == 'vn' ? 'Sai "tên đăng nhập" !' : 'Username wrong !';
	} else {
		$row = $result->fetch_assoc();
		// Verify the password
		if (!password_verify($pwd, $row['pwd'])) {
			$errMsg = $_lang == 'vn' ? 'Sai "mật khẩu" !' : 'Password wrong !';
		} else {
			$_SESSION['member'] = $uid; // Store username in session
			$_SESSION['member_id'] = $row['member_id']; // Store member ID in session
			$flagLogin = true;
		}
	}
	$stmt->close();
}

if ($flagLogin) {
?>
	<link href="../css/style.css" rel="stylesheet" type="text/css" />
	<table align="center" border="0" width="214" cellpadding="0" cellspacing="0">
		<tr>
			<td height="5"></td>
		</tr>
		<tr>
			<td>
				<table align="center" border="0" width="164" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center">
							<br><br><br>
							<?php echo $l_Welcome . ' <b class="fontRed">' . $_SESSION['member'] . '</b>' ?>
							<br><br>
							<span style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#FFCC00">
								<b><?php echo $l_LoginSuccess ?></b>
							</span>
							<br><br>
							<a class="aMagenta" href="./?frame=logout"><?php echo $l_btnLogout ?></a> ]
							<br><br><br>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="5"></td>
		</tr>
	</table>
<?php
} else {
?>
	<table width="214" border="0" align="center" cellpadding="0" cellspacing="0">
		<form name="frmLogin" action="./" method="post">
			<tr>
				<td align="left" valign="middle" id="bg_login">
					<input name="txtUid" type="text" class="inputbox1" required placeholder="<?php echo $l_Uid ?>" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="left" valign="middle" id="bg_login">
					<input name="txtPwd" type="password" class="inputbox1" required placeholder="<?php echo $l_Pwd ?>" />
				</td>
			</tr>
			<tr>
				<td height="26" align="left" valign="bottom">
					<input class="buttonorange" onmouseover="this.className='bg_over'" style="WIDTH: 89px; HEIGHT: 22px; cursor:pointer"
						onmouseout="this.className='bg_out'" type="submit" value="<?php echo $l_btnLogin ?>" name="btnLogin" onclick="return btnLogin_onclick()" />
				</td>
			</tr>
		</form>
	</table>
<?php
}

// Display error message if any
if ($errMsg != '') {
	echo '<p align=center class="err">' . $errMsg . '</p>';
	echo '<br/>';
}
?>