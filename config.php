<?php
if(!defined('NGCMS')) exit('HAL');

plugins_load_config();
LoadPluginLang('dbcache', 'config', '', '', ':');

switch ($_REQUEST['action']) {
        case 'clear_cache': clear_cache();
	default: main();
}

function main(){
    global $lang, $cfg, $plugin;
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
    array_push($cfg, array('input' => '<input value="'.$lang['dbcache:flush_cache'].'" class="button" onclick="document.forms[\'form\'].action.value = \'clear_cache\';" type="submit">', 'type' => 'manual'));
// RUN
    if ($_REQUEST['action'] == 'commit') {
            // If submit requested, do config save
            commit_plugin_config_changes($plugin, $cfg);
            print_commit_complete($plugin);
    } else {
            generate_config_page($plugin, $cfg);
    }
    
}
function clear_cache(){
    global $plugin;
    if (($f = get_plugcache_dir($plugin))){
        $scdir = scandir($f);
        foreach ($scdir as $file){
            @unlink($f.$file);
        }
    }
    if ((extension_loaded("memcache")) || (extension_loaded("memcached"))){
        $server = pluginGetVariable('dbcache', 'server');
        if (extension_loaded("memcache")){
                $mem = new Memcache();
                if (@$mem->connect((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'))) {
                    $mem->flush();
                    $mem->close();
                }
        }elseif (extension_loaded("memcached")) {
                $mem = new Memcached();
                $mem->addServer((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'));
                @$mem->flush();
        }
    }
}
