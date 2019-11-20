<?php

use Restserver \Libraries\REST_Controller ; 

Class Kendaraan extends REST_Controller{
 
public function __construct(){

header('Access-Control-Allow-Origin: *');

header("Access-Control-Allow-Methods: GET, OPTIONS, POST, DELETE");

header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

parent::__construct();

$this->load->model('KendaraanModel');

$this->load->library('form_validation');
$this->load->helper(['jwt','authorization']);

}

public function index_get(){ 
    $data = $this->verify_request();
    $status = parent::HTTP_OK;
    if($data['status'] == 401){
        return $this->returnData($data['msg'], true);
    }
    return $this->returnData($this->db->get('branches')->result(), false); 
} 

public function index_post($id = null){ 
    
    $validation = $this->form_validation;
    
    $rule = $this->KendaraanModel->rules(); 
    
    if($id == null){

array_push($rule,[

'field' => 'merk',

'label' => 'merk',

'rules' => 'required'

],

[

'field' => 'licensePlate',

'label' => 'licensePlate',

'rules' => 'required|is_unique[vehicles.licensePlate]'

]

);

}

else{

array_push($rule,

[

'field' => 'licensePlate',

'label' => 'licensePlate',


'rules' => 'required'

]

);

}

$validation->set_rules($rule);

if (!$validation->run()) {

return $this->returnData($this->form_validation->error_array(), true);

}

$kendaraan = new KendaraanData();

$kendaraan->merk = $this->post('merk');

$kendaraan->type = $this->post('type'); 

$kendaraan->licensePlate = $this->post('licensePlate');

if($id == null){

$response = $this->KendaraanModel->store($kendaraan);
 

}else{

$response = $this->KendaraanModel->update($kendaraan,$id);

}

return $this->returnData($response['msg'], $response['error']);

}

public function index_delete($id = null){ 
    if($id == null){ 
        return $this->returnData('Parameter Id Tidak Ditemukan', true); 
    } 
    $response = $this->BengkelModel->destroy($id); 
    return $this->returnData($response['msg'], $response['error']); 
} 
public function returnData($msg,$error){ 
    $response['error']=$error; 
    $response['message']=$msg; 
    return $this->response($response); 
} 
private function verify_request()
{
// Get all the headers
$headers = $this->input->request_headers();
if(!empty($headers['Authorization'])){
    $header = $headers['Authorization'];
}else{
    $status = parent::HTTP_UNAUTHORIZED;
    $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
    return $response;
}
// $token = explode(" ",$header)[1];
try {
    // Validate the token
    // Successfull validation will return the decoded user data else returns false
    $data = AUTHORIZATION::validateToken($header);
    if ($data === false) {
        $status = parent::HTTP_UNAUTHORIZED;
        $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
        // $this->response($response, $status);
        // exit();
    } else {
        $response = ['status' => 200 , 'msg' => $data];
    }
    return $response;
} catch (Exception $e) {
    // Token is invalid
    // Send the unathorized access message
    $status = parent::HTTP_UNAUTHORIZED;
    $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
    return $response;
}
}
} 

Class KendaraanData{

public $merk;

public $type;

public $licensePlate;

public $created_at;

}
