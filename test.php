<?php

    require_once 'vendor/autoload.php';

    try{
        
        $status = new MCS\DHLParcelStatus(
            'JVGL123456789'
        );
        
        $a = $status->getStatus();
        
        print_r($a);
        
        
    } catch (Exception $e) {
        dump($e->getMessage());    
    }
