<?php
/***********************************************************
IMPORTANT IF YOU INTEND TO MAKE CHANGES IN THIS FILE
The entries in this file are like this (taking as example one option for .fr domains but all others are similar):
$additionaldomainfields[".fr"][0] = array(
    "Name" => "Holder Type",
    "DisplayName" => "Type titulaire",
    "Type" => "dropdown",
    "Options" => "individual|Personne Physique,company|Entreprise,trademark|Titulaire de Marque,association|Association,other|Autre",
    "Default" => "individual",
    );

In the above do not make any changes in the "Name" field as it will make the module to stop workling properly
The text that will appear in the web interface is on the right of "DisplayName" => 
For example you can change this:
"DisplayName" => "Type titulaire",
with this:
"DisplayName" => "Owner type",

Also for entries that have drop downs the entries are a comma separated list of values liek this:
"Options" => "individual|Personne Physique,company|Entreprise,trademark|Titulaire de Marque,association|Association,other|Autre",
To generalize this the entries are a comma separated list of  "key|value" entries.  In that never change the key part (what is on the left of the | character. For example you can change this:
"Options" => "individual|Personne Physique,company|Entreprise,trademark|Titulaire de Marque,association|Association,other|Autre",
to this:
"Options" => "individual|Individual,company|Company,trademark|Trademark owner,association|Association,other|Other",

As you can see for each key|value group only the value can be changed. Changing the key will cause malfunction of the module

NOTE: Translation only works with WHMCS 4.5 and above!

************************************************************/



/*
  If you intend to register .uk domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */
/* * ************* START .UK ****************** */
$additionaldomainfields[".co.uk"]=array();
$additionaldomainfields[".co.uk"][] = array(
    "Name" => "Legal Type",
    "DisplayName" => "Legal Type",
    "Type" => "dropdown",
    "Options" => "Individual|Individual,UK Limited Company|UK Limited Company,UK Public Limited Company|UK Public Limited Company,UK Partnership|UK Partnership,UK Limited Liability Partnership|UK Limited Liability Partnership,Sole Trader|Sole Trader,UK Registered Charity|UK Registered Charity,UK Entity (other)|UK Entity (other),Foreign Organization|Foreign Organization,Other foreign organizations|Other foreign organizations",
    "Default" => "Individual",
);
# the following is NOT required when "Legal Type" is "Individual"
$additionaldomainfields[".co.uk"][] = array(
    "Name" => "Company ID Number",
    "DisplayName" => "Company Registration Number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
# the following is required only when "Legal Type" is "Individual"
$additionaldomainfields[".co.uk"][] = array(
    "Name" => "WHOIS Opt-out",
    "DisplayName" => "Hide whois details",
    "Type" => "tickbox",
);

$additionaldomainfields[".org.uk"] = $additionaldomainfields[".co.uk"];

$additionaldomainfields[".me.uk"] = $additionaldomainfields[".co.uk"];

/* * ************* END .UK ****************** */

/*
  If you intend to register .eu domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */
/* * ************* START .EU ****************** */
$additionaldomainfields[".eu"]=array();
$additionaldomainfields[".eu"][] = array(
    "Name" => "Language",
    "DisplayName" => "Language",
    "Type" => "dropdown",
    "Options" => "cs|Czech,da|Danish,de|German,el|Greek,en|English,es|Spanish,et|Estonian,fi|Finnish,fr|French,hu|Hungarian,it|Italian,lt|Lithuanian,lv|Latvian,mt|Maltese,nl|Nederlands,pl|Polish,pt|Portuguese,sk|Slovak,sl|Slovenian,sv|Swedish,ro|Romanian,bg|Bulgarian,ga|Irish",
    "Default" => "en",
    "Required" => true,
);
/* * ************* END .EU ****************** */

/* * ************* START .BE ****************** */
$additionaldomainfields[".be"]=array();
$additionaldomainfields[".be"][] = array(
    "Name" => "Language",
    "DisplayName" => "Language",
    "Type" => "dropdown",
    "Options" => "en|English,fr|French,nl|Nederlands",
    "Default" => "en",
    "Required" => true,
);
/* * ************* END .BE ****************** */

/*
  If you intend to register .asia domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */

/* * ************* START .ASIA ****************** */
$additionaldomainfields[".asia"]=array();
$additionaldomainfields[".asia"][] = array(
    "Name" => "Legal Entity Type",
    "DisplayName" => "Legal Entity Type",
    "Type" => "dropdown",
    "Options" => "naturalPerson|Natural person,corporation|Corporation,cooperative|Cooperative,partnership|Partnership,government|Government,politicalParty|Political Party,society|Society,institution|Institution,other|Other",
    "Default" => "naturalPerson",
);
$additionaldomainfields[".asia"][] = array(
    "Name" => "Identification Form",
    "DisplayName" => "Identification Form",
    "Type" => "dropdown",
    "Options" => "passport|Passport,certificate|Certificate,legislation|Legislation,societiesRegistry|Societies Registry,politicalPartyRegistry|Political Party Registry,other|Other",
    "Default" => "passport",
);
$additionaldomainfields[".asia"][] = array(
    "Name" => "Identification Number",
    "DisplayName" => "Identification Number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => true,
);
# the following is required only when "Legal Entity Type" is "other"
$additionaldomainfields[".asia"][] = array(
    "Name" => "Other legal entity type",
    "DisplayName" => "Other legal entity type",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
# the following is required only when "Identification Form" is "other"
$additionaldomainfields[".asia"][] = array(
    "Name" => "Other identification form",
    "DisplayName" => "Other identification form",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
/* * ************* END .ASIA ****************** */

/*
  If you intend to register .fr domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */

/* * ************* START .FR****************** */
$additionaldomainfields[".fr"]=array();
$additionaldomainfields[".fr"][] = array(
    "Name" => "Holder Type",
    "DisplayName" => "Holder Type",
    "Type" => "dropdown",
    "Options" => "individual|Individual,company|Company,trademark|Trademark owner,association|Association,other|Other",
    "Default" => "individual",
    );

# the following fields are required when "Holder Type" is "individual"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Birth Date YYYY-MM-DD",
    "DisplayName" => "Birth Date (YYYY-MM-DD)",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Birth Country Code",
    "DisplayName" => "Birth Country",
    "Options"=>'AF|Afghanistan,AX|Aland Islands,AL|Albania,DZ|Algeria,AS|American Samoa,AD|Andorra,AO|Angola,AI|Anguilla,AQ|Antarctica,AG|Antigua and Barbuda,AR|Argentina,AM|Armenia,AW|Aruba,AU|Australia,AT|Austria,AZ|Azerbaijan,BS|Bahamas,BH|Bahrain,BD|Bangladesh,BB|Barbados,BY|Belarus,BE|Belgium,BZ|Belize,BJ|Benin,BM|Bermuda,BT|Bhutan,BO|Bolivia,BA|Bosnia and Herzegovina,BW|Botswana,BV|Bouvet Island,BR|Brazil,IO|British Indian Ocean Territory,VG|British Virgin Islands,BN|Brunei,BG|Bulgaria,BF|Burkina Faso,BI|Burundi,KH|Cambodia,CM|Cameroon,CA|Canada,CV|Cape Verde,KY|Cayman Islands,CF|Central African Republic,TD|Chad,CL|Chile,CN|China,CX|Christmas Island,CC|Cocos (Keeling) Islands,CO|Colombia,KM|Comoros,CG|Congo,CK|Cook Islands,CR|Costa Rica,HR|Croatia,CU|Cuba,CY|Cyprus,CZ|Czech Republic,CD|Democratic Republic of Congo,DK|Denmark,DJ|Djibouti,DM|Dominica,DO|Dominican Republic,TL|East Timor,EC|Ecuador,EG|Egypt,SV|El Salvador,GQ|Equatorial Guinea,ER|Eritrea,EE|Estonia,ET|Ethiopia,FK|Falkland Islands,FO|Faroe Islands,FM|Federated States of Micronesia,FJ|Fiji,FI|Finland,FR|France,GF|French Guyana,PF|French Polynesia,TF|French Southern Territories,GA|Gabon,GM|Gambia,GE|Georgia,DE|Germany,GH|Ghana,GI|Gibraltar,GR|Greece,GL|Greenland,GD|Grenada,GP|Guadeloupe,GU|Guam,GT|Guatemala,GG|Guernsey,GN|Guinea,GW|Guinea-Bissau,GY|Guyana,HT|Haiti,HM|Heard Island and Mcdonald Islands,HN|Honduras,HK|Hong Kong,HU|Hungary,IS|Iceland,IN|India,ID|Indonesia,IR|Iran,IQ|Iraq,IE|Ireland,IM|Isle of man,IL|Israel,IT|Italy,CI|Ivory Coast,JM|Jamaica,JP|Japan,JE|Jersey,JO|Jordan,KZ|Kazakhstan,KE|Kenya,KI|Kiribati,KW|Kuwait,KG|Kyrgyzstan,LA|Laos,LV|Latvia,LB|Lebanon,LS|Lesotho,LR|Liberia,LY|Libya,LI|Liechtenstein,LT|Lithuania,LU|Luxembourg,MO|Macau,MK|Macedonia,MG|Madagascar,MW|Malawi,MY|Malaysia,MV|Maldives,ML|Mali,MT|Malta,MH|Marshall Islands,MQ|Martinique,MR|Mauritania,MU|Mauritius,YT|Mayotte,MX|Mexico,MD|Moldova,MC|Monaco,MN|Mongolia,ME|Montenegro,MS|Montserrat,MA|Morocco,MZ|Mozambique,MM|Myanmar,NA|Namibia,NR|Nauru,NP|Nepal,NL|Netherlands,AN|Netherlands Antilles,NC|New Caledonia,NZ|New Zealand,NI|Nicaragua,NE|Niger,NG|Nigeria,NU|Niue,NF|Norfolk Island,KP|North Korea,MP|Northern Mariana Islands,NO|Norway,OM|Oman,PK|Pakistan,PW|Palau,PS|Palestinian Occupied Territories,PA|Panama,PG|Papua New Guinea,PY|Paraguay,PE|Peru,PH|Philippines,PN|Pitcairn Islands,PL|Poland,PT|Portugal,PR|Puerto Rico,QA|Qatar,RE|Reunion,RO|Romania,RU|Russia,RW|Rwanda,BL|Saint Barthélemy,SH|Saint Helena and Dependencies,KN|Saint Kitts and Nevis,LC|Saint Lucia,MF|Saint Martin,PM|Saint Pierre and Miquelon,VC|Saint Vincent and the Grenadines,WS|Samoa,SM|San Marino,ST|Sao Tome and Principe,SA|Saudi Arabia,SN|Senegal,RS|Serbia,SC|Seychelles,SL|Sierra Leone,SG|Singapore,SK|Slovakia,SI|Slovenia,SB|Solomon Islands,SO|Somalia,ZA|South Africa,GS|South Georgia and South Sandwich Islands,KR|South Korea,ES|Spain,LK|Sri Lanka,SD|Sudan,SR|Suriname,SJ|Svalbard and Jan Mayen,SZ|Swaziland,SE|Sweden,CH|Switzerland,SY|Syria,TW|Taiwan,TJ|Tajikistan,TZ|Tanzania,TH|Thailand,TG|Togo,TK|Tokelau,TO|Tonga,TT|Trinidad and Tobago,TN|Tunisia,TR|Turkey,TM|Turkmenistan,TC|Turks And Caicos Islands,TV|Tuvalu,VI|US Virgin Islands,UG|Uganda,UA|Ukraine,AE|United Arab Emirates,GB|United Kingdom,US|United States,UM|United States Minor Outlying Islands,UY|Uruguay,UZ|Uzbekistan,VU|Vanuatu,VA|Vatican City,VE|Venezuela,VN|Vietnam,WF|Wallis and Futuna,EH|Western Sahara,YE|Yemen,ZM|Zambia,ZW|Zimbabwe',
    "Type" => "dropdown",
    "Default" => "FR",
    "Required" => false,
);
# the following are required only when "Birth Country Code" is "fr"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Birth City",
    "DisplayName" => "Birth City",
    "Type" => "text",
    "Size" => "20",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Birth Postal code",
    "DisplayName" => "Birth Postal code",
    "Type" => "text",
    "Size" => "10",
    "Default" => "",
    "Required" => false,
);

# the following fields are required when "Holder Type" is "company" or "trademark"
# the field "Name" is also required when "Holder Type" is "company" or "association" or "other"
# the field "Siren" or "Trade Mark" is also required when "Holder Type" is "other"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Siren",
    "DisplayName" => "SIREN",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "VATNO",
    "DisplayName" => "VAT number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);

$additionaldomainfields[".fr"][] = array(
    "Name" => "DUNSNO",
    "DisplayName" => "DUNS number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);

# the following field is also required when  "Holder Type" is "trademark"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Trade Mark",
    "DisplayName" => "Trademark",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);

# the following fields are also required when "Holder Type" is "association"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Waldec",
    "DisplayName" => "Waldec",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Date of Association YYYY-MM-DD",
    "DisplayName" => "Date of Association (YYYY-MM-DD)",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Date of Publication YYYY-MM-DD",
    "DisplayName" => "Date of Publication (YYYY-MM-DD)",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Announce No",
    "DisplayName" => "Announcement number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);
$additionaldomainfields[".fr"][] = array(
    "Name" => "Page No",
    "DisplayName" => "Page number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);

# the following fields are also required when "Holder Type" is "other"
$additionaldomainfields[".fr"][] = array(
    "Name" => "Other Legal Status",
    "DisplayName" => "Other legal status",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => false,
);

$additionaldomainfields[".fr"][] = array(
    "Name" => "Restricted Publication",
    "DisplayName" => "Hide detais in whois (for individual only)",
    "Type" => "tickbox",
);


/* * ************* END .FR ****************** */


/*
  If you intend to register .re domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */

/* * ************* START .RE/.PM/.TF/.WF/.YT ****************** */
// all same as .fr 
$additionaldomainfields[".re"] = $additionaldomainfields[".fr"];
$additionaldomainfields[".pm"] = $additionaldomainfields[".fr"];
$additionaldomainfields[".tf"] = $additionaldomainfields[".fr"];
$additionaldomainfields[".wf"] = $additionaldomainfields[".fr"];
$additionaldomainfields[".yt"] = $additionaldomainfields[".fr"];
/* * ************* END .RE/.PM/.TF/.WF/.YT ****************** */

/*
  If you intend to register .it domains using this module make sure that the following exists in your includes/additionaldomainfields.php file,
  if not exists then add the following at the end of the file includes/additionaldomainfields.php
 */

/* * ************* START .IT****************** */
$additionaldomainfields[".it"]=array();
$additionaldomainfields[".it"][] = array(
    "Name" => "Legal Entity Type",
    "DisplayName" => "Holder Type",
    "Type" => "dropdown",
    "Options" => "1. Italian and foreign natural persons|1. Italian and foreign natural persons,2. Companies/one man companies|2. Companies/one man companies,3. Freelance workers/professionals|3. Freelance workers/professionals,4. non-profit organizations|4. non-profit organizations,5. public organizations|5. public organizations,6. other subjects|6. other subjects,7. foreigners who match 2 - 6|7. foreigners who match 2 - 6",
    "Default" => "1. Italian and foreign natural persons",
    "Required" => true,
);

$additionaldomainfields[".it"][] = array(
    "Name" => "Nationality",
    "DisplayName" => "Nationality",
    "Type" => "dropdown",
    "Options" => "AFGHANISTAN,ALAND ISLANDS,ALBANIA,ALGERIA,AMERICAN SAMOA,ANDORRA,ANGOLA,ANGUILLA,ANTARCTICA,ANTIGUA AND BARBUDA,ARGENTINA,ARMENIA,ARUBA,AUSTRALIA,AUSTRIA,AZERBAIJAN,BAHAMAS,BAHRAIN,BANGLADESH,BARBADOS,BELARUS,BELGIUM,BELIZE,BENIN,BERMUDA,BHUTAN,BOLIVIA,BOSNIA AND HERZEGOVINA,BOTSWANA,BOUVET ISLAND,BRAZIL,BRITISH INDIAN OCEAN TERRITORY,BRITISH VIRGIN ISLANDS,BRUNEI,BULGARIA,BURKINA FASO,BURUNDI,CAMBODIA,CAMEROON,CANADA,CAPE VERDE,CAYMAN ISLANDS,CENTRAL AFRICAN REPUBLIC,CHAD,CHILE,CHINA,CHRISTMAS ISLAND,COCOS (KEELING) ISLANDS,COLOMBIA,COMOROS,CONGO,COOK ISLANDS,COSTA RICA,CROATIA,CUBA,CYPRUS,CZECH REPUBLIC,DEMOCRATIC REPUBLIC OF CONGO,DENMARK,DISPUTED TERRITORY,DJIBOUTI,DOMINICA,DOMINICAN REPUBLIC,EAST TIMOR,ECUADOR,EGYPT,EL SALVADOR,EQUATORIAL GUINEA,ERITREA,ESTONIA,ETHIOPIA,FALKLAND ISLANDS,FAROE ISLANDS,FEDERATED STATES OF MICRONESIA,FIJI,FINLAND,FRANCE,FRENCH GUYANA,FRENCH POLYNESIA,FRENCH SOUTHERN TERRITORIES,GABON,GAMBIA,GEORGIA,GERMANY,GHANA,GIBRALTAR,GREECE,GREENLAND,GRENADA,GUADELOUPE,GUAM,GUATEMALA,GUERNSEY,GUINEA,GUINEA-BISSAU,GUYANA,HAITI,HEARD ISLAND AND MCDONALD ISLANDS,HONDURAS,HONG KONG,HUNGARY,ICELAND,INDIA,INDONESIA,IRAN,IRAQ,IRAQ-SAUDI ARABIA NEUTRAL ZONE,IRELAND,ISRAEL,ISLE OF MAN,ITALY,IVORY COAST,JAMAICA,JAPAN,JERSEY,JORDAN,KAZAKHSTAN,KENYA,KIRIBATI,KUWAIT,KYRGYZSTAN,LAOS,LATVIA,LEBANON,LESOTHO,LIBERIA,LIBYA,LIECHTENSTEIN,LITHUANIA,LUXEMBOURG,MACAU,MACEDONIA,MADAGASCAR,MALAWI,MALAYSIA,MALDIVES,MALI,MALTA,MARSHALL ISLANDS,MARTINIQUE,MAURITANIA,MAURITIUS,MAYOTTE,MEXICO,MOLDOVA,MONACO,MONGOLIA,MONTSERRAT,MOROCCO,MOZAMBIQUE,MYANMAR,NAMIBIA,NAURU,NEPAL,NETHERLANDS,NETHERLANDS ANTILLES,NEW CALEDONIA,NEW ZEALAND,NICARAGUA,NIGER,NIGERIA,NIUE,NORFOLK ISLAND,NORTH KOREA,NORTHERN MARIANA ISLANDS,NORWAY,OMAN,PAKISTAN,PALAU,PALESTINIAN OCCUPIED TERRITORIES,PANAMA,PAPUA NEW GUINEA,PARAGUAY,PERU,PHILIPPINES,PITCAIRN ISLANDS,POLAND,PORTUGAL,PUERTO RICO,QATAR,REUNION,ROMANIA,RUSSIA,RWANDA,SAINT HELENA AND DEPENDENCIES,SAINT KITTS AND NEVIS,SAINT LUCIA,SAINT PIERRE AND MIQUELON,SAINT VINCENT AND THE GRENADINES,SAMOA,SAN MARINO,SAO TOME AND PRINCIPE,SAUDI ARABIA,SENEGAL,SEYCHELLES,SIERRA LEONE,SINGAPORE,SLOVAKIA,SLOVENIA,SOLOMON ISLANDS,SOMALIA,SOUTH AFRICA,SOUTH GEORGIA AND SOUTH SANDWICH ISLANDS,SOUTH KOREA,SPAIN,SPRATLY ISLANDS,SRI LANKA,SUDAN,SURINAME,SVALBARD AND JAN MAYEN,SWAZILAND,SWEDEN,SWITZERLAND,SYRIA,TAIWAN,TAJIKISTAN,TANZANIA,THAILAND,TOGO,TOKELAU,TONGA,TRINIDAD AND TOBAGO,TUNISIA,TURKEY,TURKMENISTAN,TURKS AND CAICOS ISLANDS,TUVALU,UGANDA,UKRAINE,UNITED ARAB EMIRATES,UNITED KINGDOM,UNITED NATIONS NEUTRAL ZONE,UNITED STATES,UNITED STATES MINOR OUTLYING ISLANDS,URUGUAY,US VIRGIN ISLANDS,UZBEKISTAN,VANUATU,VATICAN CITY,VENEZUELA,VIETNAM,WALLIS AND FUTUNA,WESTERN SAHARA,YEMEN,ZAMBIA,ZIMBABWE,SERBIA,MONTENEGRO,SAINT MARTIN,SAINT BARTHELEMY",
    "Default" => "ITALY",
    "Required" => true,
);
//for ugrade need to execute: UPDATE `tbldomainsadditionalfields` INNER JOIN `tbldomains` ON `tbldomains`.id=`tbldomainsadditionalfields`.`domainid` SET `tbldomainsadditionalfields`.`name`='VATTAXPassportIDNumber' WHERE `tbldomainsadditionalfields`.`name`='VAT/TAX/Passport/ID Number' AND `tbldomains`.`registrar`='internetbs'
$additionaldomainfields[".it"][] = array(
    "Name" => "VATTAXPassportIDNumber",
    "DisplayName" => "VAT/TAX/Passport/ID Number",
    "Type" => "text",
    "Size" => "30",
    "Default" => "",
    "Required" => true,
);


$additionaldomainfields[".it"][] = array(
    "Name" => "Hide data in public WHOIS",
    "DisplayName" => "Hide data in public WHOIS",
    "Type" => "tickbox",
);

$additionaldomainfields[".it"][] = array(
    "Name" => 'Accept Nic.it registry <a href=\'itterms.html\' target=\'_blank\'>terms and conditions</a>',
    "DisplayName" => 'Accept .it registry <a href=\'itterms.html\' target=\'_blank\'>terms and conditions</a>',
    "Type" => "tickbox",
    "Required" => true,
);

/************** END .IT*******************/

/******** START .DE*********/
$additionaldomainfields[".de"]=array();
$additionaldomainfields[".de"][] = array(
    "Name" => "tosAgree",
    "Required" => true,
    "DisplayName" => "I agree to the <a href=\"http://www.denic.de/en/bedingungen.html\" target=\"_blank\">registry terms and conditions</a>",
    "Type" => "tickbox",
);
$additionaldomainfields[".de"][] = array(
    "Name" => "role",
    "Options" => "PERSON|Person,ORG|Organization",
    "Default" => "PERSON",
    "DisplayName" => "Contact role",
    "Type" => "dropdown",
);
$additionaldomainfields[".de"][] = array(
    "Name" => "sip",
    "DisplayName" => "SIP",
    "Type" => "text",
);
$additionaldomainfields[".de"][] = array(
		"Name" => "fax",
		"DisplayName" => "Fax Number",
		"Type" => "text",
);
$additionaldomainfields[".de"][] = array(
    "Name" => "Restricted Publication",
    "DisplayName" => "Hide details in <a href=\"http://www.whois.net\" target=\"_blank\">WHOIS</a>.",
    "Type" => "tickbox",
);

/******** END .DE*********/

/******** START .NL*********/
$additionaldomainfields[".nl"]=array();
$additionaldomainfields[".nl"][] = array(
    "Name" => "nlTerm",
    "Required" => true,
    "DisplayName" => "I agree to the <a href=\"https://www.sidn.nl/fileadmin/docs/PDF-files_UK/General_Terms_and_Conditions_for_.nl_Registrants.pdf\" target=\"_blank\">registry terms and conditions</a>",
    "Type" => "tickbox",
);
$additionaldomainfields[".nl"][] = array(
    "Name" => "nlLegalForm",
    "Options" => "BGG|Non-Dutch EC company,
BRO|Non-Dutch legal form/enterprise/subsidiary,
BV|Limited company,
BVI/O|Limited company in formation,
COOP|Cooperative,
CV|Limited Partnership,
EENMANSZAAK|Sole trader,
EESV|European Economic Interest Group,
KERK|Religious society,
MAATSCHAP|Partnership,
NV|Public Company,
OWM|Mutual benefit company,
PERSOON|Natural person,
REDR|Shipping company,
STICHTING|Foundation,
VERENIGING|Association,
VOF Trading|partnership,
ANDERS|Other",
     "Required" => true,
    "DisplayName" => "Legal Registration Form",
    "Type" => "dropdown",
);
$additionaldomainfields[".nl"][] = array(
    "Name" => "nlRegNumber",
    "DisplayName" => "Legal Registration Number",
    "Type" => "text",
);
/******** END .NL*********/
/******** START .TEL*********/
$additionaldomainfields[".tel"]=array();
$additionaldomainfields[".tel"][] = array(
		"Name" => "telhostingaccount",
    	"DisplayName" => "Hosting Account",
   		"Type" => "text",
);
$additionaldomainfields[".tel"][] = array(
		"Name" => "telhostingpassword",
    	"DisplayName" => "Hosting Password",
    	"Type" => "text",
);
$additionaldomainfields[".tel"][] = array(
		"Name" => "telhidewhoisdata",
    	"DisplayName" => "Hide details in <a href=\"http://www.whois.net\" target=\"_blank\">WHOIS</a>.",
    	"Type" => "tickbox",
);

/******** END .TEL*********/
?>