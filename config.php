<?php
if(!defined('NGCMS')) exit('HAL');

plugins_load_config();
LoadPluginLang('dbcache', 'config', '', '', ':');

/*switch ($_REQUEST['action']) {
	case 'list_menu': showlist(); break;
	case 'add_form': add(); break;
	case 'move_up': move('up'); showlist(); break;
	case 'move_down': move('down'); showlist(); break;
	case 'dell': delete(); break;
	case 'general_submit': general_submit(); main(); break;
	case 'clear_cash': clear_cash();
	default: main();
}*/


//function main(){
//  global $lang, $cfg, $plugin;
    $cfg = array();
    array_push($cfg, array('descr' => $lang['dbcache:description']));
    array_push($cfg, array('name' => 'typecache', 
                           'title' => $lang['dbcache:typecache_title'], 'type' => 'select',
                           'values' => (extension_loaded("memcache") || 
                                        extension_loaded("memcached"))?
                                            array ('file' => $lang['dbcache:file'],'memcache'=>'memcache/memcached'):
                                            array ('file' => $lang['dbcache:file']), value => pluginGetVariable('dbcache','typecache')));
    array_push($cfg, array('name' => 'timeout', 'title' => $lang['dbcache:timeout_title'], 'descr' => $lang['dbcache:timeout_descr'], 'html_flags' => 'size=20', 'type' => 'input', 'value' => intval(pluginGetVariable($plugin,'timeout'))?pluginGetVariable($plugin,'timeout'):'10'));
    
    if (extension_loaded("memcache") || extension_loaded("memcached")){
        $cfgX = array();
        $server = pluginGetVariable($plugin,'server');
        array_push($cfgX, array('name' => 'server', 'title' => $lang['dbcache:server_title'], 'descr' => $lang['dbcache:server_descr'], 'html_flags' => 'size=20', 'type' => 'input', 'value' => empty($server)?'localhost':$server));
        array_push($cfgX, array('name' => 'port', 'title' => $lang['dbcache:port_title'], 'descr' => $lang['dbcache:port_descr'], 'html_flags' => 'size=20', 'type' => 'input', 'value' => intval(pluginGetVariable($plugin,'port'))?pluginGetVariable($plugin,'port'):'11211'));
        array_push($cfg,  array('mode' => 'group', 'title' => $lang['dbcache:group_descr'], 'entries' => $cfgX));
    }
    
// RUN
    if ($_REQUEST['action'] == 'commit') {
            // If submit requested, do config save
            commit_plugin_config_changes($plugin, $cfg);
            print_commit_complete($plugin);
    } else {
            generate_config_page($plugin, $cfg);
    }
    
//}
main();
