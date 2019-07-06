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

$PageOptions['ContactEmail']                         = 'zl2ro@xtra.co.nz';		    // Support E-Mail address

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

$PageOptions['LastHeardPage']['LimitTo']             = 39;                      // Number of stations to show

$PageOptions['ModuleNames'] = array();                                			// Module nomination
$PageOptions['ModuleNames']['A']                     = 'CQ UK ';
$PageOptions['ModuleNames']['B']                     = 'MMDV Peanut';
$PageOptions['ModuleNames']['C']                     = 'ZL TG 53050';
$PageOptions['ModuleNames']['D']                     = 'DV-Dongles';
$PageOptions['ModuleNames']['E']                     = 'DMR TG3131383';
$PageOptions['ModuleNames']['F']                     = 'TGIF TG31665';
$PageOptions['ModuleNames']['G']                     = 'Caribbean TG969';
$PageOptions['ModuleNames']['H']                     = '';
$PageOptions['ModuleNames']['I']                     = 'Ireland';
$PageOptions['ModuleNames']['J']                     = 'QRM TG31200';
$PageOptions['ModuleNames']['K']                     = 'Kiwi chit-chat';
$PageOptions['ModuleNames']['L']                     = '';
$PageOptions['ModuleNames']['M']                     = 'Manawatu / Wanganui';
$PageOptions['ModuleNames']['N']                     = 'DV-Tech Talk';
$PageOptions['ModuleNames']['O']                     = '';
$PageOptions['ModuleNames']['P']                     = '';
//$PageOptions['ModuleNames']['Q']                     = 'Queensland TG 5054';
$PageOptions['ModuleNames']['R']                     = '[Peanut] YSF 75161 / TG 53080';
$PageOptions['ModuleNames']['S']                     = '';
$PageOptions['ModuleNames']['T']                     = 'Taupo TG53060';
$PageOptions['ModuleNames']['U']                     = 'D-Star UK';
$PageOptions['ModuleNames']['V']                     = '';
$PageOptions['ModuleNames']['W']                     = '';
$PageOptions['ModuleNames']['X']                     = 'AREC';
$PageOptions['ModuleNames']['Y']                     = '';
$PageOptions['ModuleNames']['Z']                     = 'ZL 53029';

$PageOptions['ModuleIcons'] = array();                                			// Module nomination
$PageOptions['ModuleIcons']['A']                     = './img/globe.jpg';
$PageOptions['ModuleIcons']['B']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['C']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['D']                     = './img/kiwi_radio.png';
$PageOptions['ModuleIcons']['R']                     = './img/modules/roar.jpg';
$PageOptions['ModuleIcons']['E']                     = '/img/usa.jpg';
$PageOptions['ModuleIcons']['F']                     = './img/Snoopy.jpg';
$PageOptions['ModuleIcons']['G']                     = './img/Snoopy.jpg';
//$PageOptions['ModuleIcons']['H']                     = '';
$PageOptions['ModuleIcons']['I']                     = './img/ireland.png';
$PageOptions['ModuleIcons']['J']                     = './img/Snoopy.jpg';
$PageOptions['ModuleIcons']['K']                     = '/img/kiwi_radio.png';
//$PageOptions['ModuleIcons']['L']                     = '';
//$PageOptions['ModuleIcons']['M']                     = 'Manawatu / Wanganui';
$PageOptions['ModuleIcons']['N']                     = './img/kiwi_radio.png';
//$PageOptions['ModuleIcons']['O']                     = '';
//$PageOptions['ModuleIcons']['P']                     = '';
//$PageOptions['ModuleIcons']['Q']                     = './img/Queensland.png';
//$PageOptions['ModuleIcons']['R']                     = 'RIFROAR';
//$PageOptions['ModuleIcons']['S']                     = '';
$PageOptions['ModuleIcons']['T']                     = './img/taupo.png';
$PageOptions['ModuleIcons']['U']                     = './img/uk.png';
//$PageOptions['ModuleIcons']['V']                     = '';
//$PageOptions['ModuleIcons']['W']                     = '';
$PageOptions['ModuleIcons']['X']                     = '/img/AREC.jpg';
//$PageOptions['ModuleIcons']['Y']                     = '';
$PageOptions['ModuleIcons']['Z']                     = './img/kiwi_radio.png';


$PageOptions['MetaDescription']                      = 'XLX is a D-Star Reflector System for Ham Radio Operators.';  // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaKeywords']                         = 'Ham Radio, D-Star, XReflector, XLX, XRF, DCS, REF, ';        // Meta Tag Values, usefull forSearch Engine
$PageOptions['MetaAuthor']                           = 'LX1IQ';                                                      // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaRevisit']                          = 'After 30 Days';                                              // Meta Tag Values, usefull for Search Engine
$PageOptions['MetaRobots']                           = 'index,follow';                                               // Meta Tag Values, usefull for Search Engine

$PageOptions['UserPage']['ShowFilter']               = true;                                                         // Show Filter on Users page
$PageOptions['Traffic']['Show']                      = true;  

$Service['PIDFile']                                  = '/var/log/xlxd.pid';
$Service['XMLFile']                                  = '/var/log/xlxd.xml';

$CallingHome['Active']                               = true;					               // xlx phone home, true or false
$CallingHome['MyDashBoardURL']                       = 'http://www.xlx299.nz';			       // dashboard url
$CallingHome['ServerURL']                            = 'http://xlxapi.rlx.lu/api.php';         // database server, do not change !!!!
$CallingHome['PushDelay']                            = 600;  	                               // push delay in seconds
$CallingHome['Country']                              = "New Zealand Transcoding DMR YSF D-Star and Peanut + THGIF 31665 and TG969 QRM TG31200 and CQ UK";                         // Country
$CallingHome['Comment']                              = "Digital XLX Reflector Group NZ"; 				           // Comment. Max 100 character
$CallingHome['HashFile']                             = "/callinghome/callinghome.php";                 // Make sure the apache user has read and write permissions in this folder.
$CallingHome['OverrideIPAddress']                    = "";                                     // Insert your IP address here. Leave blank for autodetection. No need to enter a fake address.
$CallingHome['InterlinkFile']                        = "/xlxd/xlxd.interlink";                 // Path to interlink file



$VNStat['Interfaces']                                = array();
$VNStat['Interfaces'][0]['Name']                     = 'Network';
$VNStat['Interfaces'][0]['Address']                  = 'enp2s0';
$VNStat['Binary']                                    = '/usr/bin/vnstat';



/*
  include an extra config file for people who dont like to mess with shipped config.ing.php
  this makes updating dashboard from git a little bit easier
*/

if (file_exists("../config.inc.php")) {
  include ("../config.inc.php");
}

?>
