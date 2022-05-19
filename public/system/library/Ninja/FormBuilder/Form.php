<?php

namespace Ninja\FormBuilder;

class Form {
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	const CONTROL_INPUT = 'Input';
	const CONTROL_TEXTAREA = 'Textarea';
	const CONTROL_SELECT = 'Select';

	private $__isAddDateTimeScripts = false;

	private $__renderer = null;
	/**@var  bool
	 * (if false then generate first element of form hidden input with autocomplete="off" attribute)
	 * (if we have password field in controls array and $this->isAutocomplete() === 'off' then add to password control attr autocomplete="no-password")
	 **/
	private $__is_autocomplete;

	private $__action;
	private $__method;
	private $__controls;

	// __values array used for init default values for form inputs. key = input name, value = input value
	private $__values = array();

	public $controller;

	private function getControlClass($object) {
		$class_name = get_class($object);

		//if Object class longer than ClassName (\Some\NameSpace\ClassName) return only ClassName
		if (strpos($class_name, '\\') !== false) {
			$parts = explode('\\', $class_name);
			$class_name = end($parts);
		}
		return $class_name;
	}

	/**
	 * Form constructor.
	 * @param $registry \Registry
	 * @param $action
	 * @param $method
//	 * @param Controls $__controls
	 */
	public function __construct(\Registry $registry, $action = '', $method = self::METHOD_POST)
	{
//		$this->__renderer = $__renderer;
//		$this->__is_autocomplete = $__is_autocomplete;
		$this->__action = $action;
		$this->__method = $method;
		$this->__controls = new Controls();
		$this->controller = new \Ninja\Controller($registry);
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->__action;
	}

	/**
	 * @param string $action
	 */
	public function setAction($action)
	{
		$this->__action = $action;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->__method;
	}

	/**
	 * @param mixed $method
	 */
	public function setMethod($method)
	{
		$this->__method = $method;
	}

	public function getControls()
	{
		return $this->__controls->getControls();
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->__values;
	}

	/**
	 * @param array $values
	 */
	public function setValues($values)
	{
		$this->__values = $values;
	}

	/**
	 * Manual adding of Control to HTML Form.
	 * @param $control Control
	 */
	public function insertControl($control)
	{
		$this->__controls->addControl($control);
	}

	/**
	 * Add to HTML Form input element
	 * @param $type string Type of HTML Input element
	 * @param $name string Name of HTML Input element
	 */
	public function addInput($type, $name, $classes = '', $is_error = false, $error_text = '')
	{
		//generate all needed by name
		$id = "input-{$name}";
		$label = $this->controller->language->get("entry_{$name}");

		$input = new Input($type, $name, $label, $id, $is_error, $error_text);
		$input->addClass('form-control ' . $classes);

		$this->insertControl($input);
	}

	public function addTextarea($name, $classes = '', $is_error = false, $error_text = '')
	{
		//generate all needed by name
		$id = "input-{$name}";
		$label = $this->controller->language->get("entry_{$name}");

		$textarea = new Textarea($name, $label, $id, $is_error, $error_text);
		$textarea->addClass('form-control ' . $classes);

		$this->insertControl($textarea);
	}

	public function addSelect($name, $options = array(), $classes = '', $is_error = false, $error_text = '')
	{
		//generate all needed by name
		$id = "input-{$name}";
		$label = $this->controller->language->get("entry_{$name}");

		$select = new Select($name, $label, $id, $is_error, $error_text);
		$select->addClass('form-control ' . $classes);
		$select->setOptions($options);

		$this->insertControl($select);
	}

	public function renderFormStart()
	{
		return "<form id='form' action='{$this->getAction()}' class='' method='{$this->getMethod()}' enctype='multipart/form-data'>";
	}

	private function renderDateTimeScripts()
	{
		return "<script>
			$('.date').datetimepicker({
				language: '{{ datepicker }}',
				pickTime: false
			});
			
			$('.time').datetimepicker({
				language: '{{ datepicker }}',
				pickDate: false
			});
			
			$('.datetime').datetimepicker({
				language: '{{ datepicker }}',
				pickDate: true,
				pickTime: true
			});		
		</script>";
	}

	/**
	 * Render Input HTML element
	 * @param $input Input
	 * @param $value mixed
	 * @return string HTML representation as string
	 */
	private function renderInput($input, $value)
	{
		switch ($input->getType()) {
			case $input::TYPE_DATE:
				$this->__isAddDateTimeScripts = true;
				$html = '<div class="col-sm-10">
				  <div class="input-group date">
                    <input type="text" name="'.$input->getName().'" value="'.$value.'" placeholder="'.$input->getLabel().'" data-date-format="YYYY-MM-DD" id="'.$input->getId().'" class="'.$input->getClass().'" />
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                  </div></div>';
				break;
			case $input::TYPE_TIME:
				$this->__isAddDateTimeScripts = true;
				$html = '<div class="col-sm-10">
				  <div class="input-group time">
                    <input type="text" name="'.$input->getName().'" value="'.$value.'" placeholder="'.$input->getLabel().'" data-date-format="HH:mm" id="'.$input->getId().'" class="'.$input->getClass().'" />
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
                    </span>
                  </div></div>';
				break;
			case $input::TYPE_DATETIME:
				$this->__isAddDateTimeScripts = true;
				$html = '<div class="col-sm-10">
				  <div class="input-group datetime">
                    <input type="text" name="'.$input->getName().'" value="'.$value.'" placeholder="'.$input->getLabel().'" data-date-format="YYYY-MM-DD HH:mm" id="'.$input->getId().'" class="'.$input->getClass().'" />
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                  </div></div>';
				break;
			default:
				$html = '<div class="col-sm-10">
                  <input type="'.$input->getType().'" name="'.$input->getName().'" value="'.$value.'" placeholder="'.$input->getLabel().'" id="'.$input->getId().'" class="'.$input->getClass().'">
                </div>';
				break;
		}
		return $html;
	}

	public function renderFormBody()
	{

		$html = '';

		$controls = $this->getControls();

		foreach ($controls as $control) {
			//Get value from Request or default
			if (($this->getMethod() == self::METHOD_POST) && ($this->controller->request->server['REQUEST_METHOD'] == 'POST')) {
				$value = $this->controller->request->issetPost($control->getName());
			} else if (($this->getMethod() == self::METHOD_GET) && ($this->controller->request->server['REQUEST_METHOD'] == 'GET')) {
				$value = $this->controller->request->issetGet($control->getName());
			} else {
				$value = isset($this->getValues()[$control->getName()]) ? $this->getValues()[$control->getName()] : '';
			}

			if ($this->getControlClass($control) == self::CONTROL_INPUT) {
				$html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>';
				$html .= $this->renderInput($control, $value);
				if ($control->isError()) {
					$html .= '<div class="text-danger">'.$control->getErrorText().'</div>';
				}
				$html .= '</div>';
			} elseif ($this->getControlClass($control) == self::CONTROL_TEXTAREA) {
				$html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>
                    <div class="col-sm-10">
                      <textarea name="'.$control->getName().'" rows="5" placeholder="'.$control->getLabel().'" id="'.$control->getId().'" class="'.$control->getClass().'">'.$value.'</textarea>
                    </div>';
				$html .= '</div>';
			} elseif ($this->getControlClass($control) == self::CONTROL_SELECT) {
				$html .= '<div class="form-group">
                    <label class="col-sm-2 control-label label-small" for="'.$control->getId().'">'.$control->getLabel().'</label>
                    <div class="col-sm-10">
                      <select id="'.$control->getId().'" class="'.$control->getClass().'" name="'.$control->getName().'">';
				foreach ($control->getOptions() as $id => $name) {
					$is_selected = ($id == $value) ? ' selected' : '';
					$html .= '<option value="'.$id.'" '.$is_selected.'>'.$name.'</option>';
				}
				$html .= '</select>
                    </div>';
				if ($control->isError()) {
					$html .= '<div class="text-danger">'.$control->getErrorText().'</div>';
				}
				$html .= '</div>';
			}
		}
		return $html;
	}

	public function renderFormEnd()
	{
		$html = '';
		if ($this->__isAddDateTimeScripts) {
			$html .= $this->renderDateTimeScripts();
		}
		$html .= '</form>';
		return $html;
	}

	public function renderWholeForm()
	{
		//Render default Bootstrap Form
		if (is_null($this->__renderer)) {
			$html = $this->renderFormStart();
			$html .= $this->renderFormBody();
			$html .= $this->renderFormEnd();
			return $html;
		} else {
			//Use Custom Render Algorithm
			return $this->__renderer->render($this);
		}
	}

}