<?php 

namespace Envo\Foundation;

class Loader
{
    protected static $instance = null;
    
    public static function getInstance()
    {
        if( self::$instance ) return self::$instance;
        return self::$instance = new \Phalcon\Loader();
    }
    
    public static function setInstance($instance)
    {
        return self::$instance = $instance;
    }
    
    public static function register($name, $register = true)
    {
        $repositories = array(
            'Eagerload' => array(
                'Sb\Framework\Mvc\Model' => APP_PATH . '/vendor/envomer/phalcon.eager-loading/src/'
            ),
            'Cron' => array(
                'Cron' => APP_PATH . '/vendor/mtdowling/cron-expression/src/Cron/'
            )
        );
        
        $instance = self::getInstance();
        $instance->registerNamespaces($repositories[$name]);
        if( $register ) {
            $instance->register();
        }
        return $instance;
    }
    
}