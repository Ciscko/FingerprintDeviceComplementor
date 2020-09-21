<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Person extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('native');
		$this->load->model('person_model','person');
		$this->load->helper('url', 'form');
	}
	public function index()
	{
		
		$this->load->library('native');
		$data = array(
			'islogged' => $this->native->get('islogged'),
			'id' => $this->native->get('id'),
			'username' => $this->native->get('username'),
			'module' => 'candidate'
		);
		$this->load->view('header', $data);
		$this->load->view('login');
	}

	
	
	public function candidates()
	{
		if($this->native->get('islogged')){
			$data = array(
				'islogged' => $this->native->get('islogged'),
				'id' => $this->native->get('id'),
				'username' => $this->native->get('username'),
				'module' => 'candidate'
			);
			$this->load->view('header', $data);
	   
		$this->load->view('person_view');
		}
		else{
			$this->index();
		}
	}

	public function ajax_list()
	{
		$this->load->helper('url');
		$list = $this->person->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $person) {
			$no++;
			$row = array();
			$row[] = '<div class="badge badge-info">'.$person->fingerprintID.'</div>';
			$row[] = $person->regno;
			$row[] = $person->department;
			$row[] = $person->year;
			$row[] = $person->firstName;
			$row[] = $person->lastName;
			$row[] = $person->gender;
			$row[] = mb_strimwidth($person->address, 0, 15, '...');
			$row[] = $person->dob;
			$row[] = $person->exam;
			if($person->photo)
				$row[] = '<a onclick="get_photo('."'".$person->photo."'".')"><img src="'.base_url('upload/'.$person->photo).'"  class="img-thumbnail thumb"/></a>';
			else
				$row[] = '(No photo)';

			//add html for action
			$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_person('."'".$person->id."'".')"><i class="glyphicon glyphicon-pencil"></i></a>
				  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Hapus" onclick="delete_person('."'".$person->id."'".')"><i class="glyphicon glyphicon-trash"></i></a>';
		
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->person->count_all(),
						"recordsFiltered" => $this->person->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function ajax_edit($id)
	{
		$data = $this->person->get_by_id($id);
		$data->dob = ($data->dob == '0000-00-00') ? '' : $data->dob; // if 0000-00-00 set tu empty for datepicker compatibility
		echo json_encode($data);
	}

	public function ajax_add()
	{
		$this->_validate();
		
		$data = array(
			    'fingerprintID' => $this->input->post('fingerprintID'),
				'regno' => $this->input->post('regno'),
				'department' => $this->input->post('department'),
				'year' => $this->input->post('year'),
				'firstName' => $this->input->post('firstName'),
				'lastName' => $this->input->post('lastName'),
				'gender' => $this->input->post('gender'),
				'address' => $this->input->post('address'),
				'dob' => $this->input->post('dob'),
				'exam' => $this->input->post('exam')
			);

		if(!empty($_FILES['photo']['name']))
		{
			$upload = $this->_do_upload();
			$data['photo'] = $upload;
		}

		$insert = $this->person->save($data);

		echo json_encode(array("status" => TRUE));
	}

	public function search_candidate($finger)
	{
			$candidate = $this->person->get_candidate($finger);
			if(empty($candidate )){
				$response = array(
					'status' => false,
					'data' => 'No such id'
				);
			}
			else{
				$response = array(
					'status' => true,
					'data' =>  $candidate
				);
			}	
		echo json_encode($response);
	}

	
	

	public function ajax_update()
	{
		$this->_validate();
		$data = array(
			'fingerprintID' => $this->input->post('fingerprintID'),
			'regno' => $this->input->post('regno'),
			'department' => $this->input->post('department'),
			'year' => $this->input->post('year'),
			'firstName' => $this->input->post('firstName'),
			'lastName' => $this->input->post('lastName'),
			'gender' => $this->input->post('gender'),
			'address' => $this->input->post('address'),
			'dob' => $this->input->post('dob'),
			'exam' => $this->input->post('exam')
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
			$person = $this->person->get_by_id($this->input->post('id'));
			if(file_exists('upload/'.$person->photo) && $person->photo)
				unlink('upload/'.$person->photo);

			$data['photo'] = $upload;
		}

		$this->person->update(array('id' => $this->input->post('id')), $data);
		echo json_encode(array("status" => TRUE));
	}

	public function ajax_delete($id)
	{
		//delete file
		$person = $this->person->get_by_id($id);
		if(file_exists('upload/'.$person->photo) && $person->photo)
			unlink('upload/'.$person->photo);
		
		$this->person->delete_by_id($id);
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
		if($this->input->post('fingerprintID') == '')
		{
			$data['inputerror'][] = 'fingerprintID';
			$data['error_string'][] = 'Fingerprint ID is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('regno') == '')
		{
			$data['inputerror'][] = 'regno';
			$data['error_string'][] = 'Registration Number is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('department') == '')
		{
			$data['inputerror'][] = 'department';
			$data['error_string'][] = 'Department is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('year') == '')
		{
			$data['inputerror'][] = 'year';
			$data['error_string'][] = 'Year of study is required';
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
		if($this->input->post('exam') == '')
		{
			$data['inputerror'][] = 'exam';
			$data['error_string'][] = 'Exam candidature is required';
			$data['status'] = FALSE;
		}
		if($this->input->post('address') == '')
		{
			$data['inputerror'][] = 'address';
			$data['error_string'][] = 'Addess is required';
			$data['status'] = FALSE;
		}

		if($data['status'] === FALSE)
		{
			echo json_encode($data);
			exit();
		}
	}

}
