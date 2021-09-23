<?php

namespace Nononsense\HomeBundle\Command;

use Aws\Sns\SnsClient;
use Aws\Result;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class testSmsCommand extends ContainerAwareCommand
{
    private $out;
    protected function configure()
    {
        $this->setName('gsk:testSms')->setDescription('testsms');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;
        $this->out->writeln("start");
        $this->sendBySMS('+34647875631', 'Mensaje de test GSK');
        $output->writeln("fin");
    }

    private function getClientSmsAws()
    {
        try{
            $container = $this->getContainer();
            $region = $container->getParameter("sns_region");
            $this->out->writeln('Region => ' . $region);
            $key = $container->getParameter("sns_key");
            $this->out->writeln('Key => ' . $key);
            $secret = $container->getParameter("sns_secret");
            $this->out->writeln('Secret => ' . $secret);

            return new SnsClient(
                [
                    'version' => 'latest',
                    'region' => $region,
                    'credentials' => [
                        'key' => $key,
                        'secret' => $secret
                    ],
                    'http'    => [
                        'verify' => 'https://curl.se/ca/cacert.pem'
                    ]
                ]
            );
        }catch(Exception $e){
            $this->out->writeln('Error client => ' . $e->getMessage());
            return false;
        }
    }

    private function sendBySMS($phoneNumber, $textMessage)
    {
        $client = $this->getClientSmsAws();
        $this->out->writeln('Client => ' . json_encode($client));
        try {
            $snsClientResult = $client->publish(
                [
                    'Message' => $textMessage,
                    'PhoneNumber' => $phoneNumber,
                    'MessageStructure' => 'SMS',
                    'MessageAttributes' => [
                        'AWS.SNS.SMS.SenderID' => [
                            'DataType' => 'String',
                            'StringValue' => 'GSK',
                        ],
                        'AWS.SNS.SMS.SMSType' => [
                            'DataType' => 'String',
                            'StringValue' => 'Transactional', // Transactional
                        ]
                    ]
                ]
            );
            $this->out->writeln('snsClientResult => ' . $snsClientResult);
            foreach($snsClientResult as $key => $res){
                $this->out->writeln('result => ' . $key . ' => ' . json_encode($res));
            }
            if($snsClientResult->hasKey('MessageId')){
                $this->out->writeln('result => true');
                return true;
            }
        } catch (Exception $e) {
            $this->out->writeln('error => ' . json_encode($e));
            $this->out->writeln('errorMessage => ' . $e->getMessage());
        }

        $this->out->writeln('result => false');
        return false;
    }
}
