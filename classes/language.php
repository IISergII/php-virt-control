<?php
	class Language {
		var $trans = array();
		private $lang_set = false;

		function Language($lang=false) {
			$this->initlang();

			if ($lang && File_Exists('lang/'.$lang.'.php')) {
				include('lang/'.$lang.'.php');

				$this->lang_set = true;
				$this->trans = $trans;
			}
		}

		function get($ident) {
			return array_key_exists($ident, $this->trans) ? $this->trans[$ident] : false;
		}

		function initlang() {
			/* Default to English however implemented in here as well for case lang directory is missing */
			$trans = array(
					'title_vmc' => 'Virtual Machine Controller',
					'info'	 => 'Information',
					'for_php' => 'for PHP',
					'conn_details' => 'Connection details',
					'conn_uri' => 'Connection URI',
					'conn_encrypted' => 'Encrypted',
					'conn_secure' => 'Secure',
					'hypervisor_limit' => 'Hypervisor limit',
					'hostname' => 'Hostname',
					'password' => 'Password',
					'Yes' => 'Yes',
					'No' => 'No',
					'host_details' => 'Host details',
					'model' => 'Model',
					'pcpus' => 'CPUs/cores',
					'cpu_speed' => 'CPU Speed',
					'modinfo' => 'libvirt PHP module information',
					'version' => 'Version',
					'website' => 'Website',
					'pool_not_running' => 'Not running',
					'pool_building' => 'Building pool',
					'pool_running' => 'Running',
					'pool_running_deg' => 'Running degraded',
					'pool_running_inac' => 'Running but inaccessible',
					'unknown' => 'Unknown',
					'dom_running' => 'running',
					'dom_nostate' => 'no state',
					'dom_blocked' => 'blocked',
					'dom_paused' =>  'paused',
					'dom_shutdown' => 'shutdown',
					'dom_shutoff' => 'shutoff',
					'dom_crashed' => 'crashed',
					'cur_phys_size' => 'Current physical size',
					'diskless' => 'diskless',
					'changes' => 'Changes',
					'btn_apply' => 'Apply changes',
					'btn_discard' => 'Discard changes',
					'ask_apply' => 'Do you really want to apply your changes?',
					'ask_discard' => 'Do you really want to discard your changes?',
					'general' => 'General',
					'description' => 'Description',
					'vm_details' => 'Machine details',
					'host_pcpu_info' => 'Host processor information',
					'max_per_guest' => 'Max. per guest',
					'vm_vcpu_info' => 'Machine processor information',
					'host_mem_info' => 'Host memory information',
					'vm_mem_info' => 'Machine memory information',
					'total_mem' => 'Total memory',
					'mem_alloc_cur' => 'Current allocation',
					'mem_alloc_max' => 'Max. allocation',
					'vm_boot_opts' => 'Virtual machine boot options',
					'vm_boot_dev1' => 'First boot device',
					'vm_boot_dev2' => 'Second boot device',
					'vm_boot_hdd' => 'Hard-drive',
					'vm_boot_cd' => 'CD-ROM',
					'vm_boot_fda' => 'Floppy',
					'vm_boot_pxe' => 'Network boot (PXE)',
					'vm_boot_none' => 'none',
					'vm_disk_num' => 'Number of disks',
					'vm_disk_storage' => 'Storage',
					'vm_disk_type' => 'Driver type',
					'vm_disk_dev' => 'Domain device',
					'vm_disk_capacity' => 'Capacity',
					'vm_disk_allocation' => 'Allocation',
					'vm_disk_physical' => 'Physical disk size',
					'vm_disk_remove' => 'Remove disk',
					'vm_disk_add' => 'Add new disk',
					'vm_disk_image' => 'Disk image',
					'vm_disk_location' => 'Disk location',
					'vm_disk_details' => 'Machine disk device details',
					'vm_disk_askdel' => 'Are you sure you want to delete disk \'+disk+\' from the guest?',
					'vm_disk_askadd' => 'Are you sure you want to add disk to the guest?',
					'vm_network_title' => 'Machine network devices',
					'vm_network_num' => 'Number of NICs',
					'vm_network_nic' => 'Network interface card ',
					'vm_network_mac' => 'MAC Address',
					'vm_network_net' => 'Network',
					'vm_network_type' => 'NIC Type',
					'vm_network_add' => 'Add a new network interface',
					'vm_network_del' => 'Remove network interface',
					'vm_network_askadd' => 'Do you really want to add a new network interface card ?',
					'vm_network_askdel' => 'Are you sure you want to delete interface with MAC address \'+mac+\' from the guest?',
					'vm_title' => 'Virtual machine',
					'vm_multimedia_title' => 'Machine multimedia devices',
					'vm_multimedia_console' => 'Console',
					'vm_multimedia_input' => 'Input device',
					'vm_multimedia_graphics' => 'Graphics device',
					'vm_multimedia_video' => 'Video device',
					'details_readonly' => 'None (this page is currently read-only)',
					'host_devices_title' => 'Machine host devices',
					'host_devices' => 'Host devices',
					'settings' => 'Settings',
					'interval_sec' => 'Interval (sec)',
					'change' => 'Change',
					'menu_overview' => 'Overview',
					'menu_processor' => 'Processor',
					'menu_memory' => 'Memory',
					'menu_boot' => 'Boot options',
					'menu_disk' => 'Disk devices',
					'menu_network' => 'Network devices',
					'menu_multimedia' => 'Multimedia devices',
					'menu_hostdev' => 'Host devices',
					'menu_screenshot' => 'Screenshot',
					'info_msg' => '<p>This is the virtual machine controller tool written in PHP language.'.
							'You can manage virtual machines (guests) on your machines using this web-based '.
							'controlling interface. For the navigation please use the upper menu and select '.
							'the domain from the <i>Domain list</i> link to see the virtual machines available '.
							'on the current machine. You can also see the information about the hypervisor '.
							'connection, host machine and libvirt PHP module (used by this system) on the '.
							'<i>Information</i> page.</p> '.
							'<p>The hypervisor on the machine running Apache with PHP is being probed automatically '.
							'if applicable however you can override the definition to connect to any other hypervisor '.
							'on remote machine. To achieve this you need to select a connection and change the host '.
							'using the form below. If you experience any issues (e.g. not working connectivity to '.
							'SSH-based remote host) please make sure you\'re having all the prerequisites met. For '.
							'more reference please check <a href="http://libvirt.org/auth.html" target="_blank">libvirt '.
							'authentication documentation.</a></p>',
					'conns'  => 'Connections',
					'connname' => 'Connection name',
					'hypervisor' => 'Hypervisor',
					'host_type' => 'Host type',
					'type_local' => 'Local',
					'type_remote' => 'Remote',
					'host' => 'Host',
					'logfile' => 'Log file',
					'actions' => 'Actions',
					'log_opts' => 'Logging options',
					'host_opts' => 'Host options',
					'save_conn' => 'Save connection',
					'connect_new' => 'Connect to the new host',
					'change_conn' => 'Change host connection',
					'conn_method' => 'Connection method',
					'user' => 'User name',
					'conn_setup' => 'Setup connection',
					'connect' => 'Connect',
					'conn_remove' => 'Remove connection',
					'empty_disable_log' => 'Leave empty to disable logging',
					'empty_disable_save' => 'Leave empty not to save connection',
					'conn_none' => 'No connection defined',
					'hostdev_none' => 'None',
					'name'   => 'Name',
					'arch'   => 'Architecture',
					'vcpus'  => 'vCPUs',
					'mem'    => 'Memory',
					'disk/s' => 'Disk(s)',
					'nics'   => 'NICs',
					'state'  => 'State',
					'id'	 => 'ID',
					'msg'	 => 'Message',
					'dom_start' => 'Start domain',
					'dom_stop'  => 'Stop domain',
					'dom_destroy' => 'Destroy domain',
					'dom_dumpxml' => 'Dump domain XML',
					'dom_editxml' => 'Edit domain XML',
					'dom_xmldesc' => 'Domain XML description',
					'dom_undefine' => 'Undefine domain',
					'dom_start_ok' => 'Domain has been started successfully',
					'dom_start_err' => 'Error while starting domain',
					'dom_shutdown_ok' => 'Command to shutdown domain sent successfully',
					'dom_shutdown_err' => 'Error while sending shutdown command',
					'dom_destroy_ok' => 'Domain has been destroyed successfully',
					'dom_destroy_err' => 'Error while destroying domain',
					'dom_undefine_ok' => 'Domain has been undefined successfully',
					'dom_undefine_err' => 'Error while undefining domain',
					'dom_define_changed' => 'Domain definition has been changed',
					'dom_define_change_err' => 'Cannot change domain definition',
					'dom_undefine_question' => 'You can delete the domain with or without disks assigned to the domain. If you select option to delete disks only the domain disks will get deleted and the CD-ROM images will be intact. Are you sure you want to delete (undefine) this domain?',
					'delete' => 'Delete',
					'delete_with_disks' => 'Delete with disks',
					'changed_uri' => 'Changed connection URI to',
					'click_reload' => 'Click here to reload and connect using new URI',
					'conn_saved' => 'Connection has been saved to the list.',
					'conn_failed' => 'Connection has failed',
					'domain_list' => 'Domain list',
					'dom_screenshot' => 'Screenshot',
					'dom_none' => 'No valid domain defined',
					'main_menu' => 'Main menu',
					'cannot_connect' => 'Cannot connect to hypervisor. Please change connection information.',
					'language' => 'Language',
					'usig-ssh-auth' => 'Using SSH authentication',
					'info-apache-key-copy' => 'There\'s an utility called <b>apache-key-copy</b> included to this web-application (can be found in tools subdir)'.
								'that\'s useful to setup SSH keys for password-less SSH connection. The SSH connection '.
								'transport <b>does not</b> support passing credentials to the SSH process and that\'s why '.
								'password-less SSH connection settings is necesary. <b>Apache-key-copy</b> utility have to '.
								'be run from shell by the system administrator (root account). This application find the home '.
								'directory for the apache user (you will most likely need to change it if you are using a '.
								'different user name to run Apache/php) and creates the hidden SSH settings directory. Then '.
								'a SSH key is being generated (if it doesn\'t exist yet) and copied to the destination machine.',
					'create-new-vm' => 'Create a new VM',
					'install-image' => 'Install image',
					'create-vm' => 'Create VM',
					'clock-offset' => 'Clock offset',
					'features' => 'Features',
					'setup' => 'Setup',
					'nic' => 'network',
					'disk' => 'disk',
					'persistent' => 'Set as persistent',
					'new-vm-disk' => 'VM Disk',
					'new-vm-existing' => 'Use existing disk image',
					'new-vm-create' => 'Create new disk image',
					'vm-disk-size' => 'New disk size',
					);

			$this->trans = $trans;
		}
	}
?>
