<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OCA\Images_Ocr\AppInfo;

use OCP\AppFramework\App;
use OCA\Images_Ocr\Controller\ViewController;

class Application extends App {
    public function __construct(array $urlParams=array()) {
        parent::__construct('images_ocr', $urlParams);
        $container = $this->getContainer();
        $server = $container->getServer();

        
        /*$x1 = \OC::$server->query('L10NFactory');
        $x2 = \OC::$server->getURLGenerator();
        $x3 = \OC::$server->getActivityManager();
        $x4 = new \OCA\Files\ActivityHelper(
                \OC::$server->getTagManager()
        );
        $x5 = \OC::$server->getDatabaseConnection();
        $x6 = \OC::$server->getConfig();
         * 
         */
        
        
        /**
         * Controllers
         */
        $container->registerService('ViewController', function (IContainer $c) use ($server) {
            return new ViewController();
        });
    }
}
