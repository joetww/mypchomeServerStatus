<?php

    putenv("LC_ALL=en_US.UTF-8");
    putenv("LANGUAGE=en_US.UTF-8");

    if(php_sapi_name() == 'cli'){
        //這是cli模式
        $startFunction = 'main';
        $o = new monitor();
        if($_SERVER['argc'] > 1 && $_SERVER['argv'][1] != ''){
            if(!method_exists($o, $_SERVER['argv'][1])){
                $f = $startFunction;
            }else
                $f = $_SERVER['argv'][1];
        }else
            $f = $startFunction;

        $o->{$f}();
    }else{
        $startFunction = 'main';
        $o = new monitored();
        $f = $_REQUEST['ACT'];
        if($f != '')
        {
            if(!method_exists($o, $f))
            {
                $f = $startFunction;
            }
        }else{
            $f = $startFunction;
        }
        $o->{$f}();
    }

    exit();

    class monitor{
        private $config = __DIR__ . '/config_dooStatus.php';
        private $urls;
        private $jsonFile = __DIR__ . '/statusJson.json';

        function main(){
            echo "Do Nothing";
        }

        function _isCurl(){
            return function_exists('curl_version');
        }

        function getInfo(){
            if(!$this->_isCurl()) die ("Need cUrl support.");
            if(file_exists($this->config)){
                require_once($this->config);
            }else{
                die("缺少設定檔 $this->config");
            }
            $data = array();
            if(count($url) === 0) die("請在 $this->config 設定要檢查的url");
            $this->urls = $url;
            $ch = curl_init();
            //print_r($this->urls);
            //            for($i = 0; $i < count($this->urls); $i++)
            foreach($this->urls as $groupId => $groupVal)
            {
                foreach($groupVal as $v){
                    curl_setopt($ch, CURLOPT_URL, $v);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $output = curl_exec($ch);
                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $s = count($data);
                    if(curl_errno($ch)){
                        $hostdata = new stdClass();
                        $hostinfo = parse_url($v);
                        $hostdata->{$hostinfo['host']} = new stdClass();
                        foreach(array('systemtime', 'cpu', 'established', 'tasks',
                            'loadavg_1m', 'loadavg_5m', 'loadavg_15m', 'mem_used', 'mem_free', 'swap_used', 'swap_free') as $val){
                            $hostdata->{$hostinfo['host']}->{$val} = 'null';
                        }
                        $hostdata->{$hostinfo['host']}->result = ''.$status. ' / ' . $this->curlErrNo(curl_errno($ch));
                        $hostdata->{$hostinfo['host']}->group = $groupId;
                    }else{
                        $hostdata = json_decode($output);
                        foreach($hostdata as $k => $v){
                            $hostdata->{$k}->result = 'ok';
                            $hostdata->{$k}->group = $groupId;
                        }
                    }
                    $data[$s] = $hostdata;
                }
            }
            curl_close($ch);
            //echo json_encode($data);
            file_put_contents($this->jsonFile, json_encode($data));
//            print_r(json_decode(json_encode($data)));
        }

        function curlErrNo($no){
            $error_codes = array(
                1 => 'CURLE_UNSUPPORTED_PROTOCOL',
                2 => 'CURLE_FAILED_INIT',
                3 => 'CURLE_URL_MALFORMAT',
                4 => 'CURLE_URL_MALFORMAT_USER',
                5 => 'CURLE_COULDNT_RESOLVE_PROXY',
                6 => 'CURLE_COULDNT_RESOLVE_HOST',
                7 => 'CURLE_COULDNT_CONNECT',
                8 => 'CURLE_FTP_WEIRD_SERVER_REPLY',
                9 => 'CURLE_REMOTE_ACCESS_DENIED',
                11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
                13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
                14=>'CURLE_FTP_WEIRD_227_FORMAT',
                15 => 'CURLE_FTP_CANT_GET_HOST',
                17=> 'CURLE_FTP_COULDNT_SET_TYPE',
                18 => 'CURLE_PARTIAL_FILE',
                19 => 'CURLE_FTP_COULDNT_RETR_FILE',
                21 => 'CURLE_QUOTE_ERROR',
                22 => 'CURLE_HTTP_RETURNED_ERROR',
                23 => 'CURLE_WRITE_ERROR',
                25 => 'CURLE_UPLOAD_FAILED',
                26 => 'CURLE_READ_ERROR',
                27 => 'CURLE_OUT_OF_MEMORY',
                28 => 'CURLE_OPERATION_TIMEDOUT',
                30 => 'CURLE_FTP_PORT_FAILED',
                31 => 'CURLE_FTP_COULDNT_USE_REST',
                33 => 'CURLE_RANGE_ERROR',
                34 => 'CURLE_HTTP_POST_ERROR',
                35 => 'CURLE_SSL_CONNECT_ERROR',
                36 => 'CURLE_BAD_DOWNLOAD_RESUME',
                37 => 'CURLE_FILE_COULDNT_READ_FILE',
                38 => 'CURLE_LDAP_CANNOT_BIND',
                39 => 'CURLE_LDAP_SEARCH_FAILED',
                41 => 'CURLE_FUNCTION_NOT_FOUND',
                42 => 'CURLE_ABORTED_BY_CALLBACK',
                43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
                45 => 'CURLE_INTERFACE_FAILED',
                47 => 'CURLE_TOO_MANY_REDIRECTS',
                48 => 'CURLE_UNKNOWN_TELNET_OPTION',
                49 => 'CURLE_TELNET_OPTION_SYNTAX',
                51 => 'CURLE_PEER_FAILED_VERIFICATION',
                52 => 'CURLE_GOT_NOTHING',
                53 => 'CURLE_SSL_ENGINE_NOTFOUND',
                54 => 'CURLE_SSL_ENGINE_SETFAILED',
                55 => 'CURLE_SEND_ERROR',
                56 => 'CURLE_RECV_ERROR',
                58 => 'CURLE_SSL_CERTPROBLEM',
                59 => 'CURLE_SSL_CIPHER',
                60 => 'CURLE_SSL_CACERT',
                61 => 'CURLE_BAD_CONTENT_ENCODING',
                62 => 'CURLE_LDAP_INVALID_URL',
                63 => 'CURLE_FILESIZE_EXCEEDED',
                64 => 'CURLE_USE_SSL_FAILED',
                65 => 'CURLE_SEND_FAIL_REWIND',
                66 => 'CURLE_SSL_ENGINE_INITFAILED',
                67 => 'CURLE_LOGIN_DENIED',
                68 => 'CURLE_TFTP_NOTFOUND',
                69 => 'CURLE_TFTP_PERM',
                70 => 'CURLE_REMOTE_DISK_FULL',
                71 => 'CURLE_TFTP_ILLEGAL',
                72 => 'CURLE_TFTP_UNKNOWNID',
                73 => 'CURLE_REMOTE_FILE_EXISTS',
                74 => 'CURLE_TFTP_NOSUCHUSER',
                75 => 'CURLE_CONV_FAILED',
                76 => 'CURLE_CONV_REQD',
                77 => 'CURLE_SSL_CACERT_BADFILE',
                78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
                79 => 'CURLE_SSH',
                80 => 'CURLE_SSL_SHUTDOWN_FAILED',
                81 => 'CURLE_AGAIN',
                82 => 'CURLE_SSL_CRL_BADFILE',
                83 => 'CURLE_SSL_ISSUER_ERROR',
                84 => 'CURLE_FTP_PRET_FAILED',
                84 => 'CURLE_FTP_PRET_FAILED',
                85 => 'CURLE_RTSP_CSEQ_ERROR',
                86 => 'CURLE_RTSP_SESSION_ERROR',
                87 => 'CURLE_FTP_BAD_FILE_LIST',
                88 => 'CURLE_CHUNK_FAILED'
            );
            return $error_codes[$no];
        }
    }

    class monitored{
        private $user = 'www-data';
        private $home = '/var/www/html';
        private $topData = '';
        private $memData = '';
        private $jsonFile = __DIR__ . '/statusJson.json';
        private $mainHtml = __DIR__ . '/statusInfo.tpl';

        function main(){
            //echo "Hello World";
            require_once($this->mainHtml);
        }

        function getJsonData(){
            echo "myServers = " .`cat $this->jsonFile`."; CreateTableFromJSON(); ";
        }

        function getData(){
            $this->getInfo();
            echo $this->formatStatus();
        }

        function getInfo(){
            $this->getTop();
            $this->getMem();
        }

        function getTop(){
            putenv("HOME=$this->home");
            putenv("COLUMNS=160");
            $this->topData = `top -bn1 -u $this->user`;
            return true;
        }

        function getMem(){
            $this->memData = `cat /proc/meminfo | grep -P '^(MemTotal|SwapTotal|MemFree|SwapFree|Buffers|Cached|Slab):'`;
            return true;
        }

        function formatStatus()
        {
            $hostname = php_uname('n');
            $data[$hostname]['systemtime'] = time();
            preg_match_all('/^processor\s+:\s+\d+/', `cat /proc/cpuinfo`, $m);
            $data[$hostname]['cpu'] = count($m);
            $data[$hostname]['established'] = 'none';
            preg_match('/Tasks:\s+(\d+)\s+total,/i', $this->topData, $m, PREG_OFFSET_CAPTURE);
            $data[$hostname]['tasks'] = (int)$m[1][0];
            preg_match('/load average:\s+(\d*\.?\d+),\s+(\d*\.?\d+),\s+(\d*\.?\d+)/i', $this->topData, $m, PREG_OFFSET_CAPTURE);
            $data[$hostname]['loadavg_1m'] = (float)$m[1][0];
            $data[$hostname]['loadavg_5m'] = (float)$m[2][0];
            $data[$hostname]['loadavg_15m'] = (float)$m[3][0];

            preg_match_all('/(\w*?):\s+(\d+)\s+kB/', $this->memData, $m, PREG_SET_ORDER);
            $mem = array();
            for($i = 0; $i < count($m); $i++)
            {
                $mem[$m[$i][1]] = (int)$m[$i][2];
            }
            $data[$hostname]['mem_used'] = $mem['MemTotal'] - $mem['MemFree'] - $mem['Buffers'] - $mem['Cached'] - $mem['Slab'];
            $data[$hostname]['mem_free'] = $mem['MemFree'] + $mem['Buffers'] + $mem['Cached'] + $mem['Slab'];
            $data[$hostname]['swap_used'] = $mem['SwapTotal'] - $mem['SwapFree'];
            $data[$hostname]['swap_free'] = $mem['SwapFree'];



            return json_encode($data);
        }

        function showInfo(){
            $this->showTop();
            $this->showMem();
        }

        function showTop(){
            echo $this->topData;
            return true;
        }

        function showMem(){
            echo $this->memData;
            return true;
        }
    }
?>