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
class PendingValidationsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:pending-validations')
		->setDescription('Notificación de documentos pendientes de verificar.')
	    ->addOption(
            'msg',
            InputOption::VALUE_NONE
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$em = $this->getContainer()->get('doctrine')->getManager();

		$states = $em->getRepository('NononsenseHomeBundle:CVStates')->findBy(array("id" => array(2,4)));
		$areas = $em->getRepository('NononsenseHomeBundle:Areas')->findAll();
		$minutes=10;
		foreach($areas as $area){
			$ids=array();
			$qb 		= $em->createQueryBuilder();
		    $records = $qb->select('i')
		    				->from('NononsenseHomeBundle:CVRecords', 'i')
		    				->leftJoin("i.template", "t")
		    				->andWhere('i.state IN (:states)')
		    				->andWhere('t.minutesVerification IS NOT NULL')
		    				//->andWhere("DATE_SUB(i.modified,10,'MINUTE')<= CURRENT_TIMESTAMP()")
		    				->andWhere("(DATEDIFF(CURRENT_TIMESTAMP(), i.modified)/60)>t.minutesVerification")
		    				->andWhere('IDENTITY(t.area) = :area')
		    				->andWhere('(i.pending = 0 OR i.pending IS NULL)')
		    				//->setParameter('modified', new \DateTime('-'.$minutes.' minutes'))
		    				->setParameter('area', $area->getId())
		    				->setParameter('states', $states)
		    				->getQuery()
		    				->getResult();
		   
		    if ($records) {				
			    foreach ($records as $key => $record) {
		    		$record->setPending(1);
		    		$em->persist($record);
		    		$ids[] = $record->getId();
			    }
			}


			if ($ids) {
				$subject = 'Registros pendientes de verificar';
		        $message = 'Los siguientes registros están pendientes de verificación y necesitan ser gestionados por parte de algún verificador involucrado en su workflow correspondiente.<br><br>'.implode('<br>', $ids);
		        $baseUrl = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_cv_search')."?blocked=1";

		        if($area->getFll()){
		            if ($this->getContainer()->get('utilities')->sendNotification($area->getFll()->getEmail(), $baseUrl, "", "", $subject, $message)) {

		            	$output->writeln(['Mensaje enviado: '.$area->getFll()->getEmail()]);

		                if ($input->getOption('msg')) {
		                	$output->writeln(['Asunto: '.$subject]);	
		                	$output->writeln(['Cuerpo del mensaje: '.$message]);
		                	$output->writeln(['']);	
		                }
		            }
		            else{

		            	$output->writeln(['<error>Error: '.$area->getFll()->getEmail().'</error>']);
		            }
		        }
		        else{
			    	$output->writeln(['<comment>Hay '.count($ids).' registros pero no hay FLL para el area '.$area->getName().'</comment>']);
			    }
			}
			else{
		    	$output->writeln(['<comment>Ningún registro pendiente para el area '.$area->getName().'</comment>']);
		    }
		}
		$em->flush();

	    $output->writeln(['<info>Proceso completado</info>']);	
	}
}