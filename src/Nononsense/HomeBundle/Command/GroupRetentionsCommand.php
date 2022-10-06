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
class GroupRetentionsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:groupRetentions')
		->setDescription('Agrupar registros de retención para certificación');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		$retentions = $this->getPendingRetentions();

		$em2 = $this->getContainer()->get('doctrine')->getManager();

		if ($retentions) {
			
			$url 	= $this->getContainer()->getParameter('api3.url').'/hash';
			$header = ['apiKey:'.$this->getContainer()->getParameter('api3.key')];
			$html="<style>
			        table {
			            font-size: 10px;
			        }
        </style><table style='font-size:11px'><tr><th>Fecha</th><th>Tipo</th><th>ID</th><th>Ácción</th><th>Comentario</th></tr>";
			$group=uniqid();
			foreach ($retentions as $key => $retention) {
				if($retention->getRetentionTemplate()){
					$type="Plantilla";
					$id=$retention->getRetentionTemplate();
				}
				else{
					if($retention->getRetentionRecord()){
						$type="Cumplimentación";
						$id=$retention->getRetentionRecord();
					}
					else{
						$type="Categoría retención";
						$id=$retention->getRetentionCategory()->getName();
					}
				}
				$html.="<tr>
					<td>".(($retention->getModified()) ? $this->getContainer()->get('utilities')->sp_date($retention->getModified()->format('d/m/Y H:i:s')) : '')."</td>
                    <td>".$type."</td>
                    <td>".$id."</td>
                    <td>".$retention->getRetentionAction()->getName()."</td>
                    <td>".$retention->getDescription()."</td>
                </tr>";

                $retention->setGroupId($group);
                $em2->persist($retention);
			}

			$html.="</table>";

			try {
				$file = Utils::generatePdf($this->getContainer(), 'GSK - Retención', 'Listado de registros', $html, 'retention', $this->getContainer()->getParameter('crt.root_dir'));
	            Utils::setCertification($this->getContainer(), $file, 'retention', $retention->getGroupId());
	            $em2->flush();
			} catch (\Exception $e) {
				$subject = 'Error de agrupación en retención';
				$message = 'Error durante la agrupación de certificación '.$e->getMessage();
				$this->getContainer()->get('utilities')->sendNotification('sergio.saldana@nodalblock.com', false, false, false, $subject, $message);
				$output->writeln(['<error>'.$e->getMessage().'</error>']);
			}
		}
	}

	protected function getPendingRetentions(){

		$em2 = $this->getContainer()->get('doctrine')->getManager();

	    $qb 			= $em2->createQueryBuilder();
	    $retentions = $qb->select('rts')
	    					->from('NononsenseHomeBundle:RetentionSignatures', 'rts')
	    					->where('rts.groupId is NULL')
	    					->getQuery()
	    					->getResult();

		return $retentions;
	}
}