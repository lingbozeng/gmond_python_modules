<?php
#
# APC JSON builder
# Used for Ganglia APC Status module
#
# Author: Jacob V. Rasmussen (jacobvrasmussen@gmail.com)
# Site: http://blackthorne.dk
#

header("Content-type: text/plain");

function cmp_cache_list($a, $b)
{
	return ($b['num_hits'] - $a['num_hits']);
}

function _get($cache, $keys)
{
    $ret = 0;
    foreach ($keys as $key) {
        if (isset($cache[$key])) {
            $ret = $cache[$key];
            break;
        }
    }
    return $ret;
}

if ($_SERVER["REMOTE_ADDR"] == "127.0.0.1" || TRUE)
{
	$cache = apc_cache_info();
	$mem = apc_sma_info();
	$cache['num_slots']    = _get($cache, array('num_slots', 'nslots'));
	$cache['num_hits']     = _get($cache, array('num_hits', 'nhits'));
	$cache['num_misses']   = _get($cache, array('num_misses', 'nmisses'));
	$cache['num_inserts']  = _get($cache, array('num_inserts', 'ninserts'));
	$cache['num_entries']  = _get($cache, array('num_entries', 'nentries'));
	$cache['expunges']     = _get($cache, array('expunges', 'nexpunges', 'num_expunges'));
    $cache['expunges']     = _get($cache, array('expunges', 'nexpunges', 'num_expunges'));
    $uptime                = time() - _get($cache, array('start_time', 'stime'));
    $cache['uptime']       = $uptime ? $uptime : 1; #zero divied escape
	$cache['request_rate'] = ($cache['num_hits'] + $cache['num_misses']) / $cache['uptime'];
	$cache['hit_rate'] = $cache['num_hits'] / $cache['uptime'];
	$cache['miss_rate'] = $cache['num_misses'] / $cache['uptime'];
	$cache['insert_rate'] = $cache['num_inserts'] / $cache['uptime'];
	$cache['num_seg'] = $mem['num_seg'];
	$cache['mem_size'] = $mem['num_seg'] * $mem['seg_size'];
	$cache['mem_avail'] = $mem['avail_mem'];
	$cache['mem_used'] = $cache['mem_size'] - $cache['mem_avail'];

	$cache_list = $cache['cache_list'];
	usort($cache_list, 'cmp_cache_list');

	unset($cache['cache_list']); //lots of info that we don't need for a brief status
	unset($cache['deleted_list']); // ditto

	if (@$_REQUEST['debug'] == '1')
	{
		print_r($cache);
		print_r($mem);
		print_r($cache_list);
	}
	echo json_encode($cache);
}
