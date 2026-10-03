<?php

defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class User_model extends Model
{
    protected $table = 'users';

    public function find_by_email($email)
    {
        return $this->db->table($this->table)->where('email', $email)->get();
    }

    public function find_by_username($username)
    {
        return $this->db->table($this->table)->where('username', $username)->get();
    }

    public function create_user($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }
}