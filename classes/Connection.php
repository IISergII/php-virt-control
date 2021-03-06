<?php
	class Connection extends Database {
		private $_fn = false;
		private $tab = 'Connections';
		private $tabAssoc = 'AssocUserConnections';
		public $_origin = __CLASS__;
		public $_log_head = __CLASS__;
		public $_tables = array( 'Connections', 'AssocUserConnections' );
		
		function __construct($fn) {
			parent::__construct($fn);
			$this->_fn = $fn;
			$this->_ensure_database_models();
		}
		
		function get($id, $fields = false) {
			$id = (int)$id;
			
			$conditions = array(
					'id' => $id
					);

			if (!$fields)
				$fields = array(
					'id',
					'name',
					'hypervisor',
					'host',
					'method',
					'username',
					'password',
					'uri_override',
					'log_file',
					'created',
					'creatorId'
					);
			
			$ret = $this->select($this->tab, $conditions, $fields);
			if (empty($ret))
				return false;
			return $ret;
		}
		
		function getName($id) {
			$ret = $this->get($id, 'name');
			if (!$ret)
				return false;
			return $ret[0];
		}

		function getByUser($idUser = false) {
			if ($idUser == false)
				$idUser = $_SESSION['User'];
			$idUser = (int)$idUser;
			if ($idUser == 0)
				return array();

			$fields = array(
					'id',
					'name'
					);

			$conditions = array(
					'creatorId' => $idUser
					);

			$ret = $this->select($this->tab, $conditions, $fields);
			return $this->sortArrayBy($ret, 'name');
		}

		function getAllowedForUser($id) {
			$fields = array(
						'idConnection AS id'
					);

			$conditions = array(
						'idUser' => $id
					);

			$out = array();
			$ret = $this->select($this->tabAssoc, $conditions, $fields);
			for ($i = 0; $i < sizeof($ret); $i++)
				$out[] = $ret[$i]['id'];

			return $out;
		}

		function isMyConnection($id) {
			$cId = $this->get($id, array('creatorId'));
			$idUser = $_SESSION['User'];

			if (!$idUser)
				return false;

			return ($cId[0]['creatorId'] == $idUser) ? true : false;
		}

		function getAssociatedUsers($id) {
			$fields = array(
					'id',
					'idUser',
					'createdUser',
					'created AS createTimestamp',
					'DATE_FORMAT(FROM_UNIXTIME(created), "%d.%m.%Y") AS created'
					);

			$conditions = array(
					'idConnection' => $id
					);

			return $this->select($this->tabAssoc, $conditions, $fields);
		}

		function getAllowedUsers($id, $justNames=true) {
			$userObj = new User($this->_fn);
			$ret = $this->getAssociatedUsers($id);
			$rv = array();
			for ($i = 0; $i < sizeof($ret); $i++) {
				if ($justNames)
					$rv[] = $userObj->getUserName((int)$ret[$i]['idUser']);
				else
					$rv[] = array(
							'id' => $ret[$i]['idUser'],
							'name' => $userObj->getUserName((int)$ret[$i]['idUser'])
							);
			}

			return $rv;
		}

		function add($name, $hv, $method, $host, $username, $password, $uri_override = false, $log_file = false) {
			$name = $this->safeString($name);
			
			$fields = array(
					'id'
					);
					
			$conditions = array(
					'name' => $name
					);
					
			$ret = $this->select($this->tab, $conditions, $fields);
			if (sizeof($ret) > 0)
					return 'connection-name-exists';

			$idUser = $_SESSION['User'];
			$fields = array(
					'name' => $name,
					'hypervisor' => $hv,
					'method' => $method,
					'username' => $username,
					'password' => $password,
					'created' => time(),
					'creatorId' => $idUser
					);
			if ($host != false)
				$fields['host'] = $host;
			if ($uri_override != false)
				$fields['uri_override'] = $uri_override;
			if ($log_file != false)
				$fields['log_file'] = $log_file;
					
			if (!$this->insert($this->tab, $fields))
				return 'connection-add-failed';
			$id = $this->lastInsertID();
			$fields = array(
					'idUser' => $idUser,
					'idConnection' => $id,
					'createdUser' => $idUser,
					'created' => time()
					);
					
			if ($this->insert($this->tabAssoc, $fields))
				return $id;
			else
				return 'connection-assoc-failed';
		}

		function addUserAssoc($idConnection, $idUser) {
			$lidUser = $_SESSION['User'];
			$fields = array(
					'idUser' => $idUser,
					'idConnection' => $idConnection,
					'createdUser' => $lidUser,
					'created' => time()
					);

			return $this->insert($this->tabAssoc, $fields);
		}

		function delUserAssoc($idConnection, $idUser) {
			$idConnection = (int)$idConnection;
			$idUser = (int)$idUser;

			if ((!$idConnection) || (!$idUser))
				return;

			$conditionsAssoc = array(
					'idConnection' => $idConnection,
					'idUser' => $idUser
					);

			return $this->delete($this->tabAssoc, $conditionsAssoc);
		}

		function delAllUserAssoc($idConnection) {
			$idConnection = (int)$idConnection;

			if (!$idConnection)
				return;

			$conditionsAssoc = array(
					'idConnection' => $idConnection
					);

			return $this->delete($this->tabAssoc, $conditionsAssoc);
		}

		function edit($id, $name, $hv, $method, $host, $username, $password, $uri_override = false, $log_file = false) {
			$id = (int)$id;
			$name = $this->safeString($name);

			$fields = array(
					'id'
					);

			$conditions = array(
					'id' => $id
					);

			$ret = $this->select($this->tab, $conditions, $fields);
			if (sizeof($ret) != 1)
					return 'connection-id-not-exist';

			$fields = array(
					'name' => $name,
					'hypervisor' => $hv,
					'method' => $method,
					'username' => $username,
					'password' => $password,
					'host' => $host,
					'uri_override' => $uri_override,
					'log_file' => $log_file
					);

			if (!$this->update($this->tab, $fields, $conditions))
				return 'connection-edit-failed';
			else
				return true;
		}

		function del($id) {
			$id = (int)$id;

			$fields = array(
					'id'
					);

			$conditions = array(
					'id' => $id
					);

			$ret = $this->select($this->tab, $conditions, $fields);
			if (sizeof($ret) != 1)
					return 'connection-id-not-exist';
	
			$conditionsAssoc = array(
					'idConnection' => $id
					);

			if (!$this->delete($this->tabAssoc, $conditionsAssoc))
				return 'connection-assoc-cannot-delete';

			if (!$this->delete($this->tab, $conditions))
				return 'connection-cannot-delete';

			return true;
		}

		function getLibvirtObjectFromID($id, $lang = 'en') {
			$connObj = new Connection($this->_fn);
			$arr = $connObj->get($id);
			$conn = $arr[0];
			$lvObject = new Libvirt($this->_fn, $lang);
			if (!$lvObject->testConnectionUri($conn['hypervisor'], $conn['host'] ? true : false,
				$conn['method'], $conn['username'], $conn['password'], $conn['host'], false))
				return array('error' => 'connection-failed');

			$lUri = $conn['uri_override'];
			if (!$lUri)
				$lUri = $lvObject->generateConnectionUri($conn['hypervisor'], $conn['host'] ? true : false,
					$conn['method'], $conn['username'], $conn['host'], false);

			$log_file = $conn['log_file'] ? $conn['log_file'] : false;
			return new Libvirt($config, $lang, $lUri, $conn['username'], $conn['password'], $log_file);
		}

		/* RPC Methods */
		function rpc_GetUserConnections($input) {
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$ret = array();
			$ids = $this->getAllowedForUser($uId);
			for ($i = 0; $i < sizeof($ids); $i++) {
				$id = $ids[$i]['id'];
				$name = $this->getName($id);

				$ret[$id] = $this->getName($id);
			}

			return array('id' => $ret);
		}

		/* Domain RPC methods */
		function rpc_ListDomains($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$domains = $lvObj->getDomains();
			unset($lvObj);

			return $domains;
		}

		function rpc_DomainStart($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainStart($name);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainShutdown($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainShutdown($name);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainDestroy($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainDestroy($name);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainSuspend($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainSuspend($name);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainResume($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainResume($name);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainControl($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$action = $data['action'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			if ($action == 'start')
				$ret = $lvObj->domainStart($name);
			else
			if ($action == 'shutdown')
				$ret = $lvObj->domainShutdown($name);
			else
			if ($action == 'destroy')
				$ret = $lvObj->domainDestroy($name);
			else
			if ($action == 'suspend')
				$ret = $lvObj->domainSuspend($name);
			else
			if ($action == 'resume')
				$ret = $lvObj->domainResume($name);
			else
				$ret = false;

			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_DomainDumpXML($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->domainGetXml($name);
			unset($lvObj);

			return array('result' => htmlentities($ret));
		}

		/* Network RPC methods */
		function rpc_ListNetworks($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->getNetworks();
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_NetworkStart($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->setNetworkActive($name, true);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_NetworkStop($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->setNetworkActive($name, false);
			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_NetworkControl($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$action = $data['action'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			if ($action == 'start')
				$ret = $lvObj->setNetworkActive($name, true);
			else
			if ($action == 'stop')
				$ret = $lvObj->setNetworkActive($name, false);
			else
				$ret = false;

			unset($lvObj);

			return array('result' => $ret);
		}

		function rpc_NetworkDumpXML($input) {
			$data = $input['data'];
			$idConn = $data['connection'];
			$name = $data['name'];
			$apikey = $input['apikey'];

			$cu = new User($this->_fn);
			$uId = $cu->getUserIdByAPIKey($apikey);
			unset($cu);
			if (!$uId)
				return array('error' => 'invalid-api-key');

			$lvObj = $this->getLibvirtObjectFromID($idConn);
			$ret = $lvObj->getNetworkXml($name);
			unset($lvObj);

			return array('result' => htmlentities($ret));
		}
	}
?>
