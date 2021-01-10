<?php

namespace Modules\User\PublicTemplateFunction;

class Profile extends \PublicTemplateFunction
{
    public function execute($id = 0, $username = false)
    {
        $apx = $this->publicModule()->module()->apx();
        $db = $apx->db();
        $user = $apx->get_registered_object('user');

        static $cache;

        $id = (int)$id;
        if (!$id) return;

        //Benutzername auslesen, wenn SEO-URLs aktiviert
        if ($apx->config('main')['staticsites'])
        {
            if (isset($cache[$id])) $username = $cache[$id];
            if ($username === false)
            {
                list($username) = $user->get_info($id, 'username');
            }

            $cache[$id] = $username; //Speichern
        }

        echo mklink(
            'user.php?action=profile&amp;id=' . $id,
            'user,profile,' . $id . urlformat($username) . '.html'
        );
    }

}
