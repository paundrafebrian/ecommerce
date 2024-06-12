<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->rajaOngkirApiKey = config('rajaongkir.api_key');
		$this->rajaOngkirBaseUrl = config('rajaongkir.base_url');
		$this->rajaOngkirOrigin = config('rajaongkir.origin');
	}
    /**
	 * Raja Ongkir Request (Shipping Cost Calculation)
	 *
	 * @param string $resource resource url
	 * @param array  $params   parameters
	 * @param string $method   request method
	 *
	 * @return json
	 */
	protected function rajaOngkirRequest($resource, $params = [], $method = 'GET')
	{
		$client = new \GuzzleHttp\Client();

		$headers = ['key' => $this->rajaOngkirApiKey];
		$requestParams = [
			'headers' => $headers,
		];

		$url =  $this->rajaOngkirBaseUrl . '/' . $resource;
		if ($params && $method == 'POST') {
			$requestParams['form_params'] = $params;
		} else if ($params && $method == 'GET') {
			$query = is_array($params) ? '?'.http_build_query($params) : '';
			$url = $this->rajaOngkirBaseUrl . $resource . $query;
		}
		
		$response = $client->request($method, $url, $requestParams);

		return json_decode($response->getBody(), true);
    }
    
    /**
	 * Get provinces
	 *
	 * @return array
	 */
	protected function getProvinces()
	{
		$provinceFile = 'provinces.txt';
		$provinceFilePath = $this->uploadsFolder. 'files/' . $provinceFile;

		$isExistProvinceJson = \Storage::disk('local')->exists($provinceFilePath);

		if (!$isExistProvinceJson) {
			$response = $this->rajaOngkirRequest('province');
			\Storage::disk('local')->put($provinceFilePath, serialize($response['rajaongkir']['results']));
		}

		$province = unserialize(\Storage::get($provinceFilePath));

		$provinces = [];
		if (!empty($province)) {
			foreach ($province as $province) {
				$provinces[$province['province_id']] = strtoupper($province['province']);
			}
		}

		return $provinces;
    }
    
    /**
	 * Get cities by province ID
	 *
	 * @param int $provinceId province id
	 *
	 * @return array
	 */
	// protected function getCities($provinceId)
	// {
	// 	$cityFile = 'cities_at_'. $provinceId .'.txt';
	// 	$cityFilePath = $this->uploadsFolder. 'files/' .$cityFile;

	// 	$isExistCitiesJson = \Storage::disk('local')->exists($cityFilePath);

	// 	if (!$isExistCitiesJson) {
	// 		$response = $this->rajaOngkirRequest('city', ['province' => $provinceId]);
	// 		\Storage::disk('local')->put($cityFilePath, serialize($response['rajaongkir']['results']));
	// 	}

	// 	$cityList = unserialize(\Storage::get($cityFilePath));
		
	// 	$cities = [];
	// 	if (!empty($cityList)) {
	// 		foreach ($cityList as $city) {
	// 			$cities[$city['city_id']] = strtoupper($city['type'].' '.$city['city_name']);
	// 		}
	// 	}

	// 	return $cities;
	// }
	protected function getCities($provinceId)
{
    // Daftar kota untuk setiap provinsi
    $citiesByProvince = [
        1 => [
            ['city_id' => 1, 'city_name' => 'Kabupaten Badung'],
            ['city_id' => 2, 'city_name' => 'Kabupaten Bangli'],
            ['city_id' => 3, 'city_name' => 'Kabupaten Buleleng'],
            ['city_id' => 4, 'city_name' => 'Kabupaten Gianyar'],
            ['city_id' => 5, 'city_name' => 'Kabupaten Jembrana'],
            ['city_id' => 6, 'city_name' => 'Kabupaten Karangasem'],
            ['city_id' => 7, 'city_name' => 'Kabupaten Klungkung'],
            ['city_id' => 8, 'city_name' => 'Kabupaten Tabanan'],
            ['city_id' => 9, 'city_name' => 'Kota Denpasar'],
        ],
        2 => [
            ['city_id' => 10, 'city_name' => 'Kabupaten Bangka'],
            ['city_id' => 11, 'city_name' => 'Kabupaten Bangka Barat'],
            ['city_id' => 12, 'city_name' => 'Kabupaten Bangka Selatan'],
            ['city_id' => 13, 'city_name' => 'Kabupaten Bangka Tengah'],
            ['city_id' => 14, 'city_name' => 'Kabupaten Belitung'],
            ['city_id' => 15, 'city_name' => 'Kabupaten Belitung Timur'],
            ['city_id' => 16, 'city_name' => 'Kota Pangkal Pinang'],
        ],
        3 => [
            ['city_id' => 17, 'city_name' => 'Kabupaten Lebak'],
            ['city_id' => 18, 'city_name' => 'Kabupaten Pandeglang'],
            ['city_id' => 19, 'city_name' => 'Kabupaten Serang'],
            ['city_id' => 20, 'city_name' => 'Kabupaten Tangerang'],
            ['city_id' => 21, 'city_name' => 'Kota Cilegon'],
            ['city_id' => 22, 'city_name' => 'Kota Serang'],
            ['city_id' => 23, 'city_name' => 'Kota Tangerang'],
            ['city_id' => 24, 'city_name' => 'Kota Tangerang Selatan'],
        ],
        4 => [
            // Tambahkan kota untuk Bengkulu
        ],
        5 => [
            // Tambahkan kota untuk DI Yogyakarta
        ],
        6 => [
            // Tambahkan kota untuk DKI Jakarta
        ],
        7 => [
            // Tambahkan kota untuk Gorontalo
        ],
        8 => [
            // Tambahkan kota untuk Jambi
        ],
        9 => [
            // Tambahkan kota untuk Jawa Barat
        ],
        10 => [
            ['city_id' => 25, 'city_name' => 'Kabupaten Banjarnegara'],
            ['city_id' => 26, 'city_name' => 'Kabupaten Banyumas'],
            ['city_id' => 27, 'city_name' => 'Kabupaten Batang'],
            ['city_id' => 28, 'city_name' => 'Kabupaten Blora'],
            ['city_id' => 29, 'city_name' => 'Kabupaten Boyolali'],
            ['city_id' => 30, 'city_name' => 'Kabupaten Brebes'],
            ['city_id' => 31, 'city_name' => 'Kabupaten Cilacap'],
            ['city_id' => 32, 'city_name' => 'Kabupaten Demak'],
            ['city_id' => 33, 'city_name' => 'Kabupaten Grobogan'],
            ['city_id' => 34, 'city_name' => 'Kabupaten Jepara'],
            ['city_id' => 35, 'city_name' => 'Kabupaten Karanganyar'],
            ['city_id' => 36, 'city_name' => 'Kabupaten Kebumen'],
            ['city_id' => 37, 'city_name' => 'Kabupaten Kendal'],
            ['city_id' => 38, 'city_name' => 'Kabupaten Klaten'],
            ['city_id' => 39, 'city_name' => 'Kabupaten Kudus'],
            ['city_id' => 40, 'city_name' => 'Kabupaten Magelang'],
            ['city_id' => 41, 'city_name' => 'Kabupaten Pati'],
            ['city_id' => 42, 'city_name' => 'Kabupaten Pekalongan'],
            ['city_id' => 43, 'city_name' => 'Kabupaten Pemalang'],
            ['city_id' => 44, 'city_name' => 'Kabupaten Purbalingga'],
            ['city_id' => 45, 'city_name' => 'Kabupaten Purworejo'],
            ['city_id' => 46, 'city_name' => 'Kabupaten Rembang'],
            ['city_id' => 47, 'city_name' => 'Kabupaten Semarang'],
            ['city_id' => 48, 'city_name' => 'Kabupaten Sragen'],
            ['city_id' => 49, 'city_name' => 'Kabupaten Sukoharjo'],
            ['city_id' => 50, 'city_name' => 'Kabupaten Tegal'],
            ['city_id' => 51, 'city_name' => 'Kabupaten Temanggung'],
            ['city_id' => 52, 'city_name' => 'Kabupaten Wonogiri'],
            ['city_id' => 53, 'city_name' => 'Kabupaten Wonosobo'],
            ['city_id' => 54, 'city_name' => 'Kota Magelang'],
            ['city_id' => 55, 'city_name' => 'Kota Pekalongan'],
            ['city_id' => 56, 'city_name' => 'Kota Salatiga'],
            ['city_id' => 57, 'city_name' => 'Kota Semarang'],
            ['city_id' => 58, 'city_name' => 'Kota Surakarta'],
            ['city_id' => 59, 'city_name' => 'Kota Tegal'],
        ],
        11 => [
            // Tambahkan kota untuk Jawa Timur
        ],
        12 => [
            // Tambahkan kota untuk Kalimantan Barat
        ],
        13 => [
            // Tambahkan kota untuk Kalimantan Selatan
        ],
        14 => [
            // Tambahkan kota untuk Kalimantan Tengah
        ],
        15 => [
            // Tambahkan kota untuk Kalimantan Timur
        ],
        16 => [
            // Tambahkan kota untuk Kalimantan Utara
        ],
        17 => [
            // Tambahkan kota untuk Kepulauan Riau
        ],
        18 => [
            // Tambahkan kota untuk Lampung
        ],
        19 => [
            // Tambahkan kota untuk Maluku
        ],
        20 => [
            // Tambahkan kota untuk Maluku Utara
        ],
        21 => [
            // Tambahkan kota untuk Nanggroe Aceh Darussalam (NAD)
        ],
        22 => [
            // Tambahkan kota untuk Nusa Tenggara Barat (NTB)
        ],
        23 => [
            // Tambahkan kota untuk Nusa Tenggara Timur (NTT)
        ],
        24 => [
            // Tambahkan kota untuk Papua
        ],
        25 => [
            // Tambahkan kota untuk Papua Barat
        ],
        26 => [
            // Tambahkan kota untuk Riau
        ],
        27 => [
            // Tambahkan kota untuk Sulawesi Barat
        ],
        28 => [
            // Tambahkan kota untuk Sulawesi Selatan
        ],
        29 => [
            // Tambahkan kota untuk Sulawesi Tengah
        ],
        30 => [
            // Tambahkan kota untuk Sulawesi Tenggara
        ],
        31 => [
            // Tambahkan kota untuk Sulawesi Utara
        ],
        32 => [
            // Tambahkan kota untuk Sumatera Barat
        ],
        33 => [
            // Tambahkan kota untuk Sumatera Selatan
        ],
        34 => [
            // Tambahkan kota untuk Sumatera Utara
        ],
    ];

    // Inisialisasi daftar kota kosong
    $cities = [];

    // Ambil daftar kota dari array berdasarkan province_id
    if (isset($citiesByProvince[$provinceId])) {
        $cities = $citiesByProvince[$provinceId];
    }

    // Ubah format data kota sesuai kebutuhan
    $formattedCities = [];
    foreach ($cities as $city) {
        $formattedCities[$city['city_id']] = strtoupper($city['city_name']);
    }

    return $formattedCities;
}


	protected function initPaymentGateway()
	{
		// Set your Merchant Server Key
		Config::$serverKey = config('midtrans.serverKey');
		// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
		Config::$isProduction = config('midtrans.isProduction');
		// Set sanitization on (default)
		Config::$isSanitized = config('midtrans.isSanitized');
		// Set 3DS transaction for credit card to true
		Config::$is3ds = config('midtrans.is3ds');
	}
}
