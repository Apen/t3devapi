<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Xavier Perseguers <xavier@causal.ch>, Causal Sàrl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Google map in TCA
 * Example :
 *
 * require_once(t3lib_extMgm::extPath('t3devapi') . 'tca/class.tx_Tca_Map.php');
 *
 * 'map' => array(
 *  'exclude' => 0,
 *  'label' => 'LLL:EXT:tcagmap/locallang_db.xml:tx_tcagmap_coord.map',
 *  'config' => array(
 *      'type' => 'user',
 *      'userFunc' => 'tx_Tca_Map->render',
 *      'parameters' => array(
 *          'latitude' => 'gps_latitude',
 *          'longitude' => 'gps_longitude',
 *      ),
 *  ),
 * )
 *
 * @package climbing_sites
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

class tx_Tca_Map
{

	/**
	 * Renders the Google map.
	 *
	 * @param array $PA
	 * @param t3lib_TCEforms $pObj
	 * @return string
	 */

	public function render(array &$PA, t3lib_TCEforms $pObj) {

		$version = class_exists('t3lib_utility_VersionNumber')
				? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
				: t3lib_div::int_from_ver(TYPO3_version);
		if ($version < 4006000) {
			$PA['parameters'] = $PA['fieldConf']['config']['parameters'];
			/*$PA['parameters'] = array(
				'latitude' => 'gps_latitude',
				'longitude' => 'gps_longitude',
			);*/
		}

		$out = array();
		$latitude = (float)$PA['row'][$PA['parameters']['latitude']];
		$longitude = (float)$PA['row'][$PA['parameters']['longitude']];

		$baseElementId = isset($PA['itemFormElID']) ? $PA['itemFormElID'] : $PA['table'] . '_map';
		$addressId = $baseElementId . '_address';
		$mapId = $baseElementId . '_map';

		if (!($latitude && $longitude)) {
			$latitude = 46.816024;
			$longitude = 7.123325;
		}

		$dataPrefix = 'data[' . $PA['table'] . '][' . $PA['row']['uid'] . ']';
		$latitudeField = $dataPrefix . '[' . $PA['parameters']['latitude'] . ']';
		$longitudeField = $dataPrefix . '[' . $PA['parameters']['longitude'] . ']';

		$updateJs = "TBE_EDITOR.fieldChanged('%s','%s','%s','%s');";
		$updateLatitudeJs = sprintf($updateJs, $PA['table'], $PA['row']['uid'], $PA['parameters']['latitude'], $latitudeField);
		$updateLongitudeJs = sprintf($updateJs, $PA['table'], $PA['row']['uid'], $PA['parameters']['longitude'], $longitudeField);

		$out[] = '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&language=fr"></script>';
		$out[] = '<script type="text/javascript">';
		$out[] = <<<EOT
if (typeof TxClimbingSites == 'undefined') TxClimbingSites = {};

TxClimbingSites.init = function() {
	TxClimbingSites.origin = new google.maps.LatLng({$latitude}, {$longitude});
	var myOptions = {
		zoom: 13,
		center: TxClimbingSites.origin,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	TxClimbingSites.map = new google.maps.Map(document.getElementById("{$mapId}"), myOptions);
	TxClimbingSites.marker = new google.maps.Marker({
		map: TxClimbingSites.map,
		position: TxClimbingSites.origin,
		draggable: true
	});

	google.maps.event.addListener(TxClimbingSites.marker, 'dragend', function() {
		TxClimbingSites.updateCoord();
	});
	TxClimbingSites.geocoder = new google.maps.Geocoder();
	TxClimbingSites.reverseGeocode({$latitude}, {$longitude});

	// Make sure to refresh Google Map if corresponding tab is not yet active
	TxClimbingSites.tabPrefix = Ext.fly('{$mapId}').findParentNode('[id$="-DIV"]').id;
	TxClimbingSites.tabPrefix = Ext.util.Format.substr(TxClimbingSites.tabPrefix, 0, TxClimbingSites.tabPrefix.length - 4);
	if (Ext.fly(TxClimbingSites.tabPrefix + '-DIV').getStyle('display') == 'none') {
		Ext.fly(TxClimbingSites.tabPrefix + '-MENU').on('click', TxClimbingSites.refreshMap);
	}
};

TxClimbingSites.updateCoord = function() {
	var lat = TxClimbingSites.marker.getPosition().lat().toFixed(6);
	var lng = TxClimbingSites.marker.getPosition().lng().toFixed(6);

	// Update visible fields
	document[TBE_EDITOR.formname]['{$latitudeField}_hr'].value = lat;
	document[TBE_EDITOR.formname]['{$longitudeField}_hr'].value = lng;

	// Update hidden (real) fields
	document[TBE_EDITOR.formname]['{$latitudeField}'].value = lat;
	document[TBE_EDITOR.formname]['{$longitudeField}'].value = lng;

	// Update address
	TxClimbingSites.reverseGeocode(TxClimbingSites.marker.getPosition().lat(), TxClimbingSites.marker.getPosition().lng());

	// Tell TYPO3 that fields were updated
	{$updateLatitudeJs}
	{$updateLongitudeJs}
}

TxClimbingSites.refreshMap = function() {
	google.maps.event.trigger(TxClimbingSites.map, 'resize');
	// No need to do it again
	Ext.fly(TxClimbingSites.tabPrefix + '-MENU').un('click', TxClimbingSites.refreshMap);
}

TxClimbingSites.codeAddress = function() {
	var address = document.getElementById("{$addressId}").value;
	TxClimbingSites.geocoder.geocode({'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			TxClimbingSites.map.setCenter(results[0].geometry.location);
			TxClimbingSites.marker.setPosition(results[0].geometry.location);
			TxClimbingSites.updateCoord();
		} else {
			alert("Geocode was not successful for the following reason: " + status);
		}
	});
}

TxClimbingSites.reverseGeocode = function(latitude, longitude) {
	var latlng = new google.maps.LatLng(latitude, longitude);
	TxClimbingSites.geocoder.geocode({'latLng': latlng}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK && results[1]) {
			var address = document.getElementById("{$addressId}");
			address.value = results[1].formatted_address;
		}
	});
}

window.onload = TxClimbingSites.init;
EOT;
		$out[] = '</script>';
		$out[] = '<div id="' . $baseElementId . '">';
		$out[] = '
			<input id="' . $addressId . '" type="textbox" value="" style="width:300px">
			<input type="button" value="Show" onclick="TxClimbingSites.codeAddress()">
			<br/><br/>
		';
		$out[] = '<div id="' . $mapId . '" style="height:300px;width:550px"></div>';
		$out[] = '</div>'; // id=$baseElementId

		return implode('', $out);
	}

}

?>