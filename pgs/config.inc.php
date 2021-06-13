<?php
/*
Possible values for IPModus

HideIP
ShowFullIP
ShowLast1ByteOfIP
ShowLast2ByteOfIP
ShowLast3ByteOfIP

*/

$Service     = array();
$CallingHome = array();
$PageOptions = array();

$PageOptions['ContactEmail']                         = 'info@dvnz.nz';		    // Support E-Mail address

$PageOptions['DashboardVersion']                     = '2.3.7';       			// Dashboard Version

$PageOptions['PageRefreshActive']                    = true;          			// Activate automatic refresh
$PageOptions['PageRefreshDelay']                     = '10000';       			// Page refresh time in miliseconds


$PageOptions['RepeatersPage'] = array();
$PageOptions['RepeatersPage']['LimitTo']             = 99;            			// Number of Repeaters to show
$PageOptions['RepeatersPage']['IPModus']             = 'HideIP'; 		 	// See possible options above
$PageOptions['RepeatersPage']['MasqueradeCharacter'] = '*';	        			// Character used for  masquerade


$PageOptions['PeerPage'] = array();
$PageOptions['PeerPage']['LimitTo']                  = 99;            			// Number of peers to show
$PageOptions['PeerPage']['IPModus']                  = 'HideIP';  			// See possible options above
$PageOptions['PeerPage']['MasqueradeCharacter']      = '*';           			// Character used for  masquerade

$PageOptions['LastHeardPage']['LimitTo']             = 29;                      // Number of stations to show-1

$PageOptions['ModuleNames'] = array();                                			// Module nomination
$PageOptions['ModuleNames']['A']                     = 'World-W';
$PageOptions['ModuleNames']['B']                     = 'MMDV Peanut';
$PageOptions['ModuleNames']['C']                     = 'USAXLX315';
$PageOptions['ModuleNames']['D']                     = 'DV-Dongles';
$PageOptions['ModuleNames']['E']                     = '';
$PageOptions['ModuleNames']['F']                     = '';
$PageOptions['ModuleNames']['G']                     = 'Caribbean TG969';
$PageOptions['ModuleNames']['H']                     = '';
$PageOptions['ModuleNames']['I']                     = 'Ireland';
$PageOptions['ModuleNames']['J']                     = 'QRM TG31200';
$PageOptions['ModuleNames']['K']                     = 'Kiwi chit-chat';
$PageOptions['ModuleNames']['L']                     = '';
$PageOptions['ModuleNames']['M']                     = '';
$PageOptions['ModuleNames']['N']                     = 'DV-Tech Talk';
$PageOptions['ModuleNames']['O']                     = 'ZL-TRBO';
$PageOptions['ModuleNames']['P']                     = 'TRBO-1World-w';
$PageOptions['ModuleNames']['Q']                     = 'TRBO ENG-13';
$PageOptions['ModuleNames']['R']                     = '[Peanut] / TG 53080';
$PageOptions['ModuleNames']['S']                     = 'Active-Elements TG 53085';
$PageOptions['ModuleNames']['T']                     = '';
$PageOptions['ModuleNames']['U']                     = 'D-Star UK';
$PageOptions['ModuleNames']['V']                     = '';
$PageOptions['ModuleNames']['W']                     = '';
$PageOptions['ModuleNames']['X']                     = 'FreeSTAR Module X';
$PageOptions['ModuleNames']['Y']                     = '';
$PageOptions['ModuleNames']['Z']                     = '';

$PageOptions['ModuleIcons'] = array();                                			// Module nomination
$PageOptions['ModuleIcons']['A']                     = './img/globe.jpg';
$PageOptions['ModuleIcons']['B']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['C']                     = './img/usa.jpg';
$PageOptions['ModuleIcons']['D']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['R']                     = './img/modules/roar.jpg';
$PageOptions['ModuleIcons']['E']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['F']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['G']                     = './img/Snoopy.jpg';
//$PageOptions['ModuleIcons']['H']                     = '';
$PageOptions['ModuleIcons']['I']                     = './img/ireland.png';
$PageOptions['ModuleIcons']['J']                     = './img/QRMNET.jpg';
$PageOptions['ModuleIcons']['K']                     = './img/kiwi_radio.png';
//$PageOptions['ModuleIcons']['L']                     = '';
//$PageOptions['ModuleIcons']['M']                     = 'Manawatu / Wanganui';
$PageOptions['ModuleIcons']['N']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['O']                     = './img/zltrbo.gif';
$PageOptions['ModuleIcons']['P']                     = './img/globe.jpg';
$PageOptions['ModuleIcons']['Q']                     = './img/globe.jpg';
$PageOptions['ModuleIcons']['R']                     = './img/ROAR2.jpg';
$PageOptions['ModuleIcons']['S']                     = './img/globe.jpg';
$PageOptions['ModuleIcons']['T']                     = '';
$PageOptions['ModuleIcons']['U']                     = './img/uk.png';
//$PageOptions['ModuleIcons']['V']                     = '';
//$PageOptions['ModuleIcons']['W']                     = '';
$PageOptions['ModuleIcons']['X']                     = './img/FreeSTAR.jpg';
//$PageOptions['ModuleIcons']['Y']                     = '';
$PageOptions['ModuleIcons']['Z']                     = './img/kiwi_radio.png';


$PageOptions['MetaDescription']                      = 'XLX is a D-Star Reflector System for Ham Radio Operators.';  // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaKeywords']                         = 'Ham Radio, D-Star, XReflector, XLX, XRF, DCS, REF, ';        // Meta Tag Values, usefull forSearch Engine
$PageOptions['MetaAuthor']                           = 'LX1IQ';                                                      // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaRevisit']                          = 'After 30 Days';                                              // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaRobots']                           = 'index,follow';                                               // Meta Tag Values, usefull for Search Engine

$PageOptions['UserPage']['ShowFilter']               = false;                                                         // Show Filter on Users page
$PageOptions['Traffic']['Show']                      = true;  

$Service['PIDFile']                                  = '/run/xlxd.pid';
$Service['XMLFile']                                  = '/run/xlxd.xml';

$CallingHome['Active']                               = true;					               // xlx phone home, true or false
$CallingHome['MyDashBoardURL']                       = 'https://www.xlx299.nz';			       // dashboard url
$CallingHome['ServerURL']                            = 'http://xlxapi.rlx.lu/api.php';         // database server, do not change !!!!
$CallingHome['PushDelay']                            = 600;  	                               // push delay in seconds
$CallingHome['Country']                              = "New Zealand Transcoding DMR YSF D-Star Peanut";                         // Country
$CallingHome['Comment']                              = "DVNZ Group"; 				           // Comment. Max 100 character
$CallingHome['HashFile']                             = "/callinghome/callinghome.php";                 // Make sure the apache user has read and write permissions in this folder.
$CallingHome['OverrideIPAddress']                    = "202.36.45.107";                                     // Insert your IP address here. Leave blank for autodetection. No need to enter a fake address.
$CallingHome['InterlinkFile']                        = "/xlxd/xlxd.interlink";                 // Path to interlink file



$VNStat['Interfaces']                                = array();
$VNStat['Interfaces'][0]['Name']                     = 'Network';
$VNStat['Interfaces'][0]['Address']                  = 'enp2s0';
$VNStat['Binary']                                    = '/usr/bin/vnstat';

define("PEANUTAPI", "http://peanut.pa7lim.nl/api/");
// APCu PNUT CACHE
define("PNUTLIMIT", 10);        // minimum seconds between API fetches
define("PNUTREFRESH", 170);     // normal seconds between API fetches
define("PNUTBACKOFF", 15*60);   // seconds if API read error

$pnutrooms = [
    "B" => "XRF299B",
    "G" => "TGF969",
    "K" => "XRF299K",
    "R" => "XRF299R",
    "S" => "XRF299S",
];



/*
  include an extra config file for people who dont like to mess with shipped config.ing.php
  this makes updating dashboard from git a little bit easier
*/

if (file_exists("../config.inc.php")) {
  include ("../config.inc.php");
}

?>
