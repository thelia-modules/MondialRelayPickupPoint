<?php

namespace MondialRelayPickupPoint\WebApi;

use MondialRelayPickupPoint\MondialRelayPickupPoint;
use nusoap_client;
use \nusoap_client as NusoapClient;

class MondialRelayWebApi
{
    /**
     * URL du web service Mondial Relay
     * @var string
     * @access private
     */
    public $_APIEndPointUrlV1 = "http://api.mondialrelay.com/";

    /**
     * URL du web service Mondial Relay
     * @var string
     * @access private
     */
    public $_APIEndPointUrlV2 = "https://connect-api.mondialrelay.com/";

    /**
     * Mondial relay Customer ID (Brand ID)
     * @var string
     * @access private
     */
    public $_APINumericBrandId;

    /**
     * API File Endpoint
     * @var string
     * @access private
     */
    private $_APIFileEndPointV1 = "Web_Services.asmx?WSDL";

    /**
     * Nusoap Soap client isntance
     * @var nusoap_client
     * @access private
     */
    private $_SoapClient;

    /**
     * API version (V1.0 par défaut sinon V2.0)
     * @var string
     * @access public
     */
    public $_Api_Version = "1.0";

    /**
     * API login for API V1
     * @var string
     * @access public
     */
    public $_Api_CustomerCode = "";

    /**
     * API password for API V1
     * @var string
     * @access public
     */
    public $_Api_SecretKey = "";

    /**
     * Debug mode enabled or not
     * @var boolean
     * @access private
     */
    public $_Debug = false;

    /**
     * constructor
     *
     * @param string $ApiEndPointUrl Mondial Relay API EndPoint
     * @param string $ApiLogin Mondial Relay API Login (provided by your technical contact)
     * @param string $ApiPassword Mondial Relay API Password (provided by your technical contact)
     * @param string $ApiBrandId Mondial Relay API Numeric Brand ID (2 digits) (provided by your technical contact)
     * @access   public
     */
    public function __construct()
    {

    }

    /**
     * Search parcel Shop Arround a postCode according to filters
     *
     * @param string $CountryCode Country Code (ISO) of the post code
     * @param string $PostCode Post Code arround which you want to find parcel shops
     * @param string $DeliveryMode Optionnal - Delivery Mode Code Filter (3 Letter code, 24R, DRI). Will restrict the results to parcelshops available with this delivery Mode
     * @param int $ParcelWeight Optionnal - Will restrict results to parcelshops compatible with the parcel Weight in gramms specified
     * @param int $ParcelShopActivityCode Optionnal - Will restrict results to parcelshops regarding to their actity code
     * @param int $SearchDistance Optionnal - Will restrict results to parcelshops in the perimeter specified in km
     * @param int $SearchOpenningDelay Optionnal - If you intend to give us the parcel in more than one day, you can specified a delay in order to filter ParcelShops according to their oppening periods
     * @access   public
     * @return   array
     */
    public function SearchParcelShop($CountryCode, $PostCode, $DeliveryMode = "", $ParcelWeight = "", $ParcelShopActivityCode = "", $SearchDistance = "", $SearchOpenningDelay = "", $numPointRelais = "")
    {
        $endPoint =  MondialRelayPickupPoint::getConfigValue(MondialRelayPickupPoint::WEBSERVICE_URL) ?? $this->_APIEndPointUrlV1 . $this->_APIFileEndPointV1;

        $this->_SoapClient = new NusoapClient($endPoint, true);
        $this->_SoapClient->soap_defencoding = 'utf-8';

        $params = [
            'Enseigne' => str_pad($this->_Api_CustomerCode, 8),
            'Pays' => $CountryCode,
            'Ville' => "",
            'CP' => $PostCode,
            'Taille' => "",
            'Poids' => $ParcelWeight,
            'Action' => $DeliveryMode,
            'RayonRecherche' => $SearchDistance,
            'TypeActivite' => $ParcelShopActivityCode,
            'DelaiEnvoi' => $SearchOpenningDelay,
            'NumPointRelais' => $numPointRelais
        ];

        return $this->CallWebApi("WSI3_PointRelais_Recherche", $this->AddSecurityCode($params));
    }

    /**
     * add the security signature to the soap request
     *
     * @param boolean $ReturnArray Optionnal, False if you just want to output the security string
     * @access   private
     * @return   array
     */
    private function AddSecurityCode($ParameterArray)
    {
        $secString = "";
        foreach ($ParameterArray as $prm) {
            $secString .= $prm;
        }
        $ParameterArray['Security'] = strtoupper(md5(utf8_decode($secString . $this->_Api_SecretKey)));

        return $ParameterArray;
    }

    /**
     * perform a call to the mondial relay API
     *
     * @param string $methodName Soap Method to call
     * @param array $ParameterArray
     * @access   private
     */
    private function CallWebApi($methodName, $ParameterArray)
    {
        $result = $this->_SoapClient->call($methodName, $ParameterArray, $this->_APIEndPointUrlV1, $this->_APIEndPointUrlV1 . $methodName);

        // Display the request and response
        if ($this->_Debug) {
            echo '<div style="border:solid 1px #ddd;font-family:verdana;padding:5px">';
            echo '<h1>Method ' . $methodName . '</h1>';
            echo '<div>' . ApiHelper::GetStatusCode($result) . '</div>';
            echo '<h2>Request</h2>';
            echo '<pre>';
            print_r($ParameterArray);
            echo '</pre>';
            echo '<pre>' . htmlspecialchars($this->_SoapClient->request, ENT_QUOTES) . '</pre>';
            echo '<h2>Response</h2>';
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            echo '<pre>' . htmlspecialchars($this->_SoapClient->response, ENT_QUOTES) . '</pre>';

            echo '</div>';

        }

        return $result;
    }
}