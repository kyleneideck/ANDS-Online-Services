<?php
class Auth extends CI_Controller {

	public function login(){
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		//$config['authenticators'] = Array('Built in' => gCOSI_AUTH_METHOD_BUILT_IN, 'LDAP'=> gCOSI_AUTH_METHOD_LDAP, 'Shibboleth'=>gCOSI_AUTH_METHOD_SHIBBOLETH);
		//$config['default_authenticator'] = gCOSI_AUTH_METHOD_BUILT_IN;
		

		$this->CI =& get_instance();
		$data['default_authenticator'] = $this->CI->config->item('default_authenticator');
		$data['authenticators'] = $this->CI->config->item('authenticators');
		 
		
		if ($this->input->post('inputUsername') || $this->input->post('inputPassword') && !$this->user->loggedIn())
		{
			try 
			{
				if($this->user->authChallenge($this->input->post('inputUsername'), $this->input->post('inputPassword')))
				{
					redirect('/');
				}
			}
			catch (Exception $e)
			{
				$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
				$data['exception'] = $e;
			}
		}
		
		$this->load->view('login', $data);
	}
	
	public function logout(){
		// Logs the user out and redirects them to the homepage/logout confirmation screen
		$this->user->logout(); 		
	}
	
	public function setUser(){
		$sharedToken = '';
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$this->CI =& get_instance();
		$data['default_authenticator'] = $this->CI->config->item('default_authenticator');
		$data['authenticators'] = $this->CI->config->item('authenticators');
		if(isset($_SERVER['shib-shared-token'])){
			$sharedToken = $_SERVER['shib-shared-token'];
		}else{
			$data['error_message'] = "Unable to login. Shibboleth IDP was not able to authenticate the given credentials.";
			$this->load->view('login', $data);
		}

		try 
		{
			if($this->user->authChallenge($sharedToken, ''))
			{
				redirect('/auth/dashboard/');
			}
			else
			{
				$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
				$this->load->view('login', $data);
			}
		}
		catch (Exception $e)
		{
			$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
			$data['exception'] = $e;
			$this->load->view('login', $data);
		}

	}

	public function registerAffiliation($new = false){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$orgRole = $this->input->post('orgRole');
		$thisRole = $this->input->post('thisRole');
		$jsonData = array();
		$this->load->model('cosi_authentication', 'cosi');

		if($new){
			$this->cosi->createOrganisationalRole($orgRole, $thisRole);
		}

		if(in_array($orgRole, $this->user->affiliations())){
			$jsonData['status']='WARNING';
			$jsonData['message']='You are already affiliate with this organisation: '.$orgRole;
		}else{
			if($this->cosi->registerAffiliation($thisRole, $orgRole)){
				$this->user->refreshAffiliations($thisRole);
				$jsonData['status']='OK';
				$jsonData['message']='registering success';
			}else{
				$jsonData['status']='ERROR';
				$jsonData['message']='problem encountered while registering affiliation';
			}
		}
		
		//$jsonData['message'].=$thisRole. ' affiliates with '.$orgRole;
		echo json_encode($jsonData);

		//sending email
		$this->load->library('email');
		$this->email->from($this->config->item('vocab_admin_email'), 'ANDS Vocabulary Notification');
		$this->email->to($this->config->item('vocab_admin_email')); 
		$this->email->subject('New user affiliation registered');
		$message = 'Registering user '.$thisRole. ' to affiliate with '.$orgRole;
		if($new) $message.='. User created '.$orgRole;
		$this->email->message($message);	
		$this->email->send();
	}
	
	public function dashboard()
	{
		$data['title'] = 'ANDS Online Services Home';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		
		if($this->user->loggedIn()) 
		{
			if(sizeof($this->user->affiliations())>0){
				$data['hasAffiliation']=true;
			}else $data['hasAffiliation']=false;
			
			if (mod_enabled('vocab_service'))
			{
				$this->load->model('vocab_service/vocab_services','vocab');
				$data['group_vocabs']=$this->vocab->getGroupVocabs();
				//$data['owned_vocabs']=$this->vocab->getOwnedVocabs(false);
				$this->load->model('cosi_authentication', 'cosi');
				$data['available_organisations'] = $this->cosi->getAllOrganisationalRoles();
				asort($data['available_organisations']);
			}

			if (mod_enabled('registry'))
			{
				$db = $this->load->database( 'registry', TRUE );
				$this->db = $db;
				$this->load->model('data_source/data_sources','ds');
				$data['data_sources']=$this->ds->getOwnedDataSources();
			}

			$this->load->view('dashboard', $data);
		}
		else 
		{
			redirect('auth/login');
		}
	}
	
	public function printData($title, $internal_array)
	{
		if( $internal_array )
		{
			print '<b>'.$title."</b><br />\n";
			foreach($internal_array as $key => $value)
			{
				print("$key=");	
				if( is_array($value) )
				{
					foreach( $value as $subvalue )
					{
						print("$subvalue, ");
					}
				}
				else
				{
					print($value);
				}
				print "<br />\n";			
			}
		}
	}

	/* Interface for COSI built-in users to change their password from the default */
	public function change_password()
	{
		$data['title'] = 'Change Built-in Password';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();

		if (!$this->user->loggedIn() || !$this->user->authMethod() == gCOSI_AUTH_METHOD_BUILT_IN)
		{
			throw new Exception("Unable to change password unless you are logged in as a built-in COSI user!");
		}

		if ($this->config->item('authentication_class') != 'cosi_authentication')
		{
			throw new Exception("Unable to change password unless the authentication framework is COSI!");
		}

		if ($this->input->post('password'))
		{
			if ($this->input->post('password') != $this->input->post('password_confirm'))
			{
				$data['error'] = "Both passwords must match! Please try again...";
			}
			elseif (strlen($this->input->post('password')) < 6)
			{
				$data['error'] = "Password must be 6 characters or longer! Please try again...";
			}
			else
			{
				$this->load->model('cosi_authentication', 'cosi');
				$this->cosi->updatePassword($this->user->localIdentifier(), $this->input->post('password'));
				$this->session->set_flashdata('message', 'Your password has been updated. This will be effective from your next login.');
				redirect('/');
			}
		}

		$this->load->view('change_password_form', $data);
		
	}

}