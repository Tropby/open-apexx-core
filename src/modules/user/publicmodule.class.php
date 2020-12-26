<?php

namespace Modules\User;

require_once("functions.php");

class PublicModule extends \PublicModule
{
    public function __construct(\Module &$module)
    {
        parent::__construct($module);

        /**
         * Register all actions that can be executed in public scope
         */
        $this->registerAction("index");
        $this->registerAction("login");
        $this->registerAction("logout");
        $this->registerAction("friends");
        $this->registerAction("activate");
        $this->registerAction("addbookmark");
        $this->registerAction("addbuddy");
        $this->registerAction("avatar");
        $this->registerAction("blog");
        $this->registerAction("collection");
        $this->registerAction("delbookmark");
        $this->registerAction("delbuddy");
        $this->registerAction("delpm");
        $this->registerAction("gallery");
        $this->registerAction("getpwd");
        $this->registerAction("getregkey");
        $this->registerAction("guestbook");
        $this->registerAction("ignorelist");
        $this->registerAction("listuser");
        $this->registerAction("myblog");
        $this->registerAction("mygallery");
        $this->registerAction("myprofile");
        $this->registerAction("newmail");
        $this->registerAction("newpm");
        $this->registerAction("online");
        $this->registerAction("pms");
        $this->registerAction("profile");
        $this->registerAction("readpm");
        $this->registerAction("register");
        $this->registerAction("report");
        $this->registerAction("search");
        $this->registerAction("setstatus");
        $this->registerAction("signature");
        $this->registerAction("subscribe");
        $this->registerAction("subscriptions");
        $this->registerAction("usermap");

        /** 
         * Register all tempaltes that can be used in public scope
         */
        $this->registerTemplateFunction('info', 'USER_INFO');
        $this->registerTemplateFunction('stats', 'USER_STATS');
    }
}
