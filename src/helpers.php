<?php

// 获取当前登录用户
if (! function_exists('auth_user')) {
    /**
     * Get the auth_user.
     *
     * @return mixed
     */
    function auth_user()
    {
        return app('Dingo\Api\Auth\Auth')->user();
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}

if (! function_exists('require_dir')) {
    /**
     * 加载目录
     *
     * @param string $path
     */
    function require_dir($path) {
       if ($handle = opendir($path)) {    
            while (false !== ($file = readdir($handle))) {    
                if ($file === '.' || $file === '..') {    
                    continue;    
                }

                $tmp_f = explode('.', $file);
                if (end($tmp_f) != 'php') {
                    continue;
                }

                $this_file = $path . '/' . $file;
                if (is_file($this_file)) {
                    require_once($this_file);
                }
                if (is_dir($this_file)) {
                    require_dir($this_file);
                }
            }
            closedir($handle); 
        }else{    
            echo 'Open directory failed!';    
        } 
    }
}

if (! function_exists('empty_safe')) {
    function empty_safe($var) {
        if (is_numeric($var)) {
            return false;
        }

        if ($var instanceof \Illuminate\Support\Collection) {
            return (0 >= $var->count());
        }
        
        return empty($var);
    }
}

if (! function_exists('str_equal')) {
    /**
     * 字符对比
     */
    function str_equal($foo = null, $bar = null) : bool {
        if (empty_safe($foo) || empty_safe($bar) || !is_scalar($foo) || !is_scalar($bar)) {
            return false;
        }

        return (strtolower((string) $foo) === strtolower((string) $bar));
    }
}

if (! function_exists('time_diff')) {
    /**
     * 两个日期相差的天时分秒
     */
    function time_diff($timestamp1, $timestamp2) {
        if ($timestamp2 <= $timestamp1){
            return ['day'=>0, 'hours'=>0, 'minute'=>0, 'second'=>0];
        }

        $timediff = $timestamp2 - $timestamp1;

        $second = $timediff % 60;//取余得到秒数

        $nowtime = floor($timediff/60);//转化成分钟

        $minute = $nowtime % 60;//取余得到分钟数

        $nowtime = floor($nowtime/60);//转化成小时

        $hour = $nowtime % 24;//取余得到小时数

        $nowtime = floor($nowtime/24);//转化成天数

        $day = floor($nowtime);//得到天数

        $time = [
            'day'    => intval($day),
            'hours'   => intval($hour),
            'minute' => intval($minute),
            'second' => intval($second),
        ];

        return $time;
    }
}

if (! function_exists('is_phone')) {
    function is_phone(string $phone = null) : bool {
        return $phone && is_numeric($phone) && (12000000000 <= $phone) && ($phone <= 19999999999);
    }
}
