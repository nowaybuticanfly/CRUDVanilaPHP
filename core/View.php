<?php
namespace Core;

class View
{

    public function render($viewName, $user = null, $errors = []) {
        $viewAry = explode('/', $viewName);
        $viewString = implode(DS, $viewAry);
        if(file_exists(ROOT . DS . 'views' . DS . $viewString . '.php')) {
            include_once(ROOT . DS . 'views' . DS . $viewString . '.php');
        }else {
            die('The view "' . $viewName . '" does not exist');
        }
    }
}