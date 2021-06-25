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

		$em = $this->getContainer()->get('doctrine')->getManager();

		$areas = $em->getRepository('NononsenseHomeBundle:Areas')->findAll();
		foreach($areas as $area){
			$ids=array();
			$qb 		= $em->createQueryBuilder();
		    $records = $qb->select('i')
		    				->from('NononsenseHomeBundle:CVRecords', 'i')
		    				->leftJoin("i.template", "t")
		    				->andWhere('i.modified <= :modified')
		    				->andWhere('i.inEdition = 1')
		    				->andWhere('(i.blocked = 0 OR i.blocked IS NULL)')
		    				->andWhere('IDENTITY(t.area) = :area')
		    				->setParameter('modified', new \DateTime('-8 hour'))
		    				->setParameter('area', $area->getId())
		    				->getQuery()
		    				->getResult();
		   
		    if ($records) {				
			    foreach ($records as $key => $record) {
		    		$record->setBlocked(1);
		    		$em->persist($record);
		    		$ids[] = $record->getId();
			    }
			}


			if ($ids) {

		    	$subject = 'Registros bloqueados';
		        $message = 'Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte o algún otro FLL. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.implode('<br>', $ids);
		        $baseUrl = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_cv_search')."?blocked=1";

	           if ($this->getContainer()->get('utilities')->sendNotification($area->getFll()->getEmail(), $baseUrl, "", "", $subject, $message)) {
	                
	                $output->writeln(['Mensaje enviado: '.$area->getFll()->getEmail()]);

	                if ($input->getOption('msg')) {
	                	$output->writeln(['Asunto: '.$subject]);	
	                	$output->writeln(['Cuerpo del mensaje: '.$message]);
	                	$output->writeln(['']);	
	                }

	            }else{

	            	$output->writeln(['<error>Error: '.$area->getFll()->getEmail().'</error>']);
	            }

		    }else{
		    	$output->writeln(['<comment>Ningún registro bloqueado para el area '.$area->getName().'</comment>']);
		    }
		}
		$em->flush();

	    $output->writeln(['<info>Proceso completado</info>']);	
	}
}