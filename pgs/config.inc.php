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

$PageOptions['ReflectorLogo']['src']                 = './img/xlx299logot.svg';
$PageOptions['ReflectorLogo']['alt']                 = 'XLX299 Multiprotocol Gateway';
$PageOptions['Host']				     = 'Hosted by <a href="https://dvnz.nz/">Digital Voice NZ</a>';
$PageOptions['Social']				     = '<a href="https://t.me/+H8KgiQPdncUyMDgz" target="_blank"><span class="material-icons">telegram</span>&nbsp;Join DVNZ on Telegram</a>';
$PageOptions['ReflectorName']                        = 'XLX299';
$PageOptions['ModuleNames'] = array();                                			// Module nomination
$PageOptions['ModuleNames']['A']                     = 'XLX Worldwide';
$PageOptions['ModuleNames']['B']                     = 'FreeDMR-530 Peanut M17-NZD-B';
$PageOptions['ModuleNames']['C']                     = 'USA XLX315';
$PageOptions['ModuleNames']['D']                     = 'BM-TG530';
$PageOptions['ModuleNames']['E']                     = 'M17-NZD-E Bridge';
$PageOptions['ModuleNames']['F']                     = 'ZL2 Regional FD5302 M17-NZD-F';
$PageOptions['ModuleNames']['G']                     = 'Caribbean TGIF969';
$PageOptions['ModuleNames']['H']                     = 'ANZEL';
$PageOptions['ModuleNames']['I']                     = 'FM RepeaterLink VK/ZL FD50510';
$PageOptions['ModuleNames']['J']                     = 'D-Star only';
$PageOptions['ModuleNames']['K']                     = 'Chit-chat FD53029 M17-NZD-K';
//$PageOptions['ModuleNames']['L']                     = '';
$PageOptions['ModuleNames']['M']                     = 'GetOnAir';
$PageOptions['ModuleNames']['N']                     = 'DV-Tech Talk';
$PageOptions['ModuleNames']['O']                     = 'ZL-TRBO';
//$PageOptions['ModuleNames']['P']                     = 'TRBO-1World-w';
$PageOptions['ModuleNames']['Q']                     = 'CQ UK';
$PageOptions['ModuleNames']['R']                     = 'FD53080 Peanut';
$PageOptions['ModuleNames']['S']                     = 'Active-Elements TG53085';
$PageOptions['ModuleNames']['T']                     = 'Oceania FD953';
$PageOptions['ModuleNames']['U']                     = 'D-Star UK';
$PageOptions['ModuleNames']['V']                     = 'VK Calling FD505';
$PageOptions['ModuleNames']['W']                     = 'VK FD5050';
$PageOptions['ModuleNames']['X']                     = 'eXperimental';
$PageOptions['ModuleNames']['Y']                     = 'YOTA';
$PageOptions['ModuleNames']['Z']                     = 'YCS530 DG53';
$PageOptions['ShortNames'] = array();                                			// Module nomination
$PageOptions['ShortNames']['A']                     = 'XLX Worldwide';
$PageOptions['ShortNames']['B']                     = 'FD ZL National';
$PageOptions['ShortNames']['C']                     = 'USA XLX315';
$PageOptions['ShortNames']['D']                     = 'BM ZL National';
$PageOptions['ShortNames']['E']                     = 'M17-NZD-E';
$PageOptions['ShortNames']['F']                     = 'FD ZL2 Regional';
$PageOptions['ShortNames']['G']                     = 'Caribbean';
$PageOptions['ShortNames']['H']                     = 'ANZEL';
$PageOptions['ShortNames']['I']                     = 'FM Rptlnk VK/ZL';
$PageOptions['ShortNames']['J']                     = 'D-Star only';
$PageOptions['ShortNames']['K']                     = 'Chit-chat';
//$PageOptions['ShortNames']['L']                     = '';
$PageOptions['ShortNames']['M']                     = 'GetOnAir';
$PageOptions['ShortNames']['N']                     = 'DV-Tech Talk';
$PageOptions['ShortNames']['O']                     = 'ZL-TRBO';
//$PageOptions['ShortNames']['P']                     = 'TRBO-1World-w';
$PageOptions['ShortNames']['Q']                     = 'CQ UK';
$PageOptions['ShortNames']['R']                     = 'ROAR';
$PageOptions['ShortNames']['S']                     = 'Active-Elements';
$PageOptions['ShortNames']['T']                     = 'Oceania FD953';
$PageOptions['ShortNames']['U']                     = 'D-Star UK';
$PageOptions['ShortNames']['V']                     = 'VK Calling 505';
$PageOptions['ShortNames']['W']                     = 'VK FD5050';
$PageOptions['ShortNames']['X']                     = 'eXperimental';
$PageOptions['ShortNames']['Y']                     = 'YOTA';
$PageOptions['ShortNames']['Z']                     = 'YCS530 DG53';

// Icons are expected to be 65x65 square
// if the filename contains -130, the 130x65 rectangle
$PageOptions['ModuleIcons'] = array();                                			// Module nomination
$PageOptions['ModuleIcons']['A']                     = './img/globe-65.webp';
$PageOptions['ModuleIcons']['B']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['C']                     = './img/usa.jpg';
$PageOptions['ModuleIcons']['D']                     = './img/brandmeister-130.webp';
$PageOptions['ModuleIcons']['R']                     = './img/modules/roar.jpg';
$PageOptions['ModuleIcons']['E']                     = './img/kiwi_radio.webp';
$PageOptions['ModuleIcons']['F']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['G']                     = './img/snoopy.webp';
$PageOptions['ModuleIcons']['H']                     = './img/anzel-130.webp';
$PageOptions['ModuleIcons']['I']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['J']                     = './img/kiwi_radio.webp';
$PageOptions['ModuleIcons']['K']                     = './img/FreeDMRNZ-130.webp';
//$PageOptions['ModuleIcons']['L']                     = '';
$PageOptions['ModuleIcons']['M']                     = './img/globe-65.webp';
$PageOptions['ModuleIcons']['N']                     = './img/kiwi_radio.webp';
$PageOptions['ModuleIcons']['O']                     = './img/zltrbo-130.webp';
$PageOptions['ModuleIcons']['P']                     = './img/globe-65.webp';
$PageOptions['ModuleIcons']['Q']                     = './img/uk.png';
$PageOptions['ModuleIcons']['R']                     = './img/ROAR2-65.webp';
$PageOptions['ModuleIcons']['S']                     = './img/brandmeister-130.webp';
$PageOptions['ModuleIcons']['T']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['U']                     = './img/uk.png';
$PageOptions['ModuleIcons']['V']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['W']                     = './img/FreeDMRNZ-130.webp';
$PageOptions['ModuleIcons']['X']                     = './img/kiwi_radio.webp';
$PageOptions['ModuleIcons']['Y']                     = './img/globe-65.webp';
$PageOptions['ModuleIcons']['Z']                     = './img/kiwi_radio.webp';


$PageOptions['MetaDescription']                      = 'XLX299 is a NZ-based Multimode Reflector System for Ham Radio Operators.';  // Meta Tag Values, useful for Search Engine
$PageOptions['MetaKeywords']                         = 'Ham Radio, D-Star, XReflector, XLX, XRF, DCS, REF, ';        // Meta Tag Values, useful forSearch Engine
$PageOptions['MetaAuthor']                           = 'LX1IQ';                                                      // Meta Tag Values, useful for Search Engine
$PageOptions['MetaRevisit']                          = 'After 30 Days';                                              // Meta Tag Values, useful for Search Engine
$PageOptions['MetaRobots']                           = 'index,follow';                                               // Meta Tag Values, useful for Search Engine
                                     // OpenGraph image og:img in header
$PageOptions['MetaImage']                            = '/img/xlx299logot.png';

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
    "V" => "XRF299V",
];



/*
  include an extra config file for people who dont like to mess with shipped config.ing.php
  this makes updating dashboard from git a little bit easier
*/

if (file_exists("../config.inc.php")) {
  include ("../config.inc.php");
}

?>
