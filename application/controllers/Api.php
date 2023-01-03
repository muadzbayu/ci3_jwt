<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/JWT.php';
require APPPATH . '/libraries/ExpiredException.php';
require APPPATH . '/libraries/BeforeValidException.php';
require APPPATH . '/libraries/SignatureInvalidException.php';
require APPPATH . '/libraries/JWK.php';

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

class Api extends RestController{
    function __construct(){
        // Construct the parent class
        parent::__construct();
    }
    
    // config jwt
    function configToken(){
        $cnf['exp'] = 3600;
        $cnf['secretkey'] = 'dontJudgeBook29';
        return $cnf;
    }

    // generate token
    public function getToken_post(){
        $exp = time() + 3600;
        $token = array(
            "iss" => "apprestservice",
            "aud" => "pengguna",
            "iat" => time(),
            "nbf" => time() + 10,
            "exp" => $exp,
            "data" => array(
                "username" => $this->input->post('username'),
                "password" => $this->input->post('password')
            )
        );

        $jwt = JWT::encode($token, $this->configToken()['secretkey'], 'HS256');
        
        $output = [
            "status" => 200,
            "message" => "Berhasil Login",
            "token" => $jwt,
            "expireAt" =>  $token['exp']
        ];

        $data = array('kode' => 200, "pesan" => "token", "data" => array("token"=>$jwt, "exp"=> $exp));
        $this->response($data, 200);
    }

    // cek token
    public function authtoken(){
        $secret_key = $this->configToken()['secretkey'];
        $token = null;
        $authHeader = $this->input->request_headers()['Authorization'];
        $arr = explode(" ", $authHeader);
        $token = $arr[1];
        if($token){
            try{
                $decoded = JWT::decode($token, new Key($this->configToken()['secretkey'], 'HS256'));
                
                if($decoded){
                    return 'benar';
                }
            }catch(\Exception $e){
                $result = array('pesan' => 'Kode Signature tidak sesuai');
                return 'salah';
            }
        }
    }

    public function authjwt(){
        if($this->authtoken() == 'salah'){
            return $this->response(array('kode' => '401', 'pesan' => 'signature tidak sesuai', 'data' => []), 401);
            die();
        }
    }

    public function users_get(){
        $this->authjwt();

        // Users from a data store e.g. database
        $users = [
            ['id' => 0, 'name' => 'bayu', 'email' => 'bayunutech@gmail.com'],
            ['id' => 1, 'name' => 'aji', 'email' => 'ajinutech@gmail.com'],
        ];

        $id = $this->get('id');

        if($id === null){
            if($users){
                $this->response($users, 200);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'No users were found'
                ], 404);
            }
        }else{
            if(array_key_exists($id, $users)){
                $this->response($users[$id], 200);
            }else{
                $this->response([
                    'status' => false,
                    'message' => 'No such user found'
                ], 404);
            }
        }
        
    }

    public function siswa_get(){
        $this->authjwt();

        $this->db->select('*');
        $data = array('data' => $this->db->get('siswa')->result());
        $this->response($data, 200);
    }

    public function siswa_post(){
        $this->authjwt();

        $isidata = array(
                    'nis'=>$this->post('nis'),
                    'namasiswa'=>$this->post('nama')
                );
                
        $this->db->insert('siswa', $isidata);
        $this->response($this->siswa_get(),200);
    }

    public function siswa_put(){
        $this->authjwt();

        $isidata = array('namasiswa'=>$this->put('namasiswa'));
        $this->db->where(array('nis'=>$this->put('nis')));
        $this->db->Update('siswa', $isidata);           
        $this->response($this->siswa_get(),200);
    }

    public function siswa_delete(){
        $this->authjwt();

        $nis = $this->input->get('nis');
        $this->db->where('nis', $nis);
        $this->db->delete('siswa');
        $this->response($this->siswa_get(), 200);
    }

}
