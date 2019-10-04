<?php 
        error_reporting(E_ALL);

        // $operator     = $argv['1'];
        // $msisdn       = $argv['2'];
        // $msg          = $argv['3'];
        // $date         = $argv['4'];
        // $timestamp    = $argv['5'];
        // $shortcode    = $argv['6'];
        // $keyword      = $argv['7'];
        
        $operator     = $_GET["opreator"];
        $msisdn       = $_GET["msisdn"];
        $msg          = $_GET["sms_received"];
        $date         = $_GET["date"];
        $timestamp    = $_GET["timestamp"];
        
        echo $msisdn."|".$msg."|".$timestamp;

        // $url  ="http://61.5.156.102/FinancialPortalAPI/FinancialPortal/MOData.aspx?timestamp=$timestamp&msisdn=$msisdn&shortcode=$shortcode&keyword=$keyword";
        // $data = file_get_contents($url);

        $url = 'https://www.creativejin-labs.com/sms-campaign-system/tel-services-or-notification';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);  // set post data to true
        curl_setopt($ch, CURLOPT_POSTFIELDS,"opreator=".$operator."&msisdn=".$msisdn."&sms_received=".$msg."&date=".$date."&timestamp=".$timestamp."&username=apiUser&password=TelApi12345"); // post data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $json = curl_exec($ch);
        curl_close ($ch);

        // $obj = json_decode($json);

        // if ($obj->{'code'} == '200')
        // {
        //     print_r($obj);die;
        // }
        // else
        // {
        //    print_r($obj);die;
        // }

        echo $json;
?>