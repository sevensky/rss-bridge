<?php
class FindACrewBridge extends BridgeAbstract {
	const MAINTAINER = 'couraudt';
	const NAME = 'Find A Crew Bridge';
	const URI = 'https://www.findacrew.net';
	const DESCRIPTION = 'Returns the newest sailing offers.';
	const PARAMETERS = array(
		array(
			'type' => array(
				'name' => 'Type of search',
				'title' => 'Choose between finding a boat or a crew',
				'type' => 'list',
				'values' => array(
					'Find a boat' => 'boat',
					'Find a crew' => 'crew'
				)
			),
			'long' => array(
				'name' => 'Longitude of the searched location',
				'title' => 'Center the search at that longitude (e.g: -42.02)'
			),
			'lat' => array(
				'name' => 'Latitude of the searched location',
				'title' => 'Center the search at that latitude (e.g: 12.42)'
			),
			'distance' => array(
				'name' => 'Limit boundary of search in KM',
				'title' => 'Boundary of the search in kilometers when using longitude and latitude'
			)
		)
	);

	public function collectData() {
		$url = $this->getURI();

		if ($this->getInput('type') == 'boat') {
			$data = array('SrhLstBtAction' => 'Create');
		} else {
			$data = array('SrhLstCwAction' => 'Create');
		}

		if ($this->getInput('long') && $this->getInput('lat')) {
			$data['real_LocSrh_Lng'] = $this->getInput('long');
			$data['real_LocSrh_Lat'] = $this->getInput('lat');
			if ($this->getInput('distance')) {
				$data['LocDis'] = (int)$this->getInput('distance') * 1000;
			}
		}

		$header = array(
			'Content-Type: application/x-www-form-urlencoded'
		);

		$opts = array(
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => http_build_query($data) . "\n"
		);

		$html = getSimpleHTMLDOM($url, $header, $opts) or returnClientError('No results for this query.');

		$annonces = $html->find('.css_SrhRst');
		foreach ($annonces as $annonce) {
			$item = array();

			$img = parent::getURI() . $annonce->find('.lst-pic img', 0)->getAttribute('src');
			$item['title'] = $annonce->find('.lst-tags span', 0)->plaintext;
			$item['uri'] = parent::getURI() . $annonce->find('.lst-ctrls a', 0)->href;
			$content = $annonce->find('.lst-dtl', 0)->innertext;
			$item['content'] = "<img src='$img' /><br>$content";
			$item['enclosures'] = array($img);
			$item['categories'] = array($annonce->find('.css_AccLocCur', 0)->plaintext);
			$this->items[] = $item;
		}
	}

	public function getURI() {
		$uri = parent::getURI();
		// Those params must be in the URL
		$uri .= '/en/' . $this->getInput('type') . '/search?srhtyp=srhrst&mdl=2';
		return $uri;
	}
}
