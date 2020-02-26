<?php

namespace Core;


class Validator {

    private $_passed=false, $_errors=[], $_db =null;


    public function __construct()
    {
        $this->_db = \Core\DB::getInstance()->dbh();
    }


    public function validate($source, $items=[])
    {
        foreach($items as $item => $rules) {
            $item = htmlspecialchars($item);
            $display = $rules['display'];

            foreach($rules as $rule => $rule_value) {

                $value = htmlspecialchars(trim($source[$item]));

                if($rule === 'required' && empty($value)) {
                    $this->addError(["{$display} is required", $item ]);
                } else if(!empty($value)) {
                    switch ($rule) {
                        case 'min':
                            if(strlen($value) < $rule_value) {
                                $this->addError(["{$display} must be a minimum of {$rule_value} characters", $item]);
                            }
                            break;

                        case 'max':
                            if(strlen($value) > $rule_value) {
                                $this->addError(["{$display} must be a maximum of {$rule_value} characters", $item]);
                            }
                            break;

                        case 'matches':
                            if($value != $source[$rule_value]) {
                                $matchDisplay = $items[$rule_value]['display'];
                                $this->addError(["{$matchDisplay} and {$display} must match.", $item]);
                            }
                            break;

                        case 'unique':
                            $check = $this->_db->query("SELECT {$item} FROM {$rule_value} WHERE {$item} = ?");
                            if($check) {
                                $this->addError(["{$display} already exists. Please choose another {$display}, $item"]);
                            }
                            break;

                        case 'is_numeric':
                            if(!is_numeric($value)) {
                                $this->addError(['${display} has to be a number. Please use a numeric value'], $item);
                            }
                            break;

                        case 'valid_email':
                            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $this->addError(["${display} must be a valid email address"], $item);
                            }
                            break;

                        case 'no_specials':
                            if(!preg_match('/[a-z\d_]/i', $value))
                            {
                                $this->addError(["${display} No specials or spacing allowed"], $item);
                            }
                            break;
                    }
                }
            }
        }
        if(empty($this->_errors)) {
            $this->_passed = true;
        }
        return $this;
    }

    public function addError($error)
    {
        $this->_errors[] = $error;
        if(empty($this->_errors)) {
            $this->_passed = true;
        } else {
            $this->_passed = false;
        }
    }

    public function displayErrors()
    {
        $hasErrors = (!empty($this->_errors))? ' border border-danger' : '';
        $html = '<ul class="bg-danger'.$hasErrors.'">';
        foreach($this->_errors as $error) {
            if(is_array($error)) {
                $html .= '<li class="text-danger>">' . $error[0] . '</li>' ;
                @   $html .= '<script>jQuery("document").ready(function(){jQuery("#'.$error[1].'").addClass("border border-danger");});</script>';
            } else  {
                $html .= '<li class="text-danger>">' . $error . '</li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function passed()
    {
        return $this->_passed;
    }


}