<?php

namespace Modules\User\AdminAction;

/**
 * 
 */
class GAdd extends \AdminAction
{
    public function execute()
    {
        global $set, $apx, $db;

        if ($_POST['send'] == 1)
        {
            if (!in_array($_POST['gtype'], array('admin', 'indiv', 'public', 'guest'))) $_POST['gtype'] = 'public';
            list($checkname) = $db->first("SELECT groupid FROM " . PRE . "_user_groups WHERE name='" . addslashes($_POST['name']) . "' LIMIT 1");

            if (!checkToken()) infoInvalidToken();
            elseif (!$_POST['name'] || ($_POST['gtype'] == 'indiv' && !$_POST['right'])) infoNotComplete();
            elseif (count($apx->sections) && (!count($_POST['section_access']) || ($_POST['gtype'] == 'indiv' && !count($_POST['section_access'])))) infoNotComplete();
            elseif ($checkname) info($apx->lang->get('INFO_GROUPEXISTS'));
            else
            {

                //INDIV
                if ($_POST['gtype'] == 'indiv')
                {
                    $newr = array();
                    $newsp = array();

                    //Rechte
                    if (is_array($_POST['right']))
                    {
                        foreach ($_POST['right'] as $theaction => $trash)
                        {
                            $newr[] = $theaction;
                        }
                        $ins_rights = serialize($newr);
                    }

                    //Sonderrechte
                    if (is_array($_POST['spright']))
                    {
                        foreach ($_POST['spright'] as $theaction => $trash)
                        {
                            if (!in_array($theaction, $newr)) continue;
                            $newsp[] = $theaction;
                        }
                        $ins_sprights = serialize($newsp);
                    }

                    //Sektionen
                    if ($_POST['section_access'][0] == 'all') $section_access = 'all';
                    else $section_access = serialize($_POST['section_access']);
                }

                //PUBLIC -> Nur Sektionen
                else
                {
                    $_POST['gtype'] = 'public';
                    $section_access = serialize(array());
                    if ($_POST['section_access'][0] == 'all') $section_access = 'all';
                    else $section_access = serialize($_POST['section_access']);
                }

                $db->query("INSERT INTO " . PRE . "_user_groups VALUES ('','" . addslashes($_POST['name']) . "','" . addslashes($_POST['gtype']) . "','" . addslashes($ins_rights) . "','" . addslashes($ins_sprights) . "','" . addslashes($section_access) . "')");
                logit('USER_GADD', 'ID #' . $db->insert_id());
                printJSRedirect('action.php?action=user.gshow');
            }
        }
        else
        {
            $_POST['gtype'] = 'indiv';

            $apx->lang->dropall('expl');

            //Rechte
            $mobj = 0;
            foreach ($apx->modules as $module => $trash)
            {
                $obj=0;
                foreach ($apx->actions[$module] as $action => $info)
                {
                    //Standardrechte filtern
                    if ($info[3]) continue;

                    ++$obj;
                    $actiondata[$obj]['ACTION'] = $module . '.' . $action;
                    $actiondata[$obj]['TITLE'] = $apx->lang->get('TITLE_' . strtoupper($module) . '_' . strtoupper($action));
                    $actiondata[$obj]['ID'] = $module . '.' . $action;
                    $actiondata[$obj]['RIGHT'] = iif($_POST['right'][$module . '.' . $action], 1, 0);
                    $actiondata[$obj]['SPRIGHT'] = iif($_POST['spright'][$module . '.' . $action], 1, 0);
                    $actiondata[$obj]['HASSP'] = iif($info[0], 1, 0);
                    $actiondata[$obj]['INFO'] = $apx->lang->get('EXPL_' . strtoupper($module) . '_' . strtoupper($action));
                }

                ++$mobj;
                $moduledata[$mobj]['TITLE'] = $apx->lang->get('MODULENAME_' . strtoupper($module));
                $moduledata[$mobj]['ID'] = $module;
                $moduledata[$mobj]['ACTION'] = $actiondata;

                $actiondata = array();
            }

            //Sektionen
            if (is_array($apx->sections) && count($apx->sections))
            {
                if (!isset($_POST['section_access']) || $_POST['section_access'][0] == 'all') $_POST['section_access'] = array('all');
                $section_access = '<option value="all"' . iif($_POST[' section_access'][0] == 'all', ' selected="selected"') . ' style="font-weight:bold;">' . $apx->lang->get('ALLSEC') . '</option>';

                foreach ($apx->sections as $id => $info)
                {
                    $section_access .= '<option value="' . $id . '"' . iif(in_array($id, $_POST[' section_access']), ' selected="selected"') . '>' . replace($info['title']) . '</option>';
                }
            }

            $apx->tmpl->assign('NAME', compatible_hsc($_POST['name']));
            $apx->tmpl->assign('GTYPE', $_POST['gtype']);
            $apx->tmpl->assign('SECTION_ACCESS', $section_access);
            $apx->tmpl->assign('MODULE', $moduledata);
            $apx->tmpl->assign('ACTION', 'gadd');

            $apx->tmpl->parse('gadd_gedit');
        }
    }
}
