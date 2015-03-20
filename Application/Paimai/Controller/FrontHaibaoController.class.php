<?php
namespace Paimai\Controller;
use Paimai\Controller\PaimaiPublicController;

Class FrontHaibaoController extends PaimaiPublicController {

	public function _initialize() {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
    }

	public function index() {
		$this->display("Front:haibao");
	}
}