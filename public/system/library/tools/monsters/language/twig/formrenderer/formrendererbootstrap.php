<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 18.07.19
 * Time: 23:53
 */

namespace Tools\Monsters\Language\Twig\FormRenderer;

class FormRendererBootstrap extends FormRenderer
{

    public function __construct(\Registry &$registry)
    {
        parent::__construct($registry);
    }

    public function generateLanguagesTabs($tab_id)
    {
        $languages = $this->languages;
        $html = '<ul class="nav nav-tabs" id="language'.$tab_id.'">';
        foreach ($languages as $language) {
            $html .= '<li class="nav-item"><a href="#language'.$tab_id.$language['language_id'].'" data-toggle="tab"  class="nav-link"><img src="language/'.$language['code'].'/'.$language['code'].'.png" title="'.$language['name'].'"> '.$language['name'].'</a></li>';
        }
        $html .= '</ul><script>$(document).ready(function() {$("#language'.$tab_id.' a:first").tab("show");})</script>';
        return $html;
    }

    public function generateFormElements($tab_id)
    {
        $html = '<div class="tab-content">';
        $tab = $this->tabs->getTabByIdx($tab_id);

        $languages = $this->languages;

        foreach ($languages as $language) {
            $html .= '<div class="tab-pane fade card card-body" id="language'.$tab_id.$language['language_id'].'">';
            $controls = $tab->getControls();

            foreach ($controls as $control) {

                $tmp_control = $control->cloneControl();

                $id = $control->getId() . $language['language_id'];
                $setting = $control->getName() . $language['language_id'];

                $tmp_control->setName($setting);
                $tmp_control->setId($id);

                if (isset($this->request->post[$setting])) {
                    $value = $this->request->post[$setting];
                } else {
                    $value = $this->config->get($setting);
                }

                if ($tmp_control->getControlType() == 'input') {
                    $html .= '<div class="form-group">
                        <label class="col-sm-2 control-label" for="'.$tmp_control->getId().'">'.$tmp_control->getLabel().'</label>
                        <div class="col-sm-10">
                          <input type="'.$tmp_control->getType().'" name="'.$tmp_control->getName().'" value="'.$value.'" placeholder="'.$tmp_control->getLabel().'" id="'.$tmp_control->getId().'" class="form-control">
                        </div>';
                    if ($tmp_control->isError()) {
                        $html .= '<div class="text-danger">'.$tmp_control->getErrorText().'</div>';
                    }
                    $html .= '</div>';
                } elseif ($tmp_control->getControlType() == 'textarea') {
                    $html .= '<div class="form-group">
                        <label class="col-sm-2 control-label" for="'.$tmp_control->getId().'">'.$tmp_control->getLabel().'</label>
                        <div class="col-sm-10">
                          <textarea name="'.$tmp_control->getName().'" rows="5" placeholder="'.$tmp_control->getLabel().'" id="'.$tmp_control->getId().'" class="form-control">'.$value.'</textarea>
                        </div>';
                    $html .= '</div>';
                } elseif ($tmp_control->getControlType() == 'select') {
                    $html .= '<div class="form-group">
                        <label class="col-sm-2 control-label" for="'.$tmp_control->getId().'">'.$tmp_control->getLabel().'</label>
                        <div class="col-sm-10">
                          <select id="'.$tmp_control->getId().'" class="form-control"></select>
                        </div>';
                    if ($tmp_control->isError()) {
                        $html .= '<div class="text-danger">'.$tmp_control->getErrorText().'</div>';
                    }
                    $html .= '</div>';
                }

            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

}