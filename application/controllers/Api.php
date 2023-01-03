<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Api extends RestController{
    function __construct(){
        // Construct the parent class
        parent::__construct();
    }

    public function users_get(){
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
}
