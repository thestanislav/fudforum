<?php
/***************************************************************************
* copyright            : (C) 2001-2003 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: tz.inc.t,v 1.5 2003/10/09 14:34:27 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it 
* under the terms of the GNU General Public License as published by the 
* Free Software Foundation; either version 2 of the License, or 
* (at your option) any later version.
***************************************************************************/

if (@getenv('OSTYPE') == 'AIX' || @strpos(php_uname(), 'AIX')) {
	$GLOBALS['tz_names'] = "Coordinated Universal Time\nUnited Kingdom\nAzores, Cape Verde\nFalkland Islands\nGreenland, East Brazil\nCentral Brazil\nEastern United States, Colombia\nCentral United States, Honduras\nMountain United States\nPacific United States, Yukon\nAlaska\nHawaii, Aleutian Islands\nBering Strait\nNew Zealand\nSolomon Islands\nEastern Australia\nJapan\nKorea\nWestern Australia\nTaiwan\nThailand\nCentral Asia\nPakistan\nGorki, Central Asia, Oman\nTurkey\nSaudi Arabia\nFinland\nSouth Africa\nNorway";
	$GLOBALS['tz_values'] = "CUT0GDT\nGMT0BST\nAZOREST1AZOREDT\nFALKST2FALKDT\nGRNLNDST3GRNLNDDT\nAST4ADT\nEST5EDT\nCST6CDT\nMST7MDT\nPST8PDT\nAST9ADT\nHST10HDT\nBST11BDT\nNZST-12NZDT\nMET-11METDT\nEET-10EETDT\nJST-9JSTDT\nKORST-9KORDT\nWAUST-8WAUDT\nTAIST-8TAIDT\nTHAIST-7THAIDT\nTASHST-6TASHDT\nPAKST-5PAKDT\nWST-4WDT\nMEST-3MEDT\nSAUST-3SAUDT\nWET-2WET\nUSAST-2USADT\nNFT-1DFT";
} else {
	$GLOBALS['tz_names'] = "\nAfghanistan/Kabul\nAlbania/Tirane\nAlgeria/Algiers\nAndorra/Andorra\nAngola/Luanda\nAnguilla/Anguilla\nAntarctica/Casey Casey Station, Bailey Peninsula\nAntarctica/Davis Davis Station, Vestfold Hills\nAntarctica/DumontDUrville Dumont-d'Urville Base, Terre Adelie\nAntarctica/Mawson Mawson Station, Holme Bay\nAntarctica/McMurdo McMurdo Station, Ross Island\nAntarctica/Palmer Palmer Station, Anvers Island\nAntarctica/South Pole Amundsen-Scott Station, South Pole\nAntarctica/Syowa Syowa Station, E Ongul I\nAntigua & Barbuda/Antigua\nArgentina/Buenos Aires E Argentina (BA, DF, SC, TF)\nArgentina/Catamarca Catamarca (CT)\nArgentina/Cordoba W Argentina (CB, SA, TM, LR, SJ, SL, NQ, RN)\nArgentina/Jujuy Jujuy (JY)\nArgentina/Mendoza Mendoza (MZ)\nArgentina/Rosario NE Argentina (SF, ER, CN, MN, CC, FM, LP, CH)\nArmenia/Yerevan\nAruba/Aruba\nAustralia/Adelaide South Australia\nAustralia/Brisbane Queensland - most locations\nAustralia/Broken Hill New South Wales - Broken Hill\nAustralia/Darwin Northern Territory\nAustralia/Hobart Tasmania\nAustralia/Lindeman Queensland - Holiday Islands\nAustralia/Lord Howe Lord Howe Island\nAustralia/Melbourne Victoria\nAustralia/Perth Western Australia\nAustralia/Sydney New South Wales - most locations\nAustria/Vienna\nAzerbaijan/Baku\nBahamas/Nassau\nBahrain/Bahrain\nBangladesh/Dhaka\nBarbados/Barbados\nBelarus/Minsk\nBelgium/Brussels\nBelize/Belize\nBenin/Porto-Novo\nBermuda/Bermuda\nBhutan/Thimphu\nBolivia/La Paz\nBosnia & Herzegovina/Sarajevo\nBotswana/Gaborone\nBrazil/Araguaina Tocantins\nBrazil/Belem Amapa, E Para\nBrazil/Boa Vista Roraima\nBrazil/Cuiaba Mato Grosso, Mato Grosso do Sul\nBrazil/Eirunepe W Amazonas\nBrazil/Fortaleza NE Brazil (MA, PI, CE, RN, PR)\nBrazil/Maceio Alagoas, Sergipe\nBrazil/Manaus E Amazonas\nBrazil/Noronha Atlantic islands\nBrazil/Porto Acre Acre\nBrazil/Porto Velho W Para, Rondonia\nBrazil/Recife Pernambuco\nBrazil/Sao Paulo S & SE Brazil (BA, GO, DF, MG, ES, RJ, SP, PR, SC, RS)\nBritain (UK)/Belfast Northern Ireland\nBritain (UK)/London Great Britain\nBritish Indian Ocean Territory/Chagos\nBrunei/Brunei\nBulgaria/Sofia\nBurkina Faso/Ouagadougou\nBurundi/Bujumbura\nCambodia/Phnom Penh\nCameroon/Douala\nCanada/Cambridge Bay Central Time - west Nunavut\nCanada/Dawson Pacific Time - north Yukon\nCanada/Dawson Creek Mountain Standard Time - Dawson Creek & Fort Saint John, British Columbia\nCanada/Edmonton Mountain Time - Alberta, east British Columbia & west Saskatchewan\nCanada/Glace Bay Atlantic Time - Nova Scotia - places that did not observe DST 1966-1971\nCanada/Goose Bay Atlantic Time - E Labrador\nCanada/Halifax Atlantic Time - Nova Scotia (most places), NB, W Labrador, E Quebec & PEI\nCanada/Inuvik Mountain Time - west Northwest Territories\nCanada/Iqaluit Eastern Standard Time - east Nunavut\nCanada/Montreal Eastern Time - Ontario & Quebec - most locations\nCanada/Nipigon Eastern Time - Ontario & Quebec - places that did not observe DST 1967-1973\nCanada/Pangnirtung Eastern Standard Time - Pangnirtung, Nunavut\nCanada/Rainy River Central Time - Rainy River & Fort Frances, Ontario\nCanada/Rankin Inlet Eastern Standard Time - central Nunavut\nCanada/Regina Central Standard Time - Saskatchewan - most locations\nCanada/St Johns Newfoundland Island\nCanada/Swift Current Central Standard Time - Saskatchewan - midwest\nCanada/Thunder Bay Eastern Time - Thunder Bay, Ontario\nCanada/Vancouver Pacific Time - west British Columbia\nCanada/Whitehorse Pacific Time - south Yukon\nCanada/Winnipeg Central Time - Manitoba & west Ontario\nCanada/Yellowknife Mountain Time - central Northwest Territories\nCape Verde/Cape Verde\nCayman Islands/Cayman\nCentral African Rep./Bangui\nChad/Ndjamena\nChile/Easter Easter Island\nChile/Santiago mainland\nChina/Chungking China mountains\nChina/Harbin north Manchuria\nChina/Kashgar Eastern Turkestan\nChina/Shanghai China coast\nChina/Urumqi Tibet & Xinjiang\nChristmas Island/Christmas\nCocos (Keeling) Islands/Cocos\nColombia/Bogota\nComoros/Comoro\nCongo (Dem. Rep.)/Kinshasa west Dem. Rep. of Congo\nCongo (Dem. Rep.)/Lubumbashi east Dem. Rep. of Congo\nCongo (Rep.)/Brazzaville\nCook Islands/Rarotonga\nCosta Rica/Costa Rica\nCote d'Ivoire/Abidjan\nCroatia/Zagreb\nCuba/Havana\nCyprus/Nicosia\nCzech Republic/Prague\nDenmark/Copenhagen\nDjibouti/Djibouti\nDominica/Dominica\nDominican Republic/Santo Domingo\nEast Timor/Dili\nEcuador/Galapagos Galapagos Islands\nEcuador/Guayaquil mainland\nEgypt/Cairo\nEl Salvador/El Salvador\nEquatorial Guinea/Malabo\nEritrea/Asmera\nEstonia/Tallinn\nEthiopia/Addis Ababa\nFaeroe Islands/Faeroe\nFalkland Islands/Stanley\nFiji/Fiji\nFinland/Helsinki\nFrance/Paris\nFrench Guiana/Cayenne\nFrench Polynesia/Gambier Gambier Islands\nFrench Polynesia/Marquesas Marquesas Islands\nFrench Polynesia/Tahiti Society Islands\nFrench Southern & Antarctic Lands/Kerguelen\nGabon/Libreville\nGambia/Banjul\nGeorgia/Tbilisi\nGermany/Berlin\nGhana/Accra\nGibraltar/Gibraltar\nGreece/Athens\nGreenland/Godthab southwest Greenland\nGreenland/Scoresbysund east Greenland\nGreenland/Thule northwest Greenland\nGrenada/Grenada\nGuadeloupe/Guadeloupe\nGuam/Guam\nGuatemala/Guatemala\nGuinea/Conakry\nGuinea-Bissau/Bissau\nGuyana/Guyana\nHaiti/Port-au-Prince\nHonduras/Tegucigalpa\nHong Kong/Hong Kong\nHungary/Budapest\nIceland/Reykjavik\nIndia/Calcutta\nIndonesia/Jakarta Java & Sumatra\nIndonesia/Jayapura Irian Jaya & the Moluccas\nIndonesia/Ujung Pandang Borneo & Celebes\nIran/Tehran\nIraq/Baghdad\nIreland/Dublin\nIsrael/Jerusalem\nItaly/Rome\nJamaica/Jamaica\nJapan/Tokyo\nJordan/Amman\nKazakhstan/Almaty east Kazakhstan\nKazakhstan/Aqtau west Kazakhstan\nKazakhstan/Aqtobe central Kazakhstan\nKenya/Nairobi\nKiribati/Enderbury Phoenix Islands\nKiribati/Kiritimati Line Islands\nKiribati/Tarawa Gilbert Islands\nKorea (North)/Pyongyang\nKorea (South)/Seoul\nKuwait/Kuwait\nKyrgyzstan/Bishkek\nLaos/Vientiane\nLatvia/Riga\nLebanon/Beirut\nLesotho/Maseru\nLiberia/Monrovia\nLibya/Tripoli\nLiechtenstein/Vaduz\nLithuania/Vilnius\nLuxembourg/Luxembourg\nMacao/Macao\nMacedonia/Skopje\nMadagascar/Antananarivo\nMalawi/Blantyre\nMalaysia/Kuala Lumpur peninsular Malaysia\nMalaysia/Kuching Sabah & Sarawak\nMaldives/Maldives\nMali/Bamako southwest Mali\nMali/Timbuktu northeast Mali\nMalta/Malta\nMarshall Islands/Kwajalein Kwajalein\nMarshall Islands/Majuro most locations\nMartinique/Martinique\nMauritania/Nouakchott\nMauritius/Mauritius\nMayotte/Mayotte\nMexico/Cancun Central Time - Quintana Roo\nMexico/Chihuahua Mountain Time - Chihuahua\nMexico/Hermosillo Mountain Standard Time - Sonora\nMexico/Mazatlan Mountain Time - S Baja, Nayarit, Sinaloa\nMexico/Merida Central Time - Campeche, Yucatan\nMexico/Mexico City Central Time - most locations\nMexico/Monterrey Central Time - Coahuila, Durango, Nuevo Leon, Tamaulipas\nMexico/Tijuana Pacific Time\nMicronesia/Kosrae Kosrae\nMicronesia/Ponape Ponape (Pohnpei)\nMicronesia/Truk Truk (Chuuk)\nMicronesia/Yap Yap\nMoldova/Chisinau most locations\nMoldova/Tiraspol Transdniestria\nMonaco/Monaco\nMongolia/Hovd Bayan-Olgiy, Hovd, Uvs\nMongolia/Ulaanbaatar most locations\nMontserrat/Montserrat\nMorocco/Casablanca\nMozambique/Maputo\nMyanmar (Burma)/Rangoon\nNamibia/Windhoek\nNauru/Nauru\nNepal/Katmandu\nNetherlands/Amsterdam\nNetherlands Antilles/Curacao\nNew Caledonia/Noumea\nNew Zealand/Auckland most locations\nNew Zealand/Chatham Chatham Islands\nNicaragua/Managua\nNiger/Niamey\nNigeria/Lagos\nNiue/Niue\nNorfolk Island/Norfolk\nNorthern Mariana Islands/Saipan\nNorway/Oslo\nOman/Muscat\nPakistan/Karachi\nPalau/Palau\nPalestine/Gaza\nPanama/Panama\nPapua New Guinea/Port Moresby\nParaguay/Asuncion\nPeru/Lima\nPhilippines/Manila\nPitcairn/Pitcairn\nPoland/Warsaw\nPortugal/Azores Azores\nPortugal/Lisbon mainland\nPortugal/Madeira Madeira Islands\nPuerto Rico/Puerto Rico\nQatar/Qatar\nReunion/Reunion\nRomania/Bucharest\nRussia/Anadyr Moscow+10 - Bering Sea\nRussia/Irkutsk Moscow+05 - Lake Baikal\nRussia/Kaliningrad Moscow-01 - Kaliningrad\nRussia/Kamchatka Moscow+09 - Kamchatka\nRussia/Krasnoyarsk Moscow+04 - Yenisei River\nRussia/Magadan Moscow+08 - Magadan & Sakhalin\nRussia/Moscow Moscow+00 - west Russia\nRussia/Novosibirsk Moscow+03 - Novosibirsk\nRussia/Omsk Moscow+03 - west Siberia\nRussia/Samara Moscow+01 - Caspian Sea\nRussia/Vladivostok Moscow+07 - Amur River\nRussia/Yakutsk Moscow+06 - Lena River\nRussia/Yekaterinburg Moscow+02 - Urals\nRwanda/Kigali\nSamoa (American)/Pago Pago\nSamoa (Western)/Apia\nSan Marino/San Marino\nSao Tome & Principe/Sao Tome\nSaudi Arabia/Riyadh\nSenegal/Dakar\nSeychelles/Mahe\nSierra Leone/Freetown\nSingapore/Singapore\nSlovakia/Bratislava\nSlovenia/Ljubljana\nSolomon Islands/Guadalcanal\nSomalia/Mogadishu\nSouth Africa/Johannesburg\nSouth Georgia & the South Sandwich Islands/South Georgia\nSpain/Canary Canary Islands\nSpain/Ceuta Ceuta & Melilla\nSpain/Madrid mainland\nSri Lanka/Colombo\nSt Helena/St Helena\nSt Kitts & Nevis/St Kitts\nSt Lucia/St Lucia\nSt Pierre & Miquelon/Miquelon\nSt Vincent/St Vincent\nSudan/Khartoum\nSuriname/Paramaribo\nSvalbard & Jan Mayen/Jan Mayen Jan Mayen\nSvalbard & Jan Mayen/Longyearbyen Svalbard\nSwaziland/Mbabane\nSweden/Stockholm\nSwitzerland/Zurich\nSyria/Damascus\nTaiwan/Taipei\nTajikistan/Dushanbe\nTanzania/Dar es Salaam\nThailand/Bangkok\nTogo/Lome\nTokelau/Fakaofo\nTonga/Tongatapu\nTrinidad & Tobago/Port of Spain\nTunisia/Tunis\nTurkey/Istanbul\nTurkmenistan/Ashgabat\nTurks & Caicos Is/Grand Turk\nTuvalu/Funafuti\nUS minor outlying islands/Johnston Johnston Atoll\nUS minor outlying islands/Midway Midway Islands\nUS minor outlying islands/Wake Wake Island\nUganda/Kampala\nUkraine/Kiev most locations\nUkraine/Simferopol central Crimea\nUkraine/Uzhgorod Ruthenia\nUkraine/Zaporozhye Zaporozh'ye, E Lugansk\nUnited Arab Emirates/Dubai\nUnited States/Adak Aleutian Islands\nUnited States/Anchorage Alaska Time\nUnited States/Boise Mountain Time - south Idaho & east Oregon\nUnited States/Chicago Central Time\nUnited States/Denver Mountain Time\nUnited States/Detroit Eastern Time - Michigan - most locations\nUnited States/Honolulu Hawaii\nUnited States/Indiana Eastern Standard Time - Indiana - Crawford County\nUnited States/Indiana Eastern Standard Time - Indiana - Starke County\nUnited States/Indiana Eastern Standard Time - Indiana - Switzerland County\nUnited States/Indianapolis Eastern Standard Time - Indiana - most locations\nUnited States/Juneau Alaska Time - Alaska panhandle\nUnited States/Kentucky Eastern Time - Kentucky - Wayne County\nUnited States/Los Angeles Pacific Time\nUnited States/Louisville Eastern Time - Kentucky - Louisville area\nUnited States/Menominee Central Time - Michigan - Wisconsin border\nUnited States/New York Eastern Time\nUnited States/Nome Alaska Time - west Alaska\nUnited States/Phoenix Mountain Standard Time - Arizona\nUnited States/Shiprock Mountain Time - Navajo\nUnited States/Yakutat Alaska Time - Alaska panhandle neck\nUruguay/Montevideo\nUzbekistan/Samarkand west Uzbekistan\nUzbekistan/Tashkent east Uzbekistan\nVanuatu/Efate\nVatican City/Vatican\nVenezuela/Caracas\nVietnam/Saigon\nVirgin Islands (UK)/Tortola\nVirgin Islands (US)/St Thomas\nWallis & Futuna/Wallis\nWestern Sahara/El Aaiun\nYemen/Aden\nYugoslavia/Belgrade\nZambia/Lusaka\nZimbabwe/Harare";
	$GLOBALS['tz_values'] = "\nAsia/Kabul\nEurope/Tirane\nAfrica/Algiers\nEurope/Andorra\nAfrica/Luanda\nAmerica/Anguilla\nAntarctica/Casey\nAntarctica/Davis\nAntarctica/DumontDUrville\nAntarctica/Mawson\nAntarctica/McMurdo\nAntarctica/Palmer\nAntarctica/South_Pole\nAntarctica/Syowa\nAmerica/Antigua\nAmerica/Buenos_Aires\nAmerica/Catamarca\nAmerica/Cordoba\nAmerica/Jujuy\nAmerica/Mendoza\nAmerica/Rosario\nAsia/Yerevan\nAmerica/Aruba\nAustralia/Adelaide\nAustralia/Brisbane\nAustralia/Broken_Hill\nAustralia/Darwin\nAustralia/Hobart\nAustralia/Lindeman\nAustralia/Lord_Howe\nAustralia/Melbourne\nAustralia/Perth\nAustralia/Sydney\nEurope/Vienna\nAsia/Baku\nAmerica/Nassau\nAsia/Bahrain\nAsia/Dhaka\nAmerica/Barbados\nEurope/Minsk\nEurope/Brussels\nAmerica/Belize\nAfrica/Porto-Novo\nAtlantic/Bermuda\nAsia/Thimphu\nAmerica/La_Paz\nEurope/Sarajevo\nAfrica/Gaborone\nAmerica/Araguaina\nAmerica/Belem\nAmerica/Boa_Vista\nAmerica/Cuiaba\nAmerica/Eirunepe\nAmerica/Fortaleza\nAmerica/Maceio\nAmerica/Manaus\nAmerica/Noronha\nAmerica/Porto_Acre\nAmerica/Porto_Velho\nAmerica/Recife\nAmerica/Sao_Paulo\nEurope/Belfast\nEurope/London\nIndian/Chagos\nAsia/Brunei\nEurope/Sofia\nAfrica/Ouagadougou\nAfrica/Bujumbura\nAsia/Phnom_Penh\nAfrica/Douala\nAmerica/Cambridge_Bay\nAmerica/Dawson\nAmerica/Dawson_Creek\nAmerica/Edmonton\nAmerica/Glace_Bay\nAmerica/Goose_Bay\nAmerica/Halifax\nAmerica/Inuvik\nAmerica/Iqaluit\nAmerica/Montreal\nAmerica/Nipigon\nAmerica/Pangnirtung\nAmerica/Rainy_River\nAmerica/Rankin_Inlet\nAmerica/Regina\nAmerica/St_Johns\nAmerica/Swift_Current\nAmerica/Thunder_Bay\nAmerica/Vancouver\nAmerica/Whitehorse\nAmerica/Winnipeg\nAmerica/Yellowknife\nAtlantic/Cape_Verde\nAmerica/Cayman\nAfrica/Bangui\nAfrica/Ndjamena\nPacific/Easter\nAmerica/Santiago\nAsia/Chungking\nAsia/Harbin\nAsia/Kashgar\nAsia/Shanghai\nAsia/Urumqi\nIndian/Christmas\nIndian/Cocos\nAmerica/Bogota\nIndian/Comoro\nAfrica/Kinshasa\nAfrica/Lubumbashi\nAfrica/Brazzaville\nPacific/Rarotonga\nAmerica/Costa_Rica\nAfrica/Abidjan\nEurope/Zagreb\nAmerica/Havana\nAsia/Nicosia\nEurope/Prague\nEurope/Copenhagen\nAfrica/Djibouti\nAmerica/Dominica\nAmerica/Santo_Domingo\nAsia/Dili\nPacific/Galapagos\nAmerica/Guayaquil\nAfrica/Cairo\nAmerica/El_Salvador\nAfrica/Malabo\nAfrica/Asmera\nEurope/Tallinn\nAfrica/Addis_Ababa\nAtlantic/Faeroe\nAtlantic/Stanley\nPacific/Fiji\nEurope/Helsinki\nEurope/Paris\nAmerica/Cayenne\nPacific/Gambier\nPacific/Marquesas\nPacific/Tahiti\nIndian/Kerguelen\nAfrica/Libreville\nAfrica/Banjul\nAsia/Tbilisi\nEurope/Berlin\nAfrica/Accra\nEurope/Gibraltar\nEurope/Athens\nAmerica/Godthab\nAmerica/Scoresbysund\nAmerica/Thule\nAmerica/Grenada\nAmerica/Guadeloupe\nPacific/Guam\nAmerica/Guatemala\nAfrica/Conakry\nAfrica/Bissau\nAmerica/Guyana\nAmerica/Port-au-Prince\nAmerica/Tegucigalpa\nAsia/Hong_Kong\nEurope/Budapest\nAtlantic/Reykjavik\nAsia/Calcutta\nAsia/Jakarta\nAsia/Jayapura\nAsia/Ujung_Pandang\nAsia/Tehran\nAsia/Baghdad\nEurope/Dublin\nAsia/Jerusalem\nEurope/Rome\nAmerica/Jamaica\nAsia/Tokyo\nAsia/Amman\nAsia/Almaty\nAsia/Aqtau\nAsia/Aqtobe\nAfrica/Nairobi\nPacific/Enderbury\nPacific/Kiritimati\nPacific/Tarawa\nAsia/Pyongyang\nAsia/Seoul\nAsia/Kuwait\nAsia/Bishkek\nAsia/Vientiane\nEurope/Riga\nAsia/Beirut\nAfrica/Maseru\nAfrica/Monrovia\nAfrica/Tripoli\nEurope/Vaduz\nEurope/Vilnius\nEurope/Luxembourg\nAsia/Macao\nEurope/Skopje\nIndian/Antananarivo\nAfrica/Blantyre\nAsia/Kuala_Lumpur\nAsia/Kuching\nIndian/Maldives\nAfrica/Bamako\nAfrica/Timbuktu\nEurope/Malta\nPacific/Kwajalein\nPacific/Majuro\nAmerica/Martinique\nAfrica/Nouakchott\nIndian/Mauritius\nIndian/Mayotte\nAmerica/Cancun\nAmerica/Chihuahua\nAmerica/Hermosillo\nAmerica/Mazatlan\nAmerica/Merida\nAmerica/Mexico_City\nAmerica/Monterrey\nAmerica/Tijuana\nPacific/Kosrae\nPacific/Ponape\nPacific/Truk\nPacific/Yap\nEurope/Chisinau\nEurope/Tiraspol\nEurope/Monaco\nAsia/Hovd\nAsia/Ulaanbaatar\nAmerica/Montserrat\nAfrica/Casablanca\nAfrica/Maputo\nAsia/Rangoon\nAfrica/Windhoek\nPacific/Nauru\nAsia/Katmandu\nEurope/Amsterdam\nAmerica/Curacao\nPacific/Noumea\nPacific/Auckland\nPacific/Chatham\nAmerica/Managua\nAfrica/Niamey\nAfrica/Lagos\nPacific/Niue\nPacific/Norfolk\nPacific/Saipan\nEurope/Oslo\nAsia/Muscat\nAsia/Karachi\nPacific/Palau\nAsia/Gaza\nAmerica/Panama\nPacific/Port_Moresby\nAmerica/Asuncion\nAmerica/Lima\nAsia/Manila\nPacific/Pitcairn\nEurope/Warsaw\nAtlantic/Azores\nEurope/Lisbon\nAtlantic/Madeira\nAmerica/Puerto_Rico\nAsia/Qatar\nIndian/Reunion\nEurope/Bucharest\nAsia/Anadyr\nAsia/Irkutsk\nEurope/Kaliningrad\nAsia/Kamchatka\nAsia/Krasnoyarsk\nAsia/Magadan\nEurope/Moscow\nAsia/Novosibirsk\nAsia/Omsk\nEurope/Samara\nAsia/Vladivostok\nAsia/Yakutsk\nAsia/Yekaterinburg\nAfrica/Kigali\nPacific/Pago_Pago\nPacific/Apia\nEurope/San_Marino\nAfrica/Sao_Tome\nAsia/Riyadh\nAfrica/Dakar\nIndian/Mahe\nAfrica/Freetown\nAsia/Singapore\nEurope/Bratislava\nEurope/Ljubljana\nPacific/Guadalcanal\nAfrica/Mogadishu\nAfrica/Johannesburg\nAtlantic/South_Georgia\nAtlantic/Canary\nAfrica/Ceuta\nEurope/Madrid\nAsia/Colombo\nAtlantic/St_Helena\nAmerica/St_Kitts\nAmerica/St_Lucia\nAmerica/Miquelon\nAmerica/St_Vincent\nAfrica/Khartoum\nAmerica/Paramaribo\nAtlantic/Jan_Mayen\nArctic/Longyearbyen\nAfrica/Mbabane\nEurope/Stockholm\nEurope/Zurich\nAsia/Damascus\nAsia/Taipei\nAsia/Dushanbe\nAfrica/Dar_es_Salaam\nAsia/Bangkok\nAfrica/Lome\nPacific/Fakaofo\nPacific/Tongatapu\nAmerica/Port_of_Spain\nAfrica/Tunis\nEurope/Istanbul\nAsia/Ashgabat\nAmerica/Grand_Turk\nPacific/Funafuti\nPacific/Johnston\nPacific/Midway\nPacific/Wake\nAfrica/Kampala\nEurope/Kiev\nEurope/Simferopol\nEurope/Uzhgorod\nEurope/Zaporozhye\nAsia/Dubai\nAmerica/Adak\nAmerica/Anchorage\nAmerica/Boise\nAmerica/Chicago\nAmerica/Denver\nAmerica/Detroit\nPacific/Honolulu\nAmerica/Indiana/Marengo\nAmerica/Indiana/Knox\nAmerica/Indiana/Vevay\nAmerica/Indianapolis\nAmerica/Juneau\nAmerica/Kentucky/Monticello\nAmerica/Los_Angeles\nAmerica/Louisville\nAmerica/Menominee\nAmerica/New_York\nAmerica/Nome\nAmerica/Phoenix\nAmerica/Shiprock\nAmerica/Yakutat\nAmerica/Montevideo\nAsia/Samarkand\nAsia/Tashkent\nPacific/Efate\nEurope/Vatican\nAmerica/Caracas\nAsia/Saigon\nAmerica/Tortola\nAmerica/St_Thomas\nPacific/Wallis\nAfrica/El_Aaiun\nAsia/Aden\nEurope/Belgrade\nAfrica/Lusaka\nAfrica/Harare";
}
?>