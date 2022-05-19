<?php


namespace Ninja\TinyMCE;


/**
 * Class FileManager
 * @package Ninja\TinyMCE
 */
class FileManager
{
	const DIR_BACK = '..';

	private $_extensions;
	private $_root_app_path;
	private $_root_path;
	private $_path;

	//Absolute Path Replace Routine
	private $_isAbsolutePath = false;
	private $_abs_domain;
	private $_abs_path;

	private function fixTrailingSlash(string $path) {
		if (!empty($path))
			if (substr($path, strlen($path) - 1, 1) != '/') {
				$path .= '/';
			}

		return $path;
	}

	/**
	 * FileManager constructor.
	 * @param string $extensions list of allowed extensions, format example: 'png,jpg,jpeg,gif'
	 * @param string $root_path absolute path to directory contain images
	 * @param string $root_app_path absolute path to app directory
	 */
	public function __construct(string $extensions, string $root_path, string $root_app_path)
	{
		$this->_extensions = $extensions;
		$this->_root_path = $this->fixTrailingSlash($root_path);
		$this->_root_app_path = $this->fixTrailingSlash($root_app_path);
	}

	/**
	 * Fix user story back movements through directories. Example:
	 * /home/somedir/images/demo/products/../ to /home/somedir/images/demo/
	 * @param string $path
	 * @return string
	 */
	public function cleanBackMovements(string $path)
	{
		$story = explode('/', $path);

		$storyDirs = [];
		$backCounter = 0;
		foreach ($story as $item) {
			if (!empty($item)) {
				if ($item == self::DIR_BACK) {
					$backCounter++;
				} else {
					$storyDirs[] = $item;
				}
			}
		}

		$counter = count($storyDirs);
		if ($backCounter <= $counter) {
			for ($i = $counter; $i >= ($counter - $backCounter); $i--) {
				unset($storyDirs[$i]);
			}
		} else {
			$storyDirs = [];
		}

		return implode('/', $storyDirs);
	}


	public function renderDir($dir)
	{
		$str = "<div class='dir-wrapper' data-path='{$dir['relative_path']}'>";
		$str .= "<i class='fa fa-folder big'></i>";
		$str .= "<div class='name' title='{$dir['filename']}'>{$dir['filename']}</div>";
		$str .= "</div>";
		return $str;
	}

	public function renderImage($image)
	{
		$str = "<div class='file-wrapper' data-url='{$image['url']}'>";
		$str .= "<img src='{$image['relative_path']}' title='{$image['filename']}' alt='{$image['filename']}'>";
		$str .= "<div class='name' title='{$image['filename']}'>{$image['filename']}</div>";
		$str .= "</div>";
		return $str;
	}

	public function isFilenameInPath($path)
	{
		$exts = explode(',', $this->_extensions);
		foreach ($exts as $ext) {
			if (strpos($path, '.' . $ext) !== false) {
				return true;
			}
		}
		return false;
	}

	public function getDirectories()
	{
		$dirs = glob($this->getCurrentPath() . '*', GLOB_ONLYDIR);
		$results = [];

		if ($this->_root_path != $this->getCurrentPath()) {
			$results[] = ['filename' => '..', 'relative_path' => $this->getPath() . '../'];
		}

		foreach ($dirs as $dir) {
			$results[] = [
				'filename' => basename($dir),
				'relative_path' => str_replace($this->_root_path, '', $dir)
			];
		}

		return $results;
	}

	public function getFiles()
	{
		$files = glob($this->getCurrentPath() . "*.{" . $this->_extensions . "}", GLOB_BRACE);
		$results = [];
		foreach ($files as $file) {
			$relativeUrl = str_replace($this->_root_path, '', $file);
			$url = ($this->_isAbsolutePath) ? $this->_abs_domain . $relativeUrl : $relativeUrl;

			$relativePath = str_replace($this->_root_path, '', $file);
			$path = ($this->_isAbsolutePath) ? $this->_abs_domain . $relativePath : $relativePath;

			$results[] = [
				'filename' => basename($file),
				'relative_path' => $path,
				'url' => $url
			];
		}
		return $results;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * @param string $path
	 */
	public function setPath(string $path): void
	{
		$this->_path = $this->fixTrailingSlash($path);
	}

	public function getCurrentPath(): string
	{
		return str_replace('//', '/', $this->_root_path . $this->_path);
	}

	public function setAbsolutePath($domain, $path)
	{
		$this->_isAbsolutePath = true;

		$this->_abs_domain = $domain;
		$this->_abs_path = $path;
	}

}