<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_Mobile extends WHMCS_Admin {
	public function getTemplatePath() {
		if (!defined("MOBILEDIR")) {
			exit("No Mobile Directory Defined");
		}

		return MOBILEDIR . "/templates/";
	}

	public function output() {
		$this->smarty->display("header.tpl");
		$content = $this->smarty->fetch($this->template . ".tpl");
		$content = preg_replace('/(<form\W[^>]*\bmethod=(\'|"|)POST(\'|"|)\b[^>]*>)/i', '$1' . "\n" . generate_token(), $content);


		if ($this->exitmsg) {
			$content = $this->exitmsg;
		}

		echo $content;
		$this->smarty->display("footer.tpl");
	}

	public function setPageTitle($title) {
		$this->title = $title;
		return true;
	}

	public function setHeaderLeftBtn($url, $label = "", $icon = "") {
		if ($url == "back") {
			$url = "\" data-rel=\"back";
			$label = "Back";
			$icon = "back";
		}


		if ($url == "home") {
			$url = "index.php";
			$label = "Home";
			$icon = "home";
		}

		$this->assign("headleftbtnurl", $url);
		$this->assign("headleftbtnlabel", $label);
		$this->assign("headleftbtnicon", $icon);
	}

	public function setHeaderRightBtn($url, $label, $icon = "") {
		$this->assign("headrightbtnurl", $url);
		$this->assign("headrightbtnlabel", $label);
		$this->assign("headrightbtnicon", $icon);
	}
}

?>