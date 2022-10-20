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
class PhysicallyRemoveRetentionsCommand extends ContainerAwareCommand
{
	
	protected function configure(){
		$this
		->setName('gsk:physicallyRemoveRetentions')
		->setDescription('Eliminar registros previamente eliminados de forma física');
	}

	protected function execute(InputInterface $input, OutputInterface $output){

		// //Eliminamos cumplimentaciones
		// $records = $this->getRemovedRecords();
		// $em = $this->getContainer()->get('doctrine')->getManager();
		// if ($records) {
		// 	foreach($records as $record){
	 //            echo $record->getId();
  //           	//Eliminamos el workflow de cumplimentacion y verificación
  //           	foreach($record->getCvWorkflows() as $wf){
  //           		$em->remove($wf);
  //           	}

  //           	//Eliminamos el workflow de gxp y standby si lo hubiera
  //           	foreach($record->getCvSecondWorkflows() as $swf){
  //           		$em->remove($swf);
  //           	}

  //           	//Eliminamos el audittrail de cumplimentacion y verificacion
  //           	foreach($record->getCvSignatures() as $audittrail){
  //           		//Eliminamos el historial de campos modificados
  //           		foreach($audittrail->getChanges() as $change){
  //           			$em->remove($change);
  //           		}
  //           		$em->remove($audittrail);
  //           	}

  //           	//Miramos si está envuelto en una reconciliación
	 //            if($record->getFirstReconciliation()){
		//             $recon=$record->getFirstReconciliation()->getId();
		//         }
		//         else{
		//             $recon=$record->getId();
		//         }

		//         //Traemos el listado de reconciliaciones
		// 	        $reconciliations=$this->getReconciliations($recon);

		// 	        //Si el registro a borrar es el primero capturamos el siguiente elemento para enlazar los siguientes y crear un nuevo registro padre
		// 	        if(!$record->getFirstReconciliation()){
	 //        			$updateFirstReconciliation=$reconciliations[1];
	 //        		}
	 //        		else{
	 //        			$updateFirstReconciliation=FALSE;
	 //        		}
	 //            	if($reconciliations){
	            		
	            		
	 //            		foreach($reconciliations as $key => $reconciliation){
	 //            			//Si es el registro a borrar adjudicamos el siguiente registro su misma reconciliación para solventar el salto
	 //            			if($reconciliation==$record){
	 //            				$reconciliations[($key+1)]->setReconciliation($reconciliation->getReconciliation());
	 //            				$em->persist($reconciliations[($key+1)]);
	 //            			}
	 //            			else{
	 //            				//Si el que se elimina es el primer registro de reconciliacion modificamos el primer registro reconciliado del resto
		//             			if($updateFirstReconciliation){
		//             				if($updateFirstReconciliation==$reconciliation){
		//             					$reconciliation->setFirstReconciliation(NULL);
		//             				}
		//             				else{
		//             					$reconciliation->setFirstReconciliation($updateFirstReconciliation);
		//             				}
		//             			}
		//             		}
	 //            		}
	 //            	}

	 //            //¿Que pasa con las plantillas anidadas?

  //           	$em->remove($record);
  //           	$em->flush();
  //           }
		// }
		// try{
		// //Eliminamos plantillas que no tienen cumplimentaciones
		// $templates = $this->getRemovedTemplates();
		// $em = $this->getContainer()->get('doctrine')->getManager();
		// if ($templates) {
  //           foreach($templates as $template){
            	
  //           	echo $template->getId();

  //           	//Vaciamos solicitud de revisión sobre esa plantilla si la hubiera aunque se va a eliminar ya que se trata de una firma pero para evitar problemas
  //           	$template->setRequestReview(NULL);

  //           	//Eliminamos la relación de categorías de retención con esta plantilla
  //           	/*foreach($template->getRetentions() as $retention){
  //           		$em->remove($retention);
  //           	}*/

  //           	//Eliminamos las firmas de audittrail de gestión de plantillas
  //           	foreach($template->getTmSignatures() as $signature){
  //           		//Eliminamos los tests realizados sobre esta plantilla
  //           		foreach($signature->getTmTests() as $test){
  //           			$em->remove($test);
  //           		}
  //           		$em->remove($signature);

  //           	}



  //           	//Eliminamos el workflow de gestión de plantillas
  //           	foreach($template->getTmWorkflows() as $wf){
  //           		foreach($wf->getTmSignatures() as $aux_signature){
  //           			$em->remove($aux_signature);
  //           		}
  //           		$em->remove($wf);
  //           	}

  //           	//Eliminamos el workflow no nominal para cumplimentación
  //           	foreach($template->getTmSecondWorkflows() as $swf){
  //           		$em->remove($swf);
  //           	}

  //           	//Desactivamos de un area esta plantilla como plantilla maestra
  //           	foreach($template->getAreas() as $area){
  //           		$area->setTemplate(NULL);
  //           		$em->persist($area);
  //           	}

  //           	//Eliminar modelo de notificaciones para esa plantilla
  //           	foreach($template->getNotificationModels() as $model){
  //           		$em->remove($model);
  //           	}

  //           	//Eliminar la anidación de plantillas que tiene esta plantilla. Si ella es la padre, se eliminan todas sus anidaciones, no las plnatillas claro
  //           	foreach($template->getTmNestMasterTemplates() as $children){
  //           		$em->remove($children);
  //           	}

  //           	//Eliminar la anidación de plantillas en las que se encuentra involucrada esta plantilla, pero solo aquellos registros que la involucren a ella y no el resto. La plantilla que tuviera 5 anidaciones y una de ellas fuera la plantilla a borrar, se quedaría con 4 plantillas anidadas
  //           	foreach($template->getTmNestTemplates() as $parent){
  //           		$em->remove($parent);
  //           	}
            	
	 //            $em->remove($template);
	 //            $em->flush();

  //           }
		// }

		

		// }
  //   	catch(\Exception $e){
  //   		echo $e->getMessage();
  //   	}
	}

	protected function getRemovedRecords(){

		$em = $this->getContainer()->get('doctrine')->getManager();

	    $qb = $em->createQueryBuilder();
	    $retentions = $qb->select('cvr')
			->from('NononsenseHomeBundle:CVRecords', 'cvr')
			->where('cvr.retentionRemovedAt IS NOT NULL')
			->andWhere('cvr.retentionRemovedAt <= :remove')
		    ->setParameter('remove', new \DateTime('-3 year'))
			->getQuery()
			->getResult();

		return $retentions;
	}

	protected function getRemovedTemplates(){

		$em = $this->getContainer()->get('doctrine')->getManager();

		$subQueryBuilder = $em->createQueryBuilder();
		$subQuery = $subQueryBuilder
		    ->select(['DISTINCT(cvr.template)'])
		    ->from('NononsenseHomeBundle:CVRecords', 'cvr')
		    ->leftJoin("cvr.template", "t")
		    ->where('t.retentionRemovedAt IS NOT NULL')
		    ->andWhere('t.retentionRemovedAt <= :remove')
		    ->setParameter('remove', new \DateTime('-3 year'))
		    ->getQuery()
		    ->getArrayResult()
		;

	    $qb = $em->createQueryBuilder();
	    $retentions = $qb->select('t')
			->from('NononsenseHomeBundle:TMTemplates', 't')
			->where('t.retentionRemovedAt IS NOT NULL')
			->andWhere($qb->expr()->notIn('t.id', ':subQuery'))
			->setParameter('subQuery', $subQuery)
			->andWhere('t.retentionRemovedAt <= :remove')
		    ->setParameter('remove', new \DateTime('-3 year'))
			->getQuery()
			->getResult();

		return $retentions;
	}

	protected function getReconciliations($recon){

		$em = $this->getContainer()->get('doctrine')->getManager();

	    $qb = $em->createQueryBuilder();
	    $reconciliations = $qb->select('cvr')
			->from('NononsenseHomeBundle:CVRecords', 'cvr')
			->where('cvr.id=:recon OR cvr.firstReconciliation=:recon')
            ->setParameter('recon', $recon)
            ->orderBy('cvr.id', 'ASC')
			->getQuery()
			->getResult();

		return $reconciliations;
	}
}