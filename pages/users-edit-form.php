<?php
	$id = array_key_exists('id', $_GET) ? $_GET['id'] : false;
	$perms = array_key_exists('perms', $_POST) ? $_POST['perms'] : false;
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;
	$sent = array_key_exists('sent', $_POST) ? $_POST['sent'] : false;

	$allowed = true;
	if ((!$user->checkUserPermission(USER_PERMISSION_USER_CREATE)) && ($action == 'add'))
		$allowed = false;

	if ((!$user->checkUserPermission(USER_PERMISSION_USER_EDIT)) && ($action == 'edit'))
		$allowed = false;

	$skip = false;
	if (!$allowed) {
		$skip = true;
		echo "<div id=\"msg-error\"><b>{$lang->get('msg')}: </b>{$lang->get('permission-denied')}</div>";
	}
	if ($action == 'del') {
		if ((array_key_exists('confirm', $_GET)) && ($_GET['confirm'] == 1)) {
			$id = $_GET['id'];

			$ident = $user->del($_GET['id']);
			if (is_string($ident))
				echo '<div id="msg-error">'.$lang->get($ident).'</div>';
			else
				echo '<div id="msg-info">'.$lang->get('deleted').'</div>';
				
			$skip = true;
		}
		else {
			$name = $user->getUserName($_GET['id']);
			$back = 'users';
			$type = 'user';
			include('delete-form.php');
			$skip = true;
		}
	}

	if (!$skip):
	if ($action == 'add')
		$title = 'user-add';
	else
		$title = 'user-edit';

	if (!$id)
		$id = -1;

	if (!isset($data))
		$data = array();

	if (is_array($perms) && ($sent)) {
		$ak = array_keys($perms);
		if ($id == -1) {
			if (!$user->register($_POST['username'], $_POST['password'], false, false, $ak))
				$msg = $lang->get($title.'-failed');
			else
				$msg = $lang->get($title.'-ok');
		}
		else {
			$password = ($_POST['password'] == '') ? false : $_POST['password'] ;
			if (!$user->edit($_POST['username'], $password, false, $ak))
				$msg = $lang->get($title.'-failed');
			else
				$msg = $lang->get($title.'-ok');
		}
	}

	$apikey = false;
	if (($action == 'edit') && ($id)) {
		$data = $user->getUser($id);
		$apikey = array_key_exists('apikey', $data) ? $data['apikey'] : false;
	}

	if ((!$apikey) || (array_key_exists('renew', $_GET))) {
		$apikey = $user->generateRandomChars(128);

		if ($id)
			$user->setAPIKey($id, $apikey);

		if (array_key_exists('renew', $_GET)) {
			$tmp = explode('renew=1', $_SERVER['REQUEST_URI']);
			echo "<script>
				<!--
				location.href = '".$tmp[0]."';
				-->
				</script>";
		}
	}
?>

<h1><?php echo $lang->get($title) ?></h1>

<script>
<!--
	function check_values_add() {
		var p = document.getElementById('password').value;
		var c = document.getElementById('cpassword').value;

		if (document.getElementById('username').value == '') {
			alert('<?php echo $lang->get('missing-username') ?>');
			return false;
		}

		if (document.getElementById('password').value == '') {
			alert('<?php echo $lang->get('missing-password') ?>');
			return false;
		}

		if (p != c) {
			alert('<?php echo $lang->get('password-mismatch') ?>');
			return false;
		}

		return true;
	}

	function check_values_edit() {
		var p = document.getElementById('password').value;
		var c = document.getElementById('cpassword').value;

		if (p != c) {
			alert('<?php echo $lang->get('password-mismatch') ?>');
			return false;
		}

		return true;
	}

	function apikey_renew() {
		if (!confirm(('<?php echo $lang->get('apikey-renew-msg') ?>')))
			return false;

		location.href = '<?php echo $_SERVER['REQUEST_URI'] ?>&renew=1';
	}
-->
</script>

<?php
	if (isset($msg))
		echo "<div id=\"msg-info\"><b>{$lang->get('msg')}: </b>$msg</div>";
?>

	<form method="POST" onsubmit="return check_values_<?php echo $action ?>()">

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get($title) ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('username') ?>: </td>
			<td class="field"><input type="text" name="username" id="username" value="<?php echo array_key_exists('username', $data) ? $data['username'] : '' ?>"
				<?php if ($action == 'edit') echo 'readonly="readonly" style="background-color: lightgray"'; ?>
				/></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('password') ?>: </td>
			<td class="field"><input type="password" name="password" id="password" /></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('confirm-password') ?>: </td>
			<td class="field"><input type="password" name="cpassword" id="cpassword" /></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('user-registration-user-agent') ?>: </td>
			<td class="field"><?php echo array_key_exists('regUserAgent', $data) ? $data['regUserAgent'] : '-' ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('user-registered-from') ?>: </td>
			<td class="field"><?php echo array_key_exists('regFrom', $data) ? @Date($lang->get('date-format'), $data['regFrom']) : '-' ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('user-last-login') ?>: </td>
			<td class="field"><?php echo array_key_exists('lastLogin', $data) ? @Date($lang->get('date-format'), $data['lastLogin']) : '-' ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('user-num-logins') ?>: </td>
			<td class="field"><?php echo array_key_exists('numLogins', $data) ? $data['numLogins'] : '-' ?></td>
		</tr>

		<tr>
			<td class="title"><?php echo $lang->get('permissions') ?>: </td>
			<td class="field-checkbox">
<?php
	reset($user_permissions);
	while (list($key, $val) = each($user_permissions)) {
		$c = false;
		if (array_key_exists('permissions', $data)) {
			for ($i = 0; $i < sizeof($data['permissions']); $i++)
				if ($data['permissions'][$i] == $key)
					$c = true;
		}

		echo '<input type="checkbox" value="1" name="perms['.$key.']" '.($c ? 'checked="checked"' : '').'>'.$lang->get($val).'</option><br />';
	}
?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('user-api-key') ?>:<br />(<a style="cursor: pointer; text-decoration: underline" onclick="apikey_renew()"><?php echo $lang->get('renew') ?></a>)
			</td>
			<td class="field">
				<textarea readonly="readonly" rows="5" cols="75" name="apikey"><?php echo $apikey ?></textarea>
			</td>
		</tr>
<?php
	if (function_exists('qrencode')) {
		echo "<tr>";
		echo "<td class=\"title\">QR Code: </td>";
		echo "<td class=\"field\">";

		if (array_key_exists('host', $_GET))
			$addr = $_GET['host'];
		else {
			$addr = (array_key_exists('REMOTE_HOST', $_SERVER) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR']);
			if (strpos('.'.$addr, '::')) // IPv6 needs to be encoded in brackets
				$addr = '['.$addr.']';
		}

		$str = "http://localhost/virtDroid/?address=http://".$addr."/php-virt-control/xmlrpc.php&apikey=".$apikey;
		$qrdata = qrencode($str,3,QR_ECLEVEL_M,QR_MODE_8);
		foreach($qrdata as $row) {
			echo "<div class=\"qr_line\">";
			foreach($row as $cell)
				echo ($cell=="0") ? "<b></b>" : "<i></i>";
			echo "</div>";
		}

		echo "</tr>";
	}
?>
		<tr>
			<td class="title">&nbsp;</td>
			<td class="field"><input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get($title) ?>" /></td>
			<input type="hidden" name="id" value="<?php echo $id ?>" />
			<input type="hidden" name="sent" value="1" />
		</tr>
	</table>

	</form>
<?php
	endif;
?>
