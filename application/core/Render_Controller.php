<?php

// use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

defined('BASEPATH') or exit('No direct script access allowed');

class Render_Controller extends CI_Controller
{
	protected $default_template;
	protected $title;
	protected $title_show = true;
	protected $template_type;
	protected $page_setting;
	protected $page_nav;
	protected $app_name;
	protected $copyright;
	protected $breadcrumb_show = true;
	protected $breadcrumb_1;
	protected $breadcrumb_1_url;
	protected $breadcrumb_2;
	protected $breadcrumb_2_url;
	protected $breadcrumb_3;
	protected $breadcrumb_3_url;
	protected $breadcrumb_4;
	protected $breadcrumb_4_url;
	protected $content;

	protected $navigation = array();
	protected $navigation_front = null;
	protected $data = array();
	protected $plugins = array();
	private   $plugin_scripts = array();
	private   $plugin_styles = array();
	protected $debug = true;
	protected $photo_path = './files/';
	protected $navigation_type = 'admin';

	// key value
	protected $key_product_head = 'product';
	protected $key_product_head2 = 'product2';
	protected $key_testimoni_head = 'testimoni';
	protected $key_offer_head = 'offer';
	protected $key_offer_body = 'offer_decritpion';
	protected $key_offer_head2 = 'offer2';
	protected $key_offer_body2 = 'offer_decritpion2';
	// home
	protected $key_logo = 'logo';
	protected $key_footer_descritpion = 'footer_descritpion';

	protected $key_footer_contact = 'footer_contact';
	protected $key_footer_list_head = 'footer_list_head';
	protected $key_footer_copyright = 'footer_copyright';

	// menu_nav cache
	protected $cache_menu_nav = 'menu_nav';
	protected $cache_menu_nav_side = 'menu_nav_side';
	protected $cache_sosmed = 'sosmed';
	protected $cache_list_item = 'list_item';
	protected function preRender()
	{
	}

	protected function render404()
	{
		$this->default_template = 'templates/dashboard';
		$this->load->library('plugin');
		$this->load->helper('url');
		// Page config:
		$this->title = 'Error 404';
		$this->title_show = false;
		$this->content = 'err404';
		$this->plugins = [];
		$this->output->set_status_header('404');
		// Commit render:
		$this->render();
		// exit();
	}

	protected function render($template = NULL)
	{
		$this->preRender();
		$this->loadPlugins();

		if ($template == NULL) {
			$template = $this->default_template;
		}

		$this->navigation_type_front_array = [];
		if ($this->navigation_type == 'front') {
			$this->navigation_type_front_array = $this->getNavArray();
		}

		$navigation = [];
		switch ($this->navigation_type) {
			case 'admin':
				$navigation = $this->navigationHtml($this->default->menu());
				break;
			case 'front':
				$navigation = $this->navFront();
				break;
		}

		$navigation2 = [];
		switch ($this->navigation_type) {
			case 'front':
				$navigation2 = $this->navFront2();
				break;
		}

		$data = array(
			// Application
			'template_type' 	=> $this->template_type,
			'page_setting' 		=> $this->page_setting,
			'page_nav' 		=> $this->page_nav,
			'app_name' 			=> $this->app_name,
			'copyright' 		=> $this->copyright,


			// Breadcrumb
			'breadcrumb_show' 	=> $this->breadcrumb_show,
			'breadcrumb_1' 		=> $this->breadcrumb_1,
			'breadcrumb_1' 		=> $this->breadcrumb_1,
			'breadcrumb_2' 		=> $this->breadcrumb_2,
			'breadcrumb_3' 		=> $this->breadcrumb_3,
			'breadcrumb_4' 		=> $this->breadcrumb_4,
			'breadcrumb_1_url' 	=> $this->breadcrumb_1_url,
			'breadcrumb_2_url' 	=> $this->breadcrumb_2_url,
			'breadcrumb_3_url' 	=> $this->breadcrumb_3_url,
			'breadcrumb_4_url' 	=> $this->breadcrumb_4_url,

			// Content
			'plugin_styles' 	=> $this->plugin_styles,
			'plugin_scripts' 	=> $this->plugin_scripts,
			'title' 				=> $this->title,
			'title_show' 			=> $this->title_show,
			'navigation' 			=> $navigation,
			'navigation2' 			=> $navigation2,
			'content' 				=> $this->content,
			'navigation_array' => $this->navigation
		);

		// frontend
		if ($this->navigation_type == 'front') {
			// // list list item
			$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
			// if (!$list_item = $this->cache->get($this->cache_list_item)) {
			// 	$list_item = $this->db->select('link, name')
			// 		->from('home_footer_list')->where('status', 1)
			// 		->get()->result_array();
			// 	$this->cache->save($this->cache_list_item, $list_item);
			// }

			// list sosmed
			if (!$sosmed = $this->cache->get($this->cache_sosmed)) {
				$sosmed = $this->db->select('icon, link, name')
					->from('home_sosmed')->where('status', 1)
					->get()->result_array();
				$this->cache->save($this->cache_sosmed, $sosmed);
			}

			$data['front'] = [
				// 'logo' => $this->key_get($this->key_logo),
				// 'list_head' => $this->key_get($this->key_footer_list_head),
				// 'contact' => $this->key_get($this->key_footer_contact),
				// 'copyright' => $this->key_get($this->key_footer_copyright),
				// 'description' => $this->key_get($this->key_footer_descritpion),
				// 'list_item' => $list_item,
				'sosmed' => $sosmed,
			];
		}

		$data = array_merge($data, $this->data);
		$this->load->view($template, $data);
	}

	protected function output_json($data, $code = null)
	{
		$code = $code == null ? 200 : $code;
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($data));
		$this->output->set_status_header($code);
	}

	private function loadPlugins()
	{
		if (empty($this->plugins)) return;

		$result 				= $this->plugin->load_plugins($this->plugins);
		$this->plugin_styles 	= $result['styles'];
		$this->plugin_scripts 	= $result['scripts'];
	}

	private function navigationHtml($navigation)
	{
		$menu_header = '<nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">';
		$menu_body = '';
		$menu_footer = '	</ul></nav>';
		$button_logout = '<li class="nav-item btn-logout"> <a href="#" class="nav-link "> <i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p> </a> </li>';
		$main_menu = $this->navigationToArray($navigation);
		foreach ($main_menu as $menu) {
			$menu_open = $menu['active_sub'] ? ' menu-open' : '';
			$menu_active = $menu['active'] ? ' active' : '';
			$menu_nama = $menu['menu_nama'];
			$menu_icon = $menu['menu_icon'];
			$menu_url = base_url($menu['menu_url']);
			$menu_have_sub = $menu['sub_menu'] ? '<i class="right fas fa-angle-left"></i>' : '';
			$sub_menu_header = '<ul class="nav nav-treeview">';
			$sub_menu_body = '';
			$sub_menu_footer = '</ul>';
			foreach ($menu['sub_menu'] as $sub_menu) {
				$sub_menu_active = $sub_menu['active'] ? ' active' : '';
				$sub_menu_nama = $sub_menu['menu_nama'];
				$sub_menu_url = base_url($sub_menu['menu_url']);
				$sub_menu_body .= '
					<li class="nav-item">
						<a href="' . $sub_menu_url . '" class="nav-link ' . $sub_menu_active . '">
							<i class="far fa-circle nav-icon"></i>
							<p>' . $sub_menu_nama . '</p>
						</a>
					</li>
				';
			}

			$sub_menu_html = $menu['sub_menu'] ? $sub_menu_header . $sub_menu_body . $sub_menu_footer : '';

			$menu_body .= '
				<li class="nav-item' . $menu_open . '">
					<a href="' . $menu_url . '" class="nav-link ' . $menu_active . '">
						<i class="nav-icon ' . $menu_icon . '"></i>
						<p>
						' . $menu_nama . '
						' . $menu_have_sub . '
						</p>
						</a>
						' . $sub_menu_html . '
				</li>
			';
		}
		$result = $menu_header . $menu_body . $button_logout .  $menu_footer;
		return $result;
	}

	private function navigationToArray($menu)
	{
		$main_menu = [];
		$change_password = [
			'menu_id' => null,
			'menu_menu_id' => "0",
			'menu_nama' => "Ganti Password",
			'menu_icon' => "fa fa-key",
			'menu_url' => "pengaturan/password",
		];

		foreach (array_merge($menu, [$change_password]) as $nav) {
			$main_menu_active = in_array($nav['menu_nama'], $this->navigation);
			$sub_menu_list = $this->default->sub_menu($nav['menu_id']);
			$sub_menu_in_active = false;
			$sub_menu_row = [];
			if ($sub_menu_list) {
				foreach ($sub_menu_list as $row) {
					$sub_menu_cek_aktif = in_array($row['menu_nama'], $this->navigation);;
					$sub_menu_row[] = array_merge($row, [
						'active' => $sub_menu_cek_aktif,
					]);
					if ($sub_menu_cek_aktif) {
						$sub_menu_in_active = true;
					}
				}
			}

			$main_menu[] = array_merge(
				$nav,
				[
					'active' => (bool) ($main_menu_active || $sub_menu_in_active),
					'active_sub' => (bool) $sub_menu_in_active,
					'sub_menu' => $sub_menu_row
				]
			);
		}
		return $main_menu;
	}

	public function create_pdf(array $data)
	{
		if (!isset($data['html'])) {
			$data['html'] = '';
		}

		if (!isset($data['orientation'])) {
			$data['orientation'] = 'potrait';
		}

		if (!isset($data['paper_size'])) {
			$data['paper_size'] = 'A4';
		}

		if (!isset($data['doc_name'])) {
			$data['doc_name'] = 'document';
		}

		$dompdf = new Dompdf\Dompdf();
		// instantiate and use the dompdf class
		$style = '
		<style>
		body {
			margin: 0;
			font-family: \"Source Sans Pro\",-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,\"Helvetica Neue\",Arial,sans-serif,\"Apple Color Emoji\",\"Segoe UI Emoji\",\"Segoe UI Symbol\";
			align-text:center;
		}
		table {
			font-family: Arial, Helvetica, sans-serif;
			border-collapse: collapse;
			width: 100%;
		}

		table td, table th {
			border: 1px solid #ddd;
			padding: 8px;
		}

		table tr:nth-child(even){background-color: #f2f2f2;}

		table tr:hover {background-color: #ddd;}

		table th {
			padding-top: 12px;
			padding-bottom: 12px;
			text-align: left;
			background-color: #93C5FD;
			color: black;
		}</style>';

		$html = "{$style}<body> {$data['html']} </body>";
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($data['paper_size'], $data['orientation']);

		// Render the HTML as PDF
		$dompdf->render();

		$dompdf->set_option('defaultMediaType', 'all');
		$dompdf->set_option('isFontSubsettingEnabled', true);

		// Output the generated PDF to Browser
		$dompdf->stream("{$data['doc_name']}.pdf");
	}

	public function send_email($email = null, $content = null, $subject = null)
	{
		$config = array(
			'protocol' => 'POP3',
			'smtp_host' => '',
			'smtp_port' => 995,
			'smtp_user' => '',
			'smtp_pass' => '',
			'mailtype'  => 'html',
			'charset'   => 'iso-8859-1'
		);

		//Load email library
		$this->load->library('email', $config);

		// $this->email->attach($gambar);

		$this->email->set_newline("\r\n");

		$this->email->from('admin@kap.komunitashalal.com', "Audit System End to End");
		$this->email->to($email);

		$this->email->subject($subject);
		$this->email->message($content);
		return $this->email->send();
	}

	public function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public function base64url_decode($data)
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	public function uploadImage($name, $image_name = false)
	{
		$config['upload_path']          = $this->photo_path;
		$config['allowed_types']        = 'jpg|png|jpeg|JPG|PNG|JPEG|svg|SVG';
		$config['file_name']            = Date('Y-m-d h-i-s') . '_karmapack_image_' . (isset($_FILES[$name]['name']) ? $_FILES[$name]['name'] : '');
		if ($image_name) {
			$config['file_name']            = Date('Y-m-d h-i-s') . '_karmapack_image_' . $image_name;
		}
		$config['overwrite']            = false;
		$config['max_size']             = 8024;
		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if ($this->upload->do_upload($name)) {
			return [
				'status' => true,
				'data' => $this->upload->data("file_name"),
				'message' => 'Success'
			];
		} else {
			return [
				'status' => false,
				'data' => null,
				'message' => $this->upload->display_errors('', '')
			];
		}
	}

	public function deleteFile($file)
	{
		$res_foto = true;
		if ($file != null && $file != '') {
			if (file_exists($this->photo_path . $file)) {
				$res_foto = unlink($this->photo_path . $file);
			}
		}
		return $res_foto;
	}


	public function navFront()
	{
		$result = $this->navigation_type_front_array;
		if (empty($result)) {
			return '';
		}

		$li = '';
		foreach ($result as $res) {
			if ($res['have_child']) {
				$li .= $this->haveChildHtml($res);
			} else {
				$li .= $this->navHtml($res);
			}
		}

		return $li;
	}

	public function navFront2()
	{
		$result = $this->navigation_type_front_array;
		if (empty($result)) {
			return '';
		}

		$li = '';
		foreach ($result as $res) {
			if ($res['have_child']) {
				$li .= $this->haveChildHtml2($res);
			} else {
				$li .= $this->navHtml2($res);
			}
		}

		return $li;
	}

	private function haveChildHtml($data)
	{
		$child_html = '';
		foreach ($data['child'] as $child) {
			$child_html .= '<li><a class="dropdown-item" href="' . $child['url'] . '">' . $child['nama'] . '</a></li>';
		}

		return '
			<li class="nav-item dropdown ' . ($data['active'] ? 'active' : '') . '">
				<a class="nav-link dropdown-toggle" href="' . $data['url'] . '">' . $data['nama'] . '</a>
				<ul class="dropdown-menu">
				' . $child_html . '
				</ul>
			</li>
		';
	}

	private function navHtml($data)
	{
		return '
              <li class="nav-item ' . ($data['active'] ? 'active' : '') . '">
                <a class="nav-link" href="' . $data['url'] . '">' . $data['nama'] . '</a>
              </li>
		';
	}

	private function haveChildHtml2($data)
	{
		$child_html = '';
		foreach ($data['child'] as $child) {
			$child_html .= '<li><a href="' . $child['url'] . '">' . $child['nama'] . '</a></li>';
		}

		return '<li ' . ($data['active'] ? 'class="active"' : '') . '>
          <a href="' . $data['url'] . '">' . $data['nama'] . '</a>
          <ul class="submenu">
						' . $child_html . '
          </ul>
        </li>
		';
	}

	private function navHtml2($data)
	{
		return '<li ' . ($data['active'] ? 'class="active"' : '') . '> <a href="' . $data['url'] . '">' . $data['nama'] . '</a> </li>';
	}

	private function getNavArray()
	{
		$parrents = $this->db->select('menu_id as id, menu_nama as nama, menu_url as url')
			->from('menu_front')
			->where('menu_menu_id', 0)
			->where('menu_status', 'Aktif')
			->get()->result_array();

		if ($parrents == null) {
			return [];
		}

		$now = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
		$now .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$navigation_front = $this->navigation_front == null ? '' :  base_url($this->navigation_front);
		$rows = [];
		foreach ($parrents as $parrent) {
			$parrent['url'] = $parrent['url'] == '#' ? $parrent['url'] : base_url($parrent['url']);
			$parrent_active = in_array($parrent['url'], [$now, $navigation_front]);
			$child_active = false;
			$child = $this->getChild($parrent['id']);
			$child_rows = [];
			$have_child = false;
			if (is_array($child)) {
				foreach ($child as $c) {
					$c['url'] = base_url($c['url']);
					$have_child = true;
					$active = in_array($c['url'], [$now, $navigation_front]);
					$child_rows[] = array_merge(['active' => $active], $c);
					if ($active) {
						$child_active = true;
					}
				}
			}
			$rows[] = array_merge($parrent, [
				'child' => $child_rows,
				'active' => ($parrent_active || $child_active),
				'have_child' => $have_child
			]);
		}
		return $rows;
	}

	private function getChild($id)
	{
		$child = $this->db->select('menu_id as id, menu_nama as nama, menu_url as url')
			->from('menu_front')
			->where('menu_menu_id', $id)
			->where('menu_status', 'Aktif')
			->get()->result_array();
		return $child;
	}

	public function key_get($key)
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		if (!$get = $this->cache->get($key)) {
			$get = $this->db->select("key, value1, value2")
				->from('key_value')->where('key', $key)->get();
			if ($get->num_rows() == 0) {
				$data = [
					'key' => $key,
					'value1' => null,
					'value2' => null,
					'created_by' => $this->session->userdata('data')['id'],
				];
				$this->db->insert("key_value",  $data);
				$get = $data;
			} else {
				$get = $get->row_array();
			}
			$this->cache->save($key, $get);
		}
		return $get;
	}

	public function key_set($key, $value1, $value2)
	{
		// delete cache
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->cache->delete($key);

		// query
		$get = $this->db->select("key")
			->from('key_value')->where('key', $key)->get();
		if ($get->num_rows() == 0) {
			$get = $this->db->insert("key_value", [
				'key' => $key,
				'value1' => $value1,
				'value2' => $value2,
				'created_by' => $this->session->userdata('data')['id'],
			]);
		} else {
			$get = $this->db->where('key', $key)->update('key_value', [
				'value1' => $value1,
				'value2' => $value2,
				'updated_by' => $this->session->userdata('data')['id'],
			]);
		}
		return $get;
	}

	function __construct()
	{
		parent::__construct();
		$this->app_name = $this->config->item('app_name');
		$this->copyright = $this->config->item('copyright');
		$this->page_setting = $this->config->item('page_setting');
		$this->page_nav = $this->config->item('page_nav');
		$this->template_type = $this->config->item('template_type');

		$this->load->library('plugin');
	}
}
