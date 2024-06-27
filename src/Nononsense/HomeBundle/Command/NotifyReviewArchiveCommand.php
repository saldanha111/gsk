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
class NotifyReviewArchiveCommand extends ContainerAwareCommand
{
	//Poner revisión un día al año. 1 de Enero
	protected function configure(){
		$this
		->setName('gsk:NotifyReviewArchive')
		->setDescription('Notificación anual de revisión de archivos');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$em = $this->getContainer()->get('doctrine')->getManager();
		
        $subseccionObj = $em->getRepository('NononsenseUserBundle:Subsecciones')->findOneByNameId("archive_agent");
        $array_users=array();
		$groups = $em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('subseccion'=>$subseccionObj));
		foreach($groups as $group){
			foreach($group->getGroup()->getUsers() as $gu){
				$array_users[]=$gu->getUser()->getId();
			}
		}		

		$subject = 'Revisión anual pendiente de realizar.';
	    $message = 'Recuerde hacer la revisión periódica de aquellos archivos que se vayan a destruir dentro de archivo y retención';

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