<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$bal = [];
		$jsonArray = [[]];

		$this->config->load('api_keys');
		$url = $this->config->item('api_url');
		$username = $this->config->item('api_username');
		$password = $this->config->item('api_password');
		$credentials = base64_encode($username . ':' . $password);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic ' . $credentials,
		));

		$response = curl_exec($ch);

		// Check for errors
		if (curl_errno($ch)) {
			echo 'Curl error: ' . curl_error($ch);
		}

		curl_close($ch);

		// $response now contains the response from the server
		$jsonArray = json_decode($response, true);

		// Check if decoding was successful
		if ($jsonArray === null) {
			echo 'Error decoding JSON';
		} else {
			// Now $jsonArray is a PHP array
			// echo 'JSON is decoded';
		}
		$len = count($jsonArray);
		// echo $len;

		$giftCardCode = $this->input->post('giftCardCode');
		// var_dump($jsonArray);


		$numberOfGiftCards = count($jsonArray);
		// print_r("<br>".$numberOfGiftCards);
		$giftCardCode = $this->input->post('giftCardCode');
		// print_r($giftCardCode);
		$i = 0;
		while ($i < $len) {
			if ($jsonArray[$i]['balance']) {
				$bal['cards'][] = $jsonArray[$i]['number'];
				$bal['balances'][] = $jsonArray[$i]['balance'];
			}
			$i++;
		}
		// var_dump($bal['cards']);
		if ($giftCardCode) {
			for ($i = 0; $i < $numberOfGiftCards; $i++) {
				if ($jsonArray[$i]['number'] == $giftCardCode) {
					if ($jsonArray[$i]['balance'] > 0) {

						// echo $jsonArray[$i]['balance'];
						$bal['balance'] = $jsonArray[$i]['balance'];
						$bal['email'] = $jsonArray[$i]['recipient_email'];
						$bal['number'] = $jsonArray[$i]['number'];
						$bal['card'] = "Valid Card";
					} else {
						$bal['balance'] = "No Balance";
						$bal['card'] = "Invalid Card";
					}


					break;
				} else {
					$bal['balance'] = "No Balance";
					$bal['card'] = "Invalid Card";
				}
			}
		}


		$customerNumber = $this->input->post('customerNumber');





		if ($this->input->post('customerNumber')) {


			$bal['customerNumber'] = $customerNumber;
			$data = array(
				'email' => isset($bal['email']) ? $bal['email'] : null,
				'card' => isset($bal['number']) ? $bal['number'] : null,
				'bal' => isset($bal['balance']) ? $bal['balance'] : null,
				'custNum' => $bal['customerNumber']
			);
			// print_r($data);
			$this->load->database();
			$this->db->insert('card_data', $data);

			if ($this->db->affected_rows() > 0) {
				$bal['data'] = "Records Inserted in Database";
				// return true;
			} else {
				return false; // Insert failed
			}
		}



		$this->load->view('welcome_message', $bal);
	}
}
