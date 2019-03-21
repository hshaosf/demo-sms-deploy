<?php

require('../vendor/autoload.php');
require('../src/DemoSMSDeploy.php');

$demo = new DemoSMSDeploy();

$body = isset($_REQUEST['Body'])?$_REQUEST['Body']:'';

$msg = 'Hello!';

if(!empty($body)){
    $unicodes = array('1F44D', '1F680');
    preg_match('/[\x{'.implode('}\x{', $unicodes).'}]/u', $body, $matches);
    if(!empty($matches[0])){
        $input = explode(' ', preg_replace("/[^A-Za-z0-9- ]/", '', $body));
        if(!empty($input) && count($input)){
            
            $login_msg=$demo->terminus(array('auth:login', '--machine-token', getenv('terminus_token')));
            if($login_msg=='[notice] Logging in via machine token.'){
                $site_env = getenv('site') . '.'.strtolower($input[0]);
                $site_url = $demo->terminus(array('env:view', $site_env, '--print'));

                $deploy_msg=$demo->terminus(array('env:deploy', $site_env, '--note=Deploy from SMS', '--cc'));
                if($deploy_msg=='[notice] There is nothing to deploy.'){
                    $msg = trim(str_replace('[notice]', '', $deploy_msg));
                }else{
                    $demo->log('deploy_msg:'.$deploy_msg);
                    $msg = $deploy_msg;
                    $msg_array = explode("\n", $deploy_msg);
                    foreach($msg_array as $m){
                        $m = trim($m);
                        if(strpos($m, '[notice] Deployed code')!==false){
                            $msg = trim(str_replace('[notice]', '', $m));
                            break;
                        }
                    }

                }
            }

            $demo->log('msg:'.$msg);
            if(strpos($msg, 'error')!==false){
                $msg = 'Uh oh. Try again later.';
            }
            $msg = preg_replace("/[^A-Za-z0-9- .]/", '', $msg);
            if(!empty($site_url)){
                $msg .= "\n" . $site_url;
            }
        }
    }   
}

$demo->send($msg, $_REQUEST['From'], $_REQUEST['To']);

$demo->log($body);
$demo->log('=hshaosf');

