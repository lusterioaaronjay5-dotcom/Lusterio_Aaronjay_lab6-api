<?php

defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Product_model extends Model
{
    protected $table = 'products';

    public function all_products()
    {
        return $this->db->table($this->table)->order_by('id', 'DESC')->get_all() ?: [];
    }

    public function find_product($id)
    {
        return $this->db->table($this->table)->where('id', $id)->get();
    }

    public function create_product($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->last_id();
    }

    public function update_product($id, $data)
    {
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function delete_product($id)
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }
}