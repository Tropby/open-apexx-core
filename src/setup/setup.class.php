<?php

namespace Setup;

use DatabaseMysqli;

class Setup
{
    private \Apexx $apx;

    public function __construct(\Apexx $apx)
    {
        $this->apx = $apx;
    }

    public function execute()
    {
        $this->apx->tmpl->loaddesign('setup');

        $action = "index";
        if ($this->apx->param()->getIf("action"))
            $action = $this->apx->param()->getString("action");

        $template = 'index';
        switch ($action) {
            case "step1":
                $this->step1();
                $template = 'step1';
                break;

            case "step2":
                $this->step2();
                $template = 'step2';
                break;

            case "step3":
                $this->step3();
                $template = 'step3';
                break;

            case "step4":
                $this->step4();
                $template = 'step4';
                break;

            case "step5":
                $this->step5();
                $template = 'step5';
                break;

            case "finish":
                $template = 'finish';
                break;

            default:
                exit;
                break;
        }

        $this->apx->tmpl->parse($template, 'setup');
    }

    private function step1()
    {
        $failed = false;
        if (is_writeable(BASEDIR . $this->apx->path()->getpath('uploads')))
            $this->apx->tmpl->assign('WRITEABLE_UPLOADS', true);
        else
            $failed = true;

        if (is_writeable(BASEDIR . $this->apx->path()->getpath('tmpldir')))
            $this->apx->tmpl->assign('WRITEABLE_TEMPLATES', true);
        else
            $failed = true;

        if (is_writeable(BASEDIR . $this->apx->path()->getpath('moduledir')))
            $this->apx->tmpl->assign('WRITEABLE_MODULES', true);
        else
            $failed = true;

        if (is_writeable(BASEDIR . $this->apx->path()->getpath('langdir')))
            $this->apx->tmpl->assign('WRITEABLE_LANGUAGE', true);
        else
            $failed = true;

        if (is_writeable(BASEDIR . $this->apx->path()->getpath('lib')))
            $this->apx->tmpl->assign('WRITEABLE_LIB', true);
        else
            $failed = true;

        if (is_writeable(BASEDIR . $this->apx->path()->getpath('setup')))
            $this->apx->tmpl->assign('WRITEABLE_SETUP', true);
        else
            $failed = true;


        if (!$failed)
            $this->apx->tmpl->assign('STEP1_FINISHED', true);
    }

    private function step2()
    {
        if (
            $this->apx->param()->postIf("db_name") &&
            $this->apx->param()->postIf("db_username") &&
            $this->apx->param()->postIf("db_password") &&
            $this->apx->param()->postIf("db_hostname") &&
            $this->apx->param()->postIf("db_prefix")
        ) {
            $this->apx->tmpl->assign("DB_HOSTNAME", $this->apx->param()->postString("db_hostname"));
            $this->apx->tmpl->assign("DB_USERNAME", $this->apx->param()->postString("db_username"));
            $this->apx->tmpl->assign("DB_PASSWORD", $this->apx->param()->postString("db_password"));
            $this->apx->tmpl->assign("DB_NAME", $this->apx->param()->postString("db_name"));
            $this->apx->tmpl->assign("DB_PREFIX", $this->apx->param()->postString("db_prefix"));

            $config = [
                "mysql_api" => "mysqli",
                "mysql_server" => $this->apx->param()->postString("db_hostname"),
                "mysql_user" => $this->apx->param()->postString("db_username"),
                "mysql_pwd" => $this->apx->param()->postString("db_password"),
                "mysql_db" => $this->apx->param()->postString("db_name"),
                "mysql_pre" => $this->apx->param()->postString("db_prefix"),
                "mysql_utf8" => false
            ];

            try {
                new DatabaseMysqli(
                    $config['mysql_server'],
                    $config['mysql_user'],
                    $config['mysql_pwd'],
                    $config['mysql_db'],
                    $config['mysql_utf8']
                );

                $test = json_encode($config, JSON_PRETTY_PRINT);
                $fp = fopen(BASEDIR . $this->apx->path()->getPath("lib") . "/config.database.php", "w");
                if ($fp) {
                    fwrite($fp, "<?php \$configJSON='\n\n");
                    fwrite($fp, $test);
                    fwrite($fp, "\n\n'; ?>");
                    fclose($fp);
                    header("location: ?module=setup&action=step3");
                } else {
                    $this->apx->tmpl->assign('FAILED', "Can not access lib/config.database.php!");
                }
            } catch (\Exception $ex) {
                $this->apx->tmpl->assign('FAILED', $ex->getMessage());
            }
        }
    }

    private function step3()
    {
        $db = $this->apx->db();
        if ($this->apx->param()->getIf("install")) {
            $modules = scandir(BASEDIR . "modules");
            foreach ($modules as $module) {
                if ($module[0] == ".") continue;

                $setupFile = BASEDIR . $this->apx->path()->getpath("module", ["MODULE" => $module]) . "setup.php";
                if (file_exists($setupFile)) {
                    define(SETUPMODE, "install");
                    $output[] = ["TEXT" => "Install old style module " . $module . "!"];
                    require_once($setupFile);
                } else {
                    $setupFile = BASEDIR . $this->apx->path()->getpath("module", ["MODULE" => $module]) . "module.class.php";


                    if (file_exists($setupFile)) {
                        $m = "\\Modules\\" . $module . "\\Module";
                        $m = new $m($this->apx);
                        $setup = $m->setup();
                        if ($setup) {
                            $output[] = ["TEXT" => "Install " . $module . "!"];
                            $setup->install($this->apx);
                        } else
                            $output[] = ["TEXT" => "Skip " . $module . " (module without database usage)!"];
                    } else {
                        $output[] = ["TEXT" => "Skip " . $module . " (module without setup routine)!"];
                    }
                }
            }
            $this->apx->tmpl->assign("STEP3_FINISHED", $output);
        }
    }

    private function step4()
    {
        if (
            $this->apx->param()->postIf("username") &&
            $this->apx->param()->postIf("display_name") &&
            $this->apx->param()->postIf("email") &&
            $this->apx->param()->postIf("password")
        ) {
            $salt = random_string(16);

            $password = md5(md5($this->apx->param()->postIf("password") . $salt));

            $this->apx->db()->query("
                INSERT INTO 
                    `apx_user` 
                (
                    `username_login`, 
                    `username`, 
                    `password`, 
                    `salt`, 
                    `reg_time`, 
                    `reg_email`,
                    `groupid`
                ) VALUES (
                    '" . $this->apx->param()->postSqlString("username") . "',
                    '" . $this->apx->param()->postSqlString("display_name") . "',
                    '" . $password . "',
                    '" . $salt . "',
                    '" . time() . "',
                    '" . $this->apx->param()->postSqlString("email") . "',
                    '1'
                )");

            header("location: ?module=setup&action=step5");
        }
    }

    private function step5()
    {
        if ($this->apx->param()->getIf("delete")) 
        {
            $fp = fopen(BASEDIR.$this->apx->path()->getPath("lib").".htaccess", "w");
            if( $fp )
            {

            }
            else
            {
                $this->apx->tmpl->assign("FAILED", "Can not create security '.htaccess'.");
                return;
            }

            chmod(BASEDIR.$this->apx->path()->getPath("lib")."config.php", 440);
            chmod(BASEDIR.$this->apx->path()->getPath("lib")."config.database.php", 440);

            $this->deleteDirectory(BASEDIR.$this->apx->path()->getPath("tmpl_modules_public", ["THEME" => "default", "MODULE" => "setup"]));

            unlink(BASEDIR.$this->apx->path()->getPath("tmpldir")."design_setup.html");

            // delete this file!
            //$this->deleteDirectory(BASEDIR."setup");            

            // goto website
            header("location: index.php");
        }
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) 
        {
            return true;
        }

        if (!is_dir($dir)) 
        {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) 
        {
            if ($item == '.' || $item == '..') 
            {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) 
            {
                return false;
            }
        }

        return rmdir($dir);
    }
}
