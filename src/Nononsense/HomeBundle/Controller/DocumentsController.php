<?php
/**
 * Nodalblock
 * User: Sergio
 * Date: 02/08/2019
 * Time: 07:07
 */
namespace Nononsense\HomeBundle\Controller;


use Nononsense\HomeBundle\Entity\Documents;
use Nononsense\HomeBundle\Entity\RecordsDocuments;
use Nononsense\HomeBundle\Entity\DocumentsSignatures;
use Nononsense\HomeBundle\Entity\RecordsSignatures;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nononsense\HomeBundle\Entity\InstanciasSteps;

use Nononsense\UtilsBundle\Classes;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DocumentsController extends Controller
{
    public function listAction(Request $request)
    {
        $filters=Array();
        $filters2=Array();
        $types=array();

        $array_item["items"] = $this->getDoctrine()->getRepository(Documents::class)->list($filters);
        $array_item["count"] = $this->getDoctrine()->getRepository(Documents::class)->count($filters2,$types);

        return $this->render('NononsenseHomeBundle:Contratos:documents.html.twig',$array_item);
    }
}