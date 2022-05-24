<?php

namespace Nononsense\HomeBundle\Controller;

use Exception;
use Nononsense\HomeBundle\Entity\CertificationsRepository;
use Nononsense\HomeBundle\Entity\CertificationsType;
use Nononsense\HomeBundle\Utils\FiltersUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Nononsense\HomeBundle\Entity\Certifications;
use Nononsense\UtilsBundle\Classes\Utils;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificationController extends Controller
{
    const LIMIT_MANY = 15;
    const TITLE_PDF_EXCEL = "Listado de certificaciones";
    const FILENAME_PDF_EXCEL = "list_certifications";

	public function listAction(Request $request){

        if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $user = $this->container->get('security.context')->getToken()->getUser();

        $filters = [];
        $paginate = 0;

        if(!$request->get("export_excel") && !$request->get("export_pdf")) {
            FiltersUtils::paginationFilters($filters, (int) $request->get("page"), self::LIMIT_MANY);
            $paginate = 1;
        } else {
            $filters["limit_from"]=0;
            $filters["limit_many"]=99999999999;
        }

        $fields = ["hash", "type", "recordId", "from", "until"];
        FiltersUtils::requestToFilters($request, $filters, $fields);

        /** @var CertificationsRepository $certificationRepository */
        $certificationRepository = $this->getDoctrine()->getRepository(Certifications::class);
        $array_item["items"] = $certificationRepository->list($filters, $paginate);

        if ($request->get("export_excel")) {
            return $this->exportToExcel(
                $array_item["items"]
                , $user->getUsername()
            );
        }
        if ($request->get("export_pdf")) {
            $this->exportToPDF(
                $request
                , $array_item["items"]
            );
        }

        $filters["types"] = $this->getDoctrine()->getRepository(CertificationsType::class)->findAll();
        $array_item["filterSection"] = $filters;
        $array_item["count"] = $certificationRepository->count($filters);
        $array_item["pagination"] = Utils::getPaginator($request, $filters["limit_many"], $array_item["count"]);

        return $this->render('NononsenseHomeBundle:Certifications:certifications.html.twig',$array_item);

	}

	public function downloadAction(Request $request, int $id){
		
		if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

		$certifications = $this->getDoctrine()->getRepository(Certifications::class)->findOneBy(['id' => $id]);

		$response = new BinaryFileResponse($certifications->getPath());

		$response->setContentDisposition(
		    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
		    basename($certifications->getPath())
		);

		return $response;
	}

	private function isAllowed($section){

		if (!$this->get('app.security')->permissionSeccion($section)){

			$this->get('session')->getFlashBag()->add('error', 'No tiene permisos suficientes para acceder a esta sección.');

			return false;
		}

		return true;
	}

    private function exportToExcel(array $certifications, string $username)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties();
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', "Listado de certificaciones - ".$username." - ".$this->get('utilities')->sp_date(date("d/m/Y H:i:s")));

        $phpExcelObject->setActiveSheetIndex()
            ->setCellValue('A2', 'Fecha de modificación')
            ->setCellValue('B2', 'Tipo')
            ->setCellValue('C2', 'Hash')
            ->setCellValue('D2', 'Tx Hash')
            ->setCellValue('E2', 'Registro')
        ;

        $phpExcelObject->getActiveSheet()->setTitle(self::TITLE_PDF_EXCEL);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        for($col = 'A'; $col <= 'D'; $col++) {
            $phpExcelObject->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $row=3;
        forEach($certifications as $certification){
            $modifiedDate  = $certification->getModified();
            if (is_null($modifiedDate)) {
                $modifiedDateText = "-";
            } else {
                $modifiedDateText = $this->get('utilities')->sp_date($modifiedDate->format('d/m/Y H:i:s'));
            }
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$row, $modifiedDateText)
                ->setCellValue('B'.$row, $certification->getType()->getName())
                ->setCellValue('C'.$row, $certification->getHash())
                ->setCellValue('D'.$row, $certification->getTxHash())
                ->setCellValue('E'.$row, $certification->getRecordId())
            ;
            $row++;
        }

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, self::FILENAME_PDF_EXCEL . '.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;

    }

    private function exportToPDF(Request $request, array $certifications)
    {
        $html='<html><body style="font-size:8px;width:100%">';
        $sintax_head_f="<b>Filtros:</b><br>";

        if($request->get("hash")){
            $html.=$sintax_head_f."Hash => ".$request->get("hash")."<br>";
            $sintax_head_f="";
        }

        if($request->get("type")){
            $htype = $this->getDoctrine()->getRepository(CertificationsType::class)->findOneBy(array("id"=>$request->get("type")));
            $html.=$sintax_head_f."Tipo => ".$htype->getName()."<br>";
            $sintax_head_f="";
        }

        if($request->get("recordId")){
            $html.=$sintax_head_f."Id => ".$request->get("recordId")."<br>";
            $sintax_head_f="";
        }

        if($request->get("from")){
            $html.=$sintax_head_f."Fecha desde => ".$request->get("from")."<br>";
            $sintax_head_f="";
        }

        if($request->get("until")){
            $html.=$sintax_head_f."Fecha hasta => ".$request->get("until")."<br>";
        }

        $html.='<br>
            <table autosize="1" style="overflow:wrap;width:95%">
            <tr style="font-size:8px;width:100%">
                <th style="font-size:8px;width:10%">Fecha</th>
                <th style="font-size:8px;width:10%">Tipo</th>
                <th style="font-size:8px;width:40%">Hash</th>
                <th style="font-size:8px;width:40%">TX Hash</th>
                <th style="font-size:8px;width:5%">ID</th> 
            </tr>';

        forEach($certifications as $certification){
            $modifiedDate  = $certification->getModified();
            if (is_null($modifiedDate)) {
                $modifiedDateText = "-";
            } else {
                $modifiedDateText = $this->get('utilities')->sp_date($modifiedDate->format('d/m/Y H:i:s'));
            }
            $html.='<tr style="font-size:8px">
                        <td>'.$modifiedDateText.'</td>
                        <td>'.$certification->getType()->getName().'</td>
                        <td>'.$certification->getHash().'</td>
                        <td>'.$certification->getTxHash().'</td>
                        <td>'.$certification->getRecordId().'</td>
                    </tr>';
        }

        $html.='</table></body></html>';

        $this->get('utilities')->returnPDFResponseFromHTML($html, self::TITLE_PDF_EXCEL, self::FILENAME_PDF_EXCEL);
    }

    /**
     * @throws Exception
     */
    public function downloadCertificatesAction(string $hash)
    {

        if (!$this->isAllowed('crt_gestion')) return $this->redirect($this->generateUrl('nononsense_home_homepage'));

        $url = $this->getParameter('api3.url') . '/hash/' . $hash . "/certificate";
        $header = [
            'apiKey:' . $this->getParameter('api3.key'),
            "accept:" => "application/pdf"
        ];

        if ($hash) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $raw_response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode === 200) {
                $json = json_decode($raw_response, TRUE);
                $data = $json["data"];
                $fileName = $data["file_name"];

                $pdf = base64_decode($data["file_base64"]);

                $response = new Response($pdf);
                $response->headers->set('Content-Type', 'application/octet-stream');
                $response->headers->set('Content-Description','File Transfer');
                $response->headers->set('Content-Disposition' ,'attachment; filename="' . basename($fileName) . '"');
                $response->headers->set('Content-Length', strlen($pdf));
                $response->headers->set('Cache-Control', 'no-cache private');

                $response->sendHeaders();

                return $response;
            }
        }

        $this->get('session')->getFlashBag()->add(
            'error',
            "Hubo un error cuando se accedía al certificado"
        );
        return $this->redirectToRoute("nononsense_certifications_list");

    }
}