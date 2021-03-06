<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LoginModel extends Render_Model
{
	public function cekLogin($username, $password)
	{
		$query = $this->db
			->select('user_id, user_nama, user_password, user_email, b.lev_nama, b.lev_id, a.user_status')
			->from('users a')->join('level b', 'b.lev_id = a.level_id')
			->where('user_status <> 3')->where('user_email', $username)
			->get();

		if ($query->num_rows() == 1) {
			$query_row = $query->row();
			$cek = $this->b_password->hash_check($password, $query_row->user_password);
			if ($cek == true) {
				return (object)['status' => 0, 'data' => $query_row];
			} else {
				return  (object)['status' => 1, 'data' => null];
			}
		} else {
			return  (object)['status' => 2, 'data' => null];
		}
	}

	//Start: method tambahan untuk reset code
	// public function getUserInfo($id)
	// {
	// 	$q = $this->db
	// 		->select('user_id,user_nama,user_password,user_email,c.lev_nama,c.lev_id, a.user_status')
	// 		->from('users a')
	// 		->join('role_users b', 'b.role_user_id = a.user_id')
	// 		->join('level c', 'c.lev_id = b.role_lev_id')
	// 		->where('a.user_id', $id)
	// 		->limit(1)
	// 		->get();
	// 	if ($this->db->affected_rows() > 0) {
	// 		$row = $q->row();
	// 		return $row;
	// 	} else {
	// 		error_log('no user found getUserInfo(' . $id . ')');
	// 		return false;
	// 	}
	// }

	public function getUserInfoByEmail($email)
	{
		return $this->db->select('user_id, user_email')->from('users')->where('user_email', $email)->where('user_status', 1)->get()->row();
	}

	public function getUserInfoByEmail1($email)
	{
		$q = $this->db
			->select('user_id,user_nama,user_password,user_email,c.lev_nama,c.lev_id, a.user_status')
			->from('users a')
			->join('role_users b', 'b.role_user_id = a.user_id')
			->join('level c', 'c.lev_id = b.role_lev_id')
			->where('a.user_email', $email)
			->limit(1)
			->get();
		if ($this->db->affected_rows() > 0) {
			$row = $q->row();
			return $row;
		}
	}

	public function insertToken($user_id)
	{
		$token = substr(sha1(rand()), 0, 30);
		$date = date('Y-m-d');

		// hapus token yang tidak digunakan
		$this->db->where("created < '$date'");
		$this->db->delete('tokens');
		$this->db->reset_query();

		$string = array(
			'token' => $token,
			'user_id' => $user_id,
			'created' => $date
		);
		$query = $this->db->insert_string('tokens', $string);
		$this->db->query($query);
		return $token . $user_id;
	}

	public function isTokenValid($token)
	{
		$tkn = substr($token, 0, 30);
		$uid = substr($token, 30);

		$q = $this->db->get_where('tokens', array(
			'tokens.token' => $tkn,
			'tokens.user_id' => $uid
		), 1);

		if ($this->db->affected_rows() > 0) {
			$row = $q->row();

			$created = $row->created;
			$createdTS = strtotime($created);
			$today = date('Y-m-d');
			$todayTS = strtotime($today);

			if ($createdTS != $todayTS) {
				return false;
			}

			return $this->db->select('user_id, user_nama, user_email')->from('users')->where('user_id', $uid)->where('user_status', 1)->get()->row();
		} else {
			return false;
		}
	}

	public function updatePassword($id_user, $new_password)
	{
		$new_password_hash = $this->b_password->bcrypt_hash($new_password);
		$this->db->where('user_id', $id_user);
		$cek = $this->db->update('users', ['user_password' => $new_password_hash]);
		return $cek;
	}

	//End: method tambahan untuk reset code
	public function removeToken($id)
	{
		$this->db->where('user_id', $id);
		return $this->db->delete('tokens');
	}
}
