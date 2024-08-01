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
class NotifyArchiveDestructionRetentionExpiredCommand extends ContainerAwareCommand
{
	//Poner 1 al día
	protected function configure(){
		$this
		->setName('gsk:NotifyArchiveDestructionRetentionExpired')
		->setDescription('Destrucción de registros pendiente de realizar');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$em = $this->getContainer()->get('doctrine')->getManager();
		$from = new \DateTime();
		$from->sub(new \DateInterval('P6M'));

		$link = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_archive_records')."?retentionAction=3";

        $subseccionObj = $em->getRepository('NononsenseUserBundle:Subsecciones')->findOneByNameId("archive_agent");
        $array_users=array();
		$groups = $em->getRepository('NononsenseUserBundle:GroupsSubsecciones')->findBy(array('subseccion'=>$subseccionObj));
		foreach($groups as $group){
			$areasGroups = $em->getRepository('NononsenseHomeBundle:AreasGroups')->findBy(array('agroup' => $group->getGroup()));
			foreach($areasGroups as $areaGroup){
				$count = $em->getRepository('NononsenseHomeBundle:ArchiveRecords')->list("count",array("area" => $areaGroup->getArea()->getId(),"destructionUntil" => $from->format('d/m/Y')));
				if($count>0){
					foreach($group->getGroup()->getUsers() as $gu){
						$array_users[]=$gu->getUser()->getId();
					}
				}
			}
		}		

		$subject = 'Destrucción de registros pendiente de realizar';
	    $message = 'Hay registros que ya han superado por 6 meses su fecha de desctrucción. Por favor entre en la sección de "Archivo y retención -> Consulta de registros en el sistema" y filtre en "Acción" por "Destrucción de registros"';

		$users = $em->getRepository('NononsenseUserBundle:Users')->findby(array("id"=>$array_users));

		foreach($users as $user){
			if ($this->getContainer()->get('utilities')->sendNotification($user->getEmail(), $link, "", "", $subject, $message)) {
                $output->writeln(['Mensaje enviado: '.$user->getEmail()]);
            }else{
            	$output->writeln(['<error>Error: '.$user->getEmail().'</error>']);
            }
		}
	}

}