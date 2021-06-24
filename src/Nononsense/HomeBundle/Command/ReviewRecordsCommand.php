<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
* 
*/
class ReviewRecordsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:review-records')
		->setDescription('Revisar registros bloqueados.')
	    ->addOption(
            'msg',
            InputOption::VALUE_NONE
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$steps = $this->getSteps();

		if ($steps) {

			$users = $this->getUsers();

	    	$subject = 'Registros bloqueados';
	        $message = 'Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte o algún otro FLL. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.implode('<br>', $steps);
	        $baseUrl = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_cv_search')."?blocked=1";

		    foreach ($users as $key => $user) {
	            if ($this->getContainer()->get('utilities')->sendNotification($user['email'], $baseUrl, "", "", $subject, $message)) {
	                
	                $output->writeln(['Mensaje enviado: '.$user['email']]);

	                if ($input->getOption('msg')) {
	                	$output->writeln(['Asunto: '.$subject]);	
	                	$output->writeln(['Cuerpo del mensaje: '.$message]);
	                	$output->writeln(['']);	
	                }

	            }else{

	            	$output->writeln(['<error>Error: '.$user['email'].'</error>']);
	            }
		    }

	    }else{
	    	$output->writeln(['<comment>Ningún registro bloqueado</comment>']);
	    }

	    $output->writeln(['<info>Proceso completado</info>']);	
	}

	protected function getSteps(){

		$em = $this->getContainer()->get('doctrine')->getManager();

	    $qb 		= $em->createQueryBuilder();
	    $instancias = $qb->select('i')
	    				->from('NononsenseHomeBundle:CVRecords', 'i')
	    				->where('i.modified <= :modified')
	    				->setParameter('modified', new \DateTime('-8 hour'))
	    				->andWhere('i.inEdition = 1')
	    				->getQuery()
	    				->getResult();

	    if ($instancias) {				
		    foreach ($instancias as $key => $instancia) {
	    		$instancia->setBlocked(1);
	    		$em->persist($instancia);
	    		$steps[] = $instancia->getId();
		    }

		    $em->flush();

		    return $steps;
		}

		return false;
	}

	protected function getUsers(){

		$em = $this->getContainer()->get('doctrine')->getManager();

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