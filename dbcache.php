<?php

// Protect against hack attempts
if (!defined('NGCMS'))
    die('HAL');
/*
  Plugin Name: DBCache
  Plugin URI: http://wm-talk.net/
  Description: File and Memory Cache for NGCMS
  Author: Roman Kapanets
  Version: 0.1
  Author URI: http://wm-talk.net/
 */

/**
 * @desc Class that implements the Cache functionality
 */
class DBCache extends mysql {

    /**
     * @desc Function read retrieves value from cache
     * @param $fileName - name of the cache file
     * @param $timeout - Cache time in minutes. Is given here only for file. (Default is 10 minutes).
     * Usage: DBCache::read('fileName.extension')
     */
    function read($fileName, $timeout = 10) {
        if (pluginGetVariable('dbcache', 'typecache') != 'memcache') {
            return unserialize(cacheRetrieveFile($fileName, ($timeout > 0) ? ($timeout * 60) : 600, 'dbcache'));
        } else {
            if (extension_loaded("memcached")) {
                $server = pluginGetVariable('dbcache', 'server');
                $mem = new Memcached();
                $mem->addServer((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'));
                return unserialize($mem->get($fileName));
            } elseif (extension_loaded("memcache")) {
                $server = pluginGetVariable('dbcache', 'server');
                $mem = new Memcache();
                if (@$mem->connect((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'))) {
                    return unserialize($mem->get($fileName));
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * @desc Function for writing key => value to cache
     * @param $fileName - name of the cache file (key)
     * @param $variable - value
     * @param $timeout - Cache time in minutes. Is given here only for memcache. (Default is 10 minutes).
     * Usage: Cache::write('fileName.extension', value)
     */
    function write($fileName, $variable, $timeout = 10) {
        if (pluginGetVariable('dbcache', 'typecache') != 'memcache') {
            return cacheStoreFile($fileName, serialize($variable), 'dbcache');
        } else {
            if (extension_loaded("memcached")) {
                $server = pluginGetVariable('dbcache', 'server');
                $mem = new Memcached();
                $mem->addServer((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'));
                return (@$mem->set($fileName, serialize($variable), ($timeout > 0) ? (time() + $timeout * 60) : 0)) ? true : false;
            } elseif (extension_loaded("memcache")) {
                $server = pluginGetVariable('dbcache', 'server');
                $mem = new Memcache();
                if (@$mem->connect((empty($server) ? 'localhost' : $server), (intval(pluginGetVariable('dbcache', 'port')) ? intval(pluginGetVariable('dbcache', 'port')) : '11211'))) {
                    return (@$mem->set($fileName, serialize($variable), false, ($timeout > 0) ? (time() + $timeout * 60) : 0) ? true : false);
                    $mem->close();
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    function select($sql, $assocMode = 1) {
        $cache = $this->read(md5($sql . $assocMode));
        if (!empty($cache)) {
            return $cache;
        } else {
            $cache = parent::select($sql, $assocMode);
            if ((preg_match('/^select/i', $sql)) and (!empty($cache))) {
                $timeout = (intval((pluginGetVariable('dbcache', 'timeout')) ? intval(pluginGetVariable('dbcache', 'timeout')) : 10));
                $this->write(md5($sql . $assocMode), $cache, $timeout);
            }
            return $cache;
        }
    }

    function record($sql, $assocMode = 1) {
        $cache = $this->read(md5($sql . $assocMode));
        if (!empty($cache)) {
            return $cache;
        } else {
            $cache = parent::record($sql, $assocMode);
            if ((preg_match('/^select/i', $sql)) and (!empty($cache))) {
                $timeout = (intval((pluginGetVariable('dbcache', 'timeout')) ? intval(pluginGetVariable('dbcache', 'timeout')) : 10));
                $this->write(md5($sql . $assocMode), $cache, $timeout);
            }
            return $cache;
        }
    }

    function query($sql) {
        $cache = $this->read(md5($sql));
        if (!empty($cache)) {
            return $cache;
        } else {
            $cache = parent::query($sql);
            if ((preg_match('/^select/i', $sql)) and (!empty($cache))) {
                $timeout = (intval((pluginGetVariable('dbcache', 'timeout')) ? intval(pluginGetVariable('dbcache', 'timeout')) : 10));
                $this->write(md5($sql), $cache, $timeout);
            }
            return $cache;
        }
    }

    function result($sql) {
        $cache = $this->read(md5($sql));
        if (!empty($cache)) {
            return $cache;
        } else {
            $cache = parent::result($sql);
            if ((preg_match('/^select/i', $sql)) and (!empty($cache))) {
                $timeout = (intval((pluginGetVariable('dbcache', 'timeout')) ? intval(pluginGetVariable('dbcache', 'timeout')) : 10));
                $this->write(md5($sql), $cache, $timeout);
            }
            return $cache;
        }
    }

}

global $mysql;
$mysql = new DBCache;
?>