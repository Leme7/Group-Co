<?php

namespace src\Admin\Controller\Index;

use Controller;
use Request;
use AsyncService;
use AsyncMysql;

class IndexController extends Controller
{
    public function addNodeAction(Request $request)
    {	
    	$ip = $request->request->get('ip');
    	$port = $request->request->get('port');
    	$services = $request->request->get('services');

    	if ($ip =="" || $port == "" || $services == "") {
    		yield 0;
    	}

    	$isExist = (yield AsyncMysql::query("SELECT id FROM `nodes` WHERE ip = '{$ip}' and port = '{$port}' "));

    	if ($isExist->getResult()) {
    		$res = (yield AsyncMysql::query("UPDATE `nodes`  SET `status` = 'active', `services` = '{$services}' WHERE ip = '{$ip}' and port = '{$port}'"));
    	} else {
    		$res = (yield AsyncMysql::query("INSERT INTO `nodes` (`ip`, `port`, `status`, `services`) VALUES ('{$ip}', '{$port}', 'active', '{$services}')"));
    	}

        if ($res->getResult()) {
        	yield 1;
        }
        yield 0;
    }

    public function removeNodeAction(Request $request)
    {	
    	$ip = $request->request->get('ip');
    	$port = $request->request->get('port');

    	if ($ip =="" || $port == "") {
    		yield 0;
    	}

    	$isExist = (yield AsyncMysql::query("SELECT id FROM `nodes` WHERE ip = '{$ip}' and port = '{$port}' "));

    	if ($isExist && $isExist->getResult()) {
    		$res = (yield AsyncMysql::query("UPDATE `nodes`  SET `status` = 'close' WHERE ip = '{$ip}' and port = '{$port}'"));
    		if ($res && $res->getResult()) {
    			yield 1;
    		}
    		yield 0;
    	} else {
    		yield 1;
    	}
    }

    // public function sleepNodeAction(Request $request)
    // {
    //     $ip = $request->request->get('ip');
    //     $port = $request->request->get('port');

    //     $service = new AsyncService($ip, $port);
    //     $res = (yield $service->call('sleep'));
    //     if ($res) {
    //         $res = (yield \AsyncMysql::query("UPDATE `nodes`  SET `status` = 'sleep' WHERE ip = '{$ip}' and port = '{$port}'"));
    //     }

    //     yield 1;
    // }

    public function closeNodeAction(Request $request)
    {
        $ip = $request->request->get('ip');
        $port = $request->request->get('port');

        $service = new AsyncService($ip, $port);
        $res = (yield $service->call('close'));
        if ($res) {
            $res = (yield AsyncMysql::query("UPDATE `nodes`  SET `status` = 'close' WHERE ip = '{$ip}' and port = '{$port}'"));
        }

        yield 1;
    }

    public function deleteNodeAction(Request $request)
    {
        $ip = $request->request->get('ip');
        $port = $request->request->get('port');

        $service = new AsyncService($ip, $port);
        $res = (yield $service->call('close'));
        if ($res) {
            $res = (yield AsyncMysql::query("DELETE FROM `nodes` WHERE ip = '{$ip}' and port = '{$port}'"));
        }
        
        yield 1;
    }
}

