<?php

class ControllerToolTinymce extends \Ninja\AdminController
{
	private $_route = 'tool/tinymce';

	public function index()
	{
		$data['files'] = $this->files();
		$data['font_awesome'] = 'view/javascript/font-awesome/css/font-awesome.css';
		$data['ajax_url'] = $this->getUrl()->link($this->_route . '/files_ajax', "user_token={$this->getUserToken()}");

		$this->getResponse()->setOutput($this->getLoader()->view('tool/tinymce_im', $data));
	}

	private function files()
	{
		$extensions = 'jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP';
		$manager = new \Ninja\TinyMCE\FileManager($extensions, DIR_IMAGE, DIR_ROOT);

		$path = $manager->cleanBackMovements($this->getRequest()->issetGet('path'));
		$manager->setPath($path);

		if ($this->getConfig()->get('config_secure')) {
			$domain = HTTPS_CATALOG . 'image/';
		} else {
			$domain = HTTP_CATALOG . 'image/';
		}

		$manager->setAbsolutePath($domain, '');
		$dirs = $manager->getDirectories();
		$files = $manager->getFiles();

		return $this->render($manager, $dirs, $files);
	}

	public function files_ajax()
	{
		$this->getResponse()->setOutput($this->files());
	}

	private function render(\Ninja\TinyMCE\FileManager $manager, $dirs, $files)
	{
		$output = "<div class='current-path'>{$manager->getPath()}</div>";

		foreach ($dirs as $dir) {
			$output .= $manager->renderDir($dir);
		}

		foreach ($files as $file) {
			$output .= $manager->renderImage($file);
		}

		return $output;
	}

}