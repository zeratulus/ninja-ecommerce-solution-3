<?php
namespace Template;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use voku\helper\HtmlMin;
use voku\twig\MinifyHtmlExtension;

final class Twig {
	private $twig;
	private $data = array();

	/**
	 * @var \DebugBar\StandardDebugBar
	 */
	private $_debugBar = null;

	public function __construct($debugBar = null) {
		$this->_debugBar = $debugBar;
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function render($template, $cache = false) {
		// specify where to look for templates
		$loader = new FilesystemLoader(DIR_TEMPLATE);

		// initialize Twig environment
		$config = [
            'autoescape' => false,
        ];

        if ((defined(TWIG_CACHE) && constant(TWIG_CACHE)) || $cache) {
			$config['cache'] = DIR_CACHE;
		}

        $this->twig = new Environment($loader, $config);

        if (defined('DEV') && constant('DEV') == false) {
            $minifier = new HtmlMin();
            $this->twig->addExtension(new MinifyHtmlExtension($minifier, true));
        }

//		TODO: Twig info to DebugBar -> here is 4 different calls of template rendering -> 0 info about templates in results
//		if (defined('DEV') && !is_null($this->_debugBar)) {
//			if (!$this->_debugBar->hasCollector('twig')) {
//				$env = new \DebugBar\Bridge\Twig\TraceableTwigEnvironment($this->twig);
//				$this->_debugBar->addCollector(new \DebugBar\Bridge\Twig\TwigCollector($env));
//			}
//		}
//		$this->_debugBar['messages']->addMessage($template);

		try {
			// load template
			$template = $this->twig->load($template . '.twig');

			return $template->render($this->data);
		} catch (Exception $e) {
			trigger_error('Error: Could not load template ' . $template . '!');
			exit();
		}
	}
}
