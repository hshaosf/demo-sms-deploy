<?php
use Pantheon\Terminus\Terminus;
use Pantheon\Terminus\Config\DefaultsConfig;
use Pantheon\Terminus\Config\YamlConfig;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\StreamOutput;
use Twilio\Rest\Client;
use Twilio\Twiml;

class DemoSMSDeploy{

    protected $sms_client;

    public function __construct(){
        $env = getenv('environment');
        if(empty($env)){
            $dotenv = Dotenv\Dotenv::create(__DIR__.'/../');
            $dotenv->load();
        }
        $this->sms_client = new Client(getenv('sms_sid'), getenv('sms_token'));
        $this->log('[demo.request]'.print_r($_REQUEST,1));
    }
    public function terminus($argv){
        array_unshift($argv, 'terminus');
        $input = new ArgvInput($argv);
        $output = new StreamOutput(fopen('php://output', 'w'));

        $config = new DefaultsConfig();
        $config->extend(new YamlConfig($config->get('root') . '/config/constants.yml'));

        ob_start();
        $terminus = new Terminus($config, $input, $output);
        $status_code = $terminus->run($input, $output);
        $msg = ob_get_contents();
        ob_end_clean();
        $this->log('[demo.terminus]'.$status_code);
        $this->log('[demo.terminus]'.$msg);
        return trim($msg);
    }

    public function respond($msg){
        $this->log('[demo.respond]'.$msg);
        $client = $this->sms_client;
        $response = new Twiml;
        $response->message($msg);
        print $response;
    }

    public function send($msg, $num_client, $num_host){
        $num_client = preg_replace("/[^0-9+]/", '', $num_client);
        $num_host = preg_replace("/[^0-9+]/", '', $num_host);

        $client = $this->sms_client;
        $client->messages->create(
            $num_client,
            array(
                'from' => $num_host,
                'body' => $msg
            )
        );
    }

    public function log($msg, $type='error'){
        error_log($msg);
    }

}