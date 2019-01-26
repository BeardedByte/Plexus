<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 25/01/2019
 * Time: 21:48
 */

namespace Plexus\Module;


use Plexus\Application;
use Plexus\Module;

class UserModule extends Module
{

    static $USER_SESSION_IDENTIFIER = "@UserModule.user";

    /**
     * @var
     */
    private $user;

    /**
     * UserModule constructor.
     * @param Application $application
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     */
    public function __construct(Application $application) {
        parent::__construct('UserModule', $application);

        $this->user = null;
        if (isset($_SESSION[UserModule::$USER_SESSION_IDENTIFIER])) {
            $this->user = $_SESSION[UserModule::$USER_SESSION_IDENTIFIER];
        }
    }

    /**
     * @return bool
     */
    public function isUserConnected() {
        return ($this->user !== null);
    }

    /**
     * @return mixed
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param $user
     */
    public function openUserSession($user) {
        $this->user = $user;
        $_SESSION[UserModule::$USER_SESSION_IDENTIFIER] = $user;
    }

    /**
     *
     */
    public function closeUserSession() {
        $this->user = null;
        unset($_SESSION[UserModule::$USER_SESSION_IDENTIFIER]);
    }
}