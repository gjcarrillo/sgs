<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MainController extends CI_Controller {

	/**
	 * Load Header and Footer
	 */
	public function index()
	{
		$this->load->view('templates/header');

		$this->load->view('templates/footer');
	}
}
