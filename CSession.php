<?php
/**
 * Cookie Session Handler
 * 
 * @package     Horus Platform
 * @author      Mohammed Al Ashaal <http://is.gd/alash3al>
 * @copyright   2014
 * @access      public
 * @license     MIT License
 */
Class CSession
{
    /** @ignore */
    protected $lifetime, $name, $domain, $path, $key; 

    /**
     * Constructor
     * @param   string  $name
     * @param   integer $lifetime
     * @param   string  $domain
     * @param   string  $path
     * @param   string  $key
     * @return  self
     */
    public function __construct($name, $lifetime, $domain = null, $path = '/', $key = null)
    {
        $this->name     =   $name;
        $this->lifetime =   (int) $lifetime;
        $this->domain   =   $domain;
        $this->path     =   $path;
        $this->key      =   $key;

        $this->handle();
    }

    /**
     * Handle the session
     * @return  void
     */
    public function handle()
    {
        $horus= Horus::I();

        session_name($this->name);

        session_set_save_handler
        (
            array($this, '__open'),
            array($this, '__close'),
            array($this, '__read'),
            array($this, '__write'),
            array($this, '__destroy'),
            array($this, '__gc')
        );

        register_shutdown_function('session_write_close');

        session_start();

        if ( ! isset($_SESSION['$csess.conf.lifetime']) )
            $_SESSION['$csess.conf.lifetime']   =   (int) $this->lifetime;

        if ( ! isset($_SESSION['$csess.conf.key']) )
            $_SESSION['$csess.conf.key']        =   $horus->util->hashMake($this->key);
    }

    /**
     * Read data from cookie
     * @param   string $id
     * @return  string
     */
    public function __read($id)
    {
        $data = null;
        $name = $this->name.'Data';
        $horus= Horus::I();

        if ( isset($_COOKIE[$name]) )
        {
            // key has been changed ?
            if ( isset($_SESSION['$csess.conf.key']) && ! $horus->util->hashVerify($this->key, $_SESSION['$csess.conf.key']) )
                $data = null;
            // decrypt ?
            elseif ( $this->key )
                $data = @$horus->util->decrypt($_COOKIE[$name], md5($id . $this->key));
            // decode ?
            else
                $data = @ base64_decode($_COOKIE[$name]);
        }

        return (string) $data;
    }

    /**
     * Write data to cookie
     * @param   string  $id
     * @param   string  $data
     * @return  true
     */
    public function __write($id, $data)
    {
        $horus = Horus::I();
        $name  = $this->name.'Data';

        // encrypt ?
        if ( $this->key )
            $data = (string) $horus->util->encrypt($data, md5($id . $this->key));
        // encode ?
        else
            $data = (string) @ base64_encode($data);

        $horus->res->cookie($name, $data, $x = array(
            'secure'    =>  $horus->req->secure(),
            'http'      =>  true,
            'lifetime'  =>  $_SESSION['$csess.conf.lifetime'],
            'path'      =>  $this->path,
            'domain'    =>  $this->domain
        ));

        return true;
    }

    /**
     * Remove all session data
     * @return  true
     */
    public function __destroy()
    {
        Horus::I()->res->cookie($this->name, null, array(
            'expire'    =>  time() - 3600
        ));

        Horus::I()->res->cookie($this->name.'Data', null, array(
            'expire'    =>  time() - 3600
        ));

        $_SESSION   =   array();

        return true;
    }

    /** @ignore */
    public function __open()
    {
        return true;
    }

    /** @ignore */
    public function __close()
    {
        return true;
    }

    /** @ignore */
    public function __gc()
    {
        return true;
    }
}
