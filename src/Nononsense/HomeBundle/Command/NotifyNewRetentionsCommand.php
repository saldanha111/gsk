<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Nononsense\UtilsBundle\Classes\Utils;

/**
* 
*/
class NotifyNewRetentionsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:notifyNewRetentions')
		->setDescription('Notificar archivos que se pueden borrar');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$em = $this->getContainer()->get('doctrine')->getManager();
		$array_users=array();
		$array_groups=array();

		$templates = $em->getRepository('NononsenseHomeBundle:TMTemplates')->list("list",array("retention_type" =>1, "notify_retention" => 1));
		foreach($templates as $template){
			if(array_key_exists("confirmUser", $template)){
				$array_users[]=$template["confirmUser"];
			}
			if(array_key_exists("confirmGroup", $template)){
				$array_groups[]=$template["confirmGroup"];
			}
		}

		$records = $em->getRepository('NononsenseHomeBundle:CVRecords')->list("list",array("retention_type" =>2, "notify_retention" => 1));
		foreach($records as $record){
			if(array_key_exists("confirmUser", $record)){
				$array_users[]=$record["confirmUser"];
			}
			if(array_key_exists("confirmGroup", $record)){
				$array_groups[]=$record["confirmGroup"];
			}
		}

		$groups = $em->getRepository('NononsenseGroupBundle:Groups')->findby(array("id"=>$array_groups));
		foreach($groups as $group){
			foreach($group->getUsers() as $gu){
				$array_users[]=$gu->getUser()->getId();
			}
		}		

		$subject = 'Hay registros en retención que ya se pueden borrar';
	    $message = 'Existen registros en retención que han llegado a su fecha de destrucción y que por tanto se pueden borrar. Para ello acceda al apartado de Retención y destrucción y a continuación en plantillas o cumplimentaciones y filtre por Acción como "Solo vencidos"';

		$users = $em->getRepository('NononsenseUserBundle:Users')->findby(array("id"=>$array_users));
		foreach($users as $user){
			if ($this->getContainer()->get('utilities')->sendNotification($user->getEmail(), NULL, "", "", $subject, $message)) {
                $output->writeln(['Mensaje enviado: '.$user->getEmail()]);
            }else{
            	$output->writeln(['<error>Error: '.$user->getEmail().'</error>']);
            }
		}
	}

}