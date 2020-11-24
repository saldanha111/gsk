<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
* 
*/
class ReviewRecordsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:review-records')
		->setDescription('Revisar registros bloqueados.')
	    ->addArgument(
            'msg',
            InputArgument::OPTIONAL
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output){

	    $em = $this->getContainer()->get('doctrine')->getManager();

	    $qb 		= $em->createQueryBuilder();
	    $instancias = $qb->select('iw, st')
	    				->from('NononsenseHomeBundle:InstanciasWorkflows', 'iw')
	    				//->join('iw.Steps','st')
	    				->join("iw.Steps", "st", "WITH", 'st.dependsOn = 0')
	    				->where('iw.modified <= :modified')
	    				->setParameter('modified', new \DateTime('-8 hour'))
	    				->andWhere('iw.in_edition = 1')
	    				//->andWhere('st.dependsOn = 0')
	    				->getQuery();

	    $loked 	= [];

	    foreach ($instancias->getResult() as $key => $instancia) {

    		// $steps	 = $em->getRepository('NononsenseHomeBundle:InstanciasSteps')->findOneBy(array('workflow_id' => $instancia->getId(), 'dependsOn' => 0));
    		// $loked[] = $steps->getId();

    		$instancia->setInEdition(0);
    		$instancia->setStatus(11);

    		$em->persist($instancia);
    		// $output->writeln([$instancia->getSteps()->getId()]);
    		// $output->writeln([$instancia->getId()]);

    		foreach ($instancia->getSteps() as $key => $value) {
    			if (isset($value)) {
    				$loked[] = $value->getId();
    			}
    		}
	    }

	    $em->flush();

	    if (!empty($loked)) {

	    	$qb 	= $em->createQueryBuilder();
			$query 	= $qb->select('u.email')
			   ->distinct()
			   ->from('NononsenseGroupBundle:GroupUsers', 'gu')
			   ->join('gu.group', 'g')
			   ->join('gu.user', 'u')
			   ->where("g.tipo = 'FLL'")
			   ->getQuery();

			$users = $query->getResult();
			//$output->writeln(['Enters!']);
	    }

	


	    // if (!empty($loked)) {
	    // 	$groups = $em->getRepository('NononsenseGroupBundle:Groups')->findBy(array('tipo' => 'FLL'));
	    // 	foreach ($groups as $key => $group) {
	    // 		$groupUsers = $em->getRepository('NononsenseGroupBundle:GroupUsers')->findBy(array('group' => $group));
	    // 		foreach ($groupUsers as $key => $groupUser) {
	    // 			$emails[] = $groupUser->getUser()->getEmail();
	    // 		}
	    // 	}
	    // }

	    //$emails = array_unique($emails);
	    $log_records_stand_by = implode("<br>", $loked);

	    foreach ($users as $key => $user) {
	    	$subject = "Registros bloqueados";
            $mensaje = 'Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte o algún otro FLL. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.$log_records_stand_by;
            $baseURL = $this->getContainer()->get('router')->generate('nononsense_backoffice_standby_documents_list',array(),TRUE);
            
            //$this->getContainer()->get('utilities')->sendNotification($email, $baseURL, "", "", $subject, $mensaje)	
            if (true) {
                
                $output->writeln(['Mensaje enviado: '.$user['email']]);

                if ($input->getArgument('msg') !== null && $input->getArgument('msg')) {
                	$output->writeln(['Asunto: '.$mensaje]);	
                	$output->writeln(['Cuerpo del mensaje: '.$mensaje]);
                	$output->writeln(['']);	
                }

            }else{

            	$output->writeln(['<error>Error: '.$user['email'].'</error>']);
            }
	    }

	    $output->writeln(['<info>Proceso completado</info>']);	
	}
}