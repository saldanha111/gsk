<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
* 
*/
class PendingValidationsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:pending-validations')
		->setDescription('Notificación de documentos pendientes de verificar.')
	    ->addArgument(
            'msg',
            InputArgument::OPTIONAL
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$pendingWorkflows = $this->getWorkflows();

		if ($pendingWorkflows) {
			
			$users = $this->getUsers();
		
			$subject = "Documento pendiente de verificar";
	        $message = "Los siguientes registros: " . $pendingWorkflows . " están pendientes de verificar en el sistema";
	        $baseUrl = $this->getContainer()->get('router')->generate('nononsense_search', array(),TRUE);

	        foreach ($users as $key => $user) {
	        	//$this->get('utilities')->sendNotification($email, $baseUrl, "", "", $subject, $message)
	        	if (true) {
	        		
	        		$output->writeln(['Mensaje enviado: '.$user['email']]);

	        		if ($input->getArgument('msg') !== null && $input->getArgument('msg')) {
	                	$output->writeln(['Asunto: '.$message]);	
	                	$output->writeln(['Cuerpo del mensaje: '.$message]);
	                	$output->writeln(['']);	
	                }

	        	}else{

	        		$output->writeln(['<error>Error: '.$user['email'].'</error>']);
	        	}
	        }
    	}else{

    		$output->writeln(['<comment>Ningún documento pendiente</comment>']);
    	}

        $output->writeln(['<info>Proceso completado</info>']);
	}

	protected function getWorkflows(){

		$em = $this->getContainer()->get('doctrine')->getManager();

		$result = $em->getRepository('NononsenseHomeBundle:InstanciasWorkflows')->findBy(array("status" => [4,7]));

		$pendingWorkflows = implode(', ', array_map(function($c){ return $c->getId(); }, $result));

		return $pendingWorkflows;
	}

	protected function getUsers(){

		$em 	= $this->getContainer()->get('doctrine')->getManager();

		$qb 	= $em->createQueryBuilder();
		$query 	= $qb->select('u.email')
		   ->distinct()
		   ->from('NononsenseGroupBundle:GroupUsers', 'gu')
		   ->join('gu.group', 'g')
		   ->join('gu.user', 'u')
		   ->where("g.tipo = 'FLL'")
		   ->getQuery();

		$users = $query->getResult();

		return $users;
	}
}