<?php
namespace tsframe\module\anticaptcha;

interface AntiCaptchaTaskProtocol{
    
    public function getPostData();
    public function getTaskSolution();
    
}