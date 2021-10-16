<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    /**
     * @Route("/", name="customer")
     */
    public function index(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $allCustomers = $entityManager->getRepository(Customer::class)->findAll();

        $customers = $this->getTableData($allCustomers);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers
        ]);
    }

    /**
     * @Route("/search/{countryCode}/{status}", name="customer_search")
     */
    public function search($countryCode, $status): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        if($countryCode != 'all'){
            $allCustomers = $entityManager->getRepository(Customer::class)->findByCountryCode($countryCode);
        } else {
            $allCustomers = $entityManager->getRepository(Customer::class)->findAll();
        }

        $allCustomers = $this->getTableData($allCustomers);

        if($status == 'valid'){

            foreach ($allCustomers as $key => $customer){
                if($customer['status'] != 'OK'){
                    unset($allCustomers[$key]);
                }
            }

        }

        if($status == 'invalid'){
            foreach ($allCustomers as $key => $customer){
                if($customer['status'] != 'NOK'){
                    unset($allCustomers[$key]);
                }
            }
        }

        return $this->render('customer/table.html.twig', [
            'customers' => $allCustomers
        ]);

    }

    function isPhoneNumberValid($number): string
    {
        $countriesList = $this->getCountriesList();
        foreach ($countriesList as $country){
            if(preg_match($country['regex'], $number)) {
                return 'OK';
            }
        }
        return 'NOK';
    }

    function getCountryFromPhoneNumber($number): string
    {
        $countriesList = $this->getCountriesList();
        foreach ($countriesList as $country){
            if (strpos($number, '('.$country['code'].')') !== false) {
                return $country['countryName'];
            }
        }
        return 'unknown';
    }

    function getCountryCode($number): string
    {
        $countriesList = $this->getCountriesList();
        foreach ($countriesList as $country){
            if (strpos($number, '('.$country['code'].')') !== false) {
                return '+'.$country['code'];
            }

        }
        return 'unknown';
    }

    function getCountriesList(): array
    {
        //Countries retrieved from database or wherever
        return $countriesList = [
            ['code' => '237', 'countryName' => 'Cameroon', 'regex' => "#^\(237\)\ ?[2368]\d{7,8}$#"],
            ['code' => '251', 'countryName' => 'Ethiopia', 'regex' => "#^\(251\)\ ?[1-59]\d{8}$#"],
            ['code' => '212', 'countryName' => 'Morocco', 'regex' => "#^\(212\)\ ?[5-9]\d{8}$#"],
            ['code' => '258', 'countryName' => 'Mozambique', 'regex' => "#^\(258\)\ ?[28]\d{7,8}$#"],
            ['code' => '256', 'countryName' => 'Uganda', 'regex' => "#^\(256\)\ ?\d{9}$#"]
        ];
    }

    function getTableData($receivedCustomers): array
    {
        $customers = [];

        foreach ($receivedCustomers as $customer){

            $phoneNumber = $customer->getPhone();

            array_push($customers,[
                'phone' => preg_replace("/\([^)]+\)/","",$phoneNumber),
                'status' => $this->isPhoneNumberValid($phoneNumber),
                'country' => $this->getCountryFromPhoneNumber($phoneNumber),
                'countryCode' => $this->getCountryCode($phoneNumber)
            ]);

        }

        return $customers;
    }
}
