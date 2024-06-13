<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $data = [];
    protected $uploadsFolder = 'uploads/';

    protected $rajaOngkirApiKey = null;
    protected $rajaOngkirBaseUrl = null;
    protected $rajaOngkirOrigin = null;
    protected $couriers = [
        'jne' => 'JNE',
        'pos' => 'POS Indonesia',
        'tiki' => 'Titipan Kilat'
    ];

    protected $provinces = [];

    public function __construct()
    {
        $this->rajaOngkirApiKey = config('rajaongkir.api_key');
        $this->rajaOngkirBaseUrl = config('rajaongkir.base_url');
        $this->rajaOngkirOrigin = config('rajaongkir.origin');
    }

    protected function rajaOngkirRequest($resource, $params = [], $method = 'GET')
    {
        $client = new Client();
        $headers = ['key' => $this->rajaOngkirApiKey];
        $requestParams = [
            'headers' => $headers,
        ];

        $url = $this->rajaOngkirBaseUrl . '/' . $resource;
        if ($params && $method == 'POST') {
            $requestParams['form_params'] = $params;
        } else if ($params && $method == 'GET') {
            $query = is_array($params) ? '?' . http_build_query($params) : '';
            $url .= $query;
        }

        try {
            $response = $client->request($method, $url, $requestParams);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getProvinces()
    {
        $provinceFile = 'provinces.txt';
        $provinceFilePath = $this->uploadsFolder . 'files/' . $provinceFile;

        $isExistProvinceJson = Storage::disk('local')->exists($provinceFilePath);

        if (!$isExistProvinceJson) {
            $response = $this->rajaOngkirRequest('province');
            Storage::disk('local')->put($provinceFilePath, serialize($response['rajaongkir']['results']));
        }

        $province = unserialize(Storage::get($provinceFilePath));

        $provinces = [];
        if (!empty($province)) {
            foreach ($province as $prov) {
                $provinces[$prov['province_id']] = strtoupper($prov['province']);
            }
        }

        return $provinces;
    }

    protected function getCities($provinceId)
    {
        $cityFile = 'cities_at_' . $provinceId . '.txt';
        $cityFilePath = $this->uploadsFolder . 'files/' . $cityFile;

        $isExistCitiesJson = Storage::disk('local')->exists($cityFilePath);

        if (!$isExistCitiesJson) {
            $response = $this->rajaOngkirRequest('city', ['province' => $provinceId]);
            Storage::disk('local')->put($cityFilePath, serialize($response['rajaongkir']['results']));
        }

        $cityList = unserialize(Storage::get($cityFilePath));

        $cities = [];
        if (!empty($cityList)) {
            foreach ($cityList as $city) {
                $cities[$city['city_id']] = strtoupper($city['type'] . ' ' . $city['city_name']);
            }
        }

        return $cities;
    }

    // protected function initPaymentGateway()
    // {
    //     Config::$serverKey = config('midtrans.server_key');
    //     Config::$isProduction = config('midtrans.is_production');
    //     Config::$isSanitized = config('midtrans.is_sanitized');
    //     Config::$is3ds = config('midtrans.is_3ds');
    // }
    protected function initPaymentGateway()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_IS_3DS', true);
    }
}
