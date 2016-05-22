<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Rates;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RatesController extends Controller
{
	 /**
     * @Route("/save", name="saveData")
     */
    public function save()
    {
		$data = $this->getDataApi();
		
		$checkDate = $this->getDoctrine()->getRepository('AppBundle:Rates');
		if(null === $checkDate->findOneByDate(new \DateTime("now"))) {
			
			foreach($data as $val) {
			
				$rates = new Rates();
				$rates->setName($val[0]);
				$rates->setQty($val[1]);
				$rates->setDate(new \DateTime("now"));
			
				$em = $this->getDoctrine()->getManager();

				$em->persist($rates);
			
				$em->flush();
			}
			
		}
		
		return new Response('Saved new rates');
    }
	
	private function getDataApi() {
		$csv = array_map('str_getcsv', file("http://www.bank.lv/vk/ecb.csv?date=" . date("Ymd")));
		foreach($csv as &$val) {
			$val = explode("	", $val[0]);
		}
		return $csv;
	}
	
	/**
     * @Route("/rate/{val}/{qty}", name="rate",  requirements={
	 *     "qty": "^\d+|\d*\.\d+$"
	 * })
     */
	public function rate($val, $qty)
	{
		$rate = $this->getDoctrine()
			->getRepository('AppBundle:Rates')
			->findOneBy(
				array('name' => $val, 'date' => new \DateTime("now"))
			);

		if (!$rate) {
			return new Response('Please save the data into the database');
		}
		
		return new Response($rate->getQty() * $qty);
	}
}