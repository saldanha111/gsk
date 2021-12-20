<?php
namespace Nononsense\HomeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Nononsense\HomeBundle\Entity\CVSecondWorkflowStates;
use Nononsense\HomeBundle\Entity\SpecificGroups;
use Nononsense\HomeBundle\Entity\CVSecondWorkflow;
use Nononsense\GroupBundle\Entity\GroupUsers;

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
		$ids_eco=array();
		$aux_message_eco="";
		foreach($areas as $area){
			$ids=array();
			$qb 		= $em->createQueryBuilder();
		   	$records = $qb->select('i')
		    				->from('NononsenseHomeBundle:CVRecords', 'i')
		    				->leftJoin("i.template", "t")
		    				->andWhere('i.openDate <= :modified')
		    				->andWhere('i.inEdition = 1')
		    				->andWhere('(i.blocked = 0 OR i.blocked IS NULL)')
		    				->andWhere('IDENTITY(t.area) = :area')
		    				->setParameter('modified', new \DateTime('-2 hour'))
		    				->setParameter('area', $area->getId())
		    				->getQuery()
		    				->getResult();
		   	$aux_message="";
		    if ($records) {	
	    		$typesw = $em->getRepository(CVSecondWorkflowStates::class)->findOneBy(array("id" => "2"));
		    	$specific = $em->getRepository(SpecificGroups::class)->findOneBy(array("name" => "ECO"));
            		$other_group = $specific->getGroup();

		    	foreach ($records as $key => $record) {
		    		$record->setBlocked(1);
		    		$em->persist($record);
		    		$ids[] = $record->getId();
		    		$aux_message.=$record->getId()." - Código: ".$record->getTemplate()->getId()." - Título: ".$record->getTemplate()->getName()." - Edición: ".$record->getTemplate()->getNumEdition()."<br>";

		            $sworkflow = new CVSecondWorkflow();
			        $sworkflow->setRecord($record);
			        $sworkflow->setGroup($other_group);
			        $sworkflow->setNumberSignature(1);
			        $sworkflow->setType($typesw);
			        $sworkflow->setSigned(FALSE);
			        $em->persist($sworkflow);

			        if($area->getFll()){
			            $sworkflow = new CVSecondWorkflow();
				        $sworkflow->setRecord($record);
				        $sworkflow->setUser($area->getFll());
				        $sworkflow->setNumberSignature(2);
				        $sworkflow->setType($typesw);
				        $sworkflow->setSigned(FALSE);
				        $em->persist($sworkflow);
			        }

			        if (!in_array($record->getId(), $ids_eco)) {
			        	$aux_message_eco.=$record->getId()." - Código: ".$record->getTemplate()->getId()." - Título: ".$record->getTemplate()->getName()." - Edición: ".$record->getTemplate()->getNumEdition()."<br>";
			        	$ids_eco[]=$record->getId();
			        }
			    }
			}


			if ($ids) {

		    	$subject = 'Registros bloqueados';
		        $message = 'Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.$aux_message;
		        $baseUrl = trim($this->getContainer()->getParameter('cm_installation'), '/').$this->getContainer()->get('router')->generate('nononsense_cv_search')."?blocked=1";

	            if($area->getFll()){
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
		        }

		    }else{
		    	$output->writeln(['<comment>Ningún registro bloqueado para el area '.$area->getName().'</comment>']);
		    }
		}

		$specific = $em->getRepository(SpecificGroups::class)->findOneBy(array("name" => "ECO"));
		$eco=$specific->getGroup();
        $eco_users = $em->getRepository(GroupUsers::class)->findBy(["group" => $eco]);
        $message = 'Los siguientes registros han sido bloqueados y necesitan ser gestionados por su parte o algún otro FLL. Acceda al siguiente  Link para gestionar los bloqueos.<br><br>'.$aux_message_eco;
    	foreach ($eco_users as $eco_user) {
    		if ($this->getContainer()->get('utilities')->sendNotification($eco_user->getUser()->getEmail(), $baseUrl, "", "", $subject, $message)) {

                $output->writeln(['Mensaje enviado: '.$eco_user->getUser()->getEmail()]);

                if ($input->getOption('msg')) {
                	$output->writeln(['Asunto: '.$subject]);	
                	$output->writeln(['Cuerpo del mensaje: '.$message]);
                	$output->writeln(['']);	
                }

            }else{

            	$output->writeln(['<error>Error: '.$area->getFll()->getEmail().'</error>']);
            }
    	}

		$em->flush();

	    $output->writeln(['<info>Proceso completado</info>']);	
	}
}