<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model','user');
		$this->load->helper('url', 'form');
		$this->load->library('native');
	}
	
	public function index()
	{
		if($this->native->get('islogged')){
			$data = array(
				'islogged' => $this->native->get('islogged'),
				'id' => $this->native->get('id'),
				'username' => $this->native->get('username'),
				'module' => 'user'
			);
		$this->load->view('header', $data);
		$this->load->view('user_view');
		}
		else{
			redirect('Person');
		}
	}

    public function login(){
		$data = array();
        if($this->input->post('username') == ''){
            $data['status'] = false;
        }
        if($this->input->post('pwd') == ''){
            $data['status'] = false;
        }
        else{
            $pwd  = $this->input->post('pwd');
            $username = $this->input->post('username');
            $user_data = $this->user->get_user($pwd, $username);
            if($user_data['id'] > 0){
                $data['status'] = true;
					$this->native->set('id',$user_data['id']);
					$this->native->set('islogged',true);
                    $this->native->set('username', $this->input->post('username'));
                
            }
            else{
                $data['status'] = false;
            }
        }
        echo json_encode($data);
	}

	public function logout(){
		$user_data_got = array(
			'id' ,
			'username',
			'islogged' 
		);
		foreach ($user_data_got as $key)  {
			$this->native->delete($key);
		}
		$this->index();
	 }
	public function ajax_list()
	{
		$this->load->helper('url');
		$list = $this->user->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $user) {
			$no++;
            $row = array();
            $row[] = $user->id;
            $row[] = $user->firstName;
            $row[] = $user->lastName;
            $row[] = $user->email;
            $row[] = $user->gender;
            $row[] = $user->username;
           // $row[] = mb_strimwidth(md5($user->password), 0, 10, '...');
            $row[] = $user->auth;
			$row[] = $user->phone;
            $row[] = $user->dob;
            $row[] = $user->department;
			if($user->photo)
				$row[] = '<a onclick="get_photo('."'".$user->photo."'".')"><img src="'.base_url('upload/'.$user->photo).'"  class="img-thumbnail thumb"/></a>';
			else
				$row[] = '(No photo)';

			//add html for action
			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_user('."'".$user->id."'".')"><i class="glyphicon glyphicon-pencil"></i></a>
				  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_user('."'".$user->id."'".')"><i class="glyphicon glyphicon-trash"></i></a>';
		
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->user->count_all(),
						"recordsFiltered" => $this->user->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

    public function check_admin(){
        if($this->native->get('islogged')){
            $user = $this->user->get_by_id($this->native->get('id'));
            echo json_encode($user);
        }
    } 
    
    public function ajax_edit($id)
	{
		$data = $this->user->get_by_id($id);
		$data->dob = ($data->dob == '0000-00-00') ? '' : $data->dob; // if 0000-00-00 set tu empty for datepicker compatibility
		echo json_encode($data);
	}

	public function ajax_add()
	{
		$this->_validate();
		
		$data = array(
				'email' => $this->input->post('email'),
				'firstName' => $this->input->post('firstName'),
				'lastName' => $this->input->post('lastName'),
				'gender' => $this->input->post('gender'),
				'phone' => $this->input->post('phone'),
                'dob' => $this->input->post('dob'),
                'department' => $this->input->post('department'),
                'username' => $this->input->post('username'),
                'auth' => $this->input->post('auth'),
                'password' => $this->input->post('password')
			);

		if(!empty($_FILES['photo']['name']))
		{
			$upload = $this->_do_upload();
			$data['photo'] = $upload;
		}

		$insert = $this->user->save($data);

		echo json_encode(array("status" => TRUE));
	}

	public function ajax_update()
	{
		$this->_validate();
		$data = array(
			    'email' => $this->input->post('email'),
				'firstName' => $this->input->post('firstName'),
				'lastName' => $this->input->post('lastName'),
				'gender' => $this->input->post('gender'),
				'phone' => $this->input->post('phone'),
                'dob' => $this->input->post('dob'),
                'department' => $this->input->post('department'),
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'auth' => $this->input->post('auth')
                
		);

		if($this->input->post('remove_photo')) // if remove photo checked
		{
			if(file_exists('upload/'.$this->input->post('remove_photo')) && $this->input->post('remove_photo'))
				unlink('upload/'.$this->input->post('remove_photo'));
			$data['photo'] = '';
		}

		if(!empty($_FILES['photo']['name']))
		{
			$upload = $this->_do_upload();
			
			//delete file
			$user = $this->user->get_by_id($this->input->post('id'));
			if(file_exists('upload/'.$user->photo) && $user->photo)
				unlink('upload/'.$user->photo);

			$data['photo'] = $upload;
		}

		$this->user->update(array('id' => $this->input->post('id')), $data);
		echo json_encode(array("status" => TRUE));
	}

	public function ajax_delete($id)
	{
		//delete file
		$user = $this->user->get_by_id($id);
		if(file_exists('upload/'.$user->photo) && $user->photo)
			unlink('upload/'.$user->photo);
		
		$this->user->delete_by_id($id);
		echo json_encode(array("status" => TRUE));
	}

	private function _do_upload()
	{
		$this->load->helper('url', 'form');
		$config['upload_path']          = 'upload/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 1000000; //set max size allowed in Kilobyte
        $config['max_width']            = 100000; // set max width image allowed
        $config['max_height']           = 100000; // set max height allowed
        $config['file_name']            = round(microtime(true) * 1000); //just milisecond timestamp fot unique name

        $this->load->library('upload', $config);

        if(!$this->upload->do_upload('photo')) //upload and validate
        {
            $data['inputerror'][] = 'photo';
			$data['error_string'][] = 'Upload error: '.$this->upload->display_errors('',''); //show ajax error
			$data['status'] = FALSE;
			echo json_encode($data);
			exit();
		}
		return $this->upload->data('file_name');
	}

	private function _validate()
	{
		$data = array();
		$data['error_string'] = array();
		$data['inputerror'] = array();
		$data['status'] = TRUE;

		if($this->input->post('firstName') == '')
		{
			$data['inputerror'][] = 'firstName';
			$data['error_string'][] = 'First name is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('email') == '')
		{
			$data['inputerror'][] = 'email';
			$data['error_string'][] = 'Email is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('auth') == '')
		{
			$data['inputerror'][] = 'auth';
			$data['error_string'][] = 'Auth-Level is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('department') == '')
		{
			$data['inputerror'][] = 'department';
			$data['error_string'][] = 'Department is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('username') == '')
		{
			$data['inputerror'][] = 'username';
			$data['error_string'][] = 'Username is required';
			$data['status'] = FALSE;
        }
        if($this->input->post('password') == '')
		{
			$data['inputerror'][] = 'password';
			$data['error_string'][] = 'Password is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('lastName') == '')
		{
			$data['inputerror'][] = 'lastName';
			$data['error_string'][] = 'Last name is required';
			$data['status'] = FALSE;
		}

		if($this->input->post('dob') == '')
		{
			$data['inputerror'][] = 'dob';
			$data['error_string'][] = 'Date of Birth is required';
			$data['status'] = FALSE;
		}

		if($this->input->post('gender') == '')
		{
			$data['inputerror'][] = 'gender';
			$data['error_string'][] = 'Please select gender';
			$data['status'] = FALSE;
		}
		
		if($this->input->post('phone') == '')
		{
			$data['inputerror'][] = 'phone';
			$data['error_string'][] = 'Phone is required';
			$data['status'] = FALSE;
		}

		if($data['status'] === FALSE)
		{
			echo json_encode($data);
			exit();
		}
	}

}
