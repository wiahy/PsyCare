<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

	public function __construct(){ 

		parent::__construct();

        // $this->load->model('M_login');
        // $this->load->model('M_register');
        $this->load->library('form_validation');
	}

    public function index(){

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ( $this->form_validation->run() == false){

            $data['title'] = 'Login Page';

            $this->load->view('Template/auth_header', $data);
            $this->load->view('V_login');
            $this->load->view('Template/auth_footer', $data);

        } else {
            $this->login_check();
        }
    }

    private function login_check(){
        
        $email= $this->input->post('email');
        $password= $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    if ($user['role_id'] == 1) {
                        $data = [
                            'email' => $user['email']
                        ];
                        $this->session->set_userdata($data);
                        redirect('Dashboard');
                    } else {
                        redirect('Home');
                    }
                } else {
                    $this->session->set_flashdata('message', 
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                        <strong>Galat!</strong> Wrong Password.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>');
                    redirect('Auth');
                }
            } else {
                $this->session->set_flashdata('message', 
                '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                    <strong>Galat!</strong> Your Account Unactive.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>');
                redirect('Auth');
            }
        } else {
            $this->session->set_flashdata('message', 
            '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                <strong>Galat!</strong> Email Unregistered.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>');
            redirect('Auth');
        }
    }
	
	public function register(){
        
		
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[8]|matches[password2]',
            ['matches' => 'Password not Match!',
             'min_length' => 'Password is Short']);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password]');

        if ( $this->form_validation->run() == false ) {

            $data['title'] = 'Registration';

            $this->load->view('Template/auth_header', $data);
            $this->load->view('V_register');
            $this->load->view('Template/auth_footer');

        } else {
            $data = [
                'name' => $this->input->post('name', true),
                'email' => $this->input->post('email', true),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'date_time' => time(),
                'role_id' => $this->input->post('as', true),
                'is_active' => 1
            ];

            $this->db->insert('user', $data);
            $this->session->set_flashdata('message', 
                '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                    <strong>Success!</strong> Your Account is created
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>');
            redirect('Auth');
        }
	}

    public function logout(){
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        redirect('Auth');
    }

    public function forgot_pass(){
        $this->load->view('Template/auth_header');
        $this->load->view('V_forgot_pass');
        $this->load->view('Template/auth_footer');
    }
}