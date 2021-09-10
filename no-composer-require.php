<?php
//手动加载 Manual loading
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'iry/queue')===0 && !class_exists($class,false)){
        include str_replace('^iry/queue/',__DIR__.'/src/', '^'.$classPath).'.php';
    }
});