<?php
define("MOTDCOOKIE", "motdseen");
define("MOTDFILE", dirname(__FILE__) . "/pgs/motd.txt");

/*
 *  This dashboard is being developed by the DVBrazil Team as a courtesy to
 *  the XLX Multiprotocol Gateway Reflector Server project.
 *  The dashboard is based of the Bootstrap dashboard template. 
*/

if (file_exists("./pgs/functions.php")) {
    require_once("./pgs/functions.php");
} else {
    die("functions.php does not exist.");
}
if (file_exists("./pgs/config.inc.php")) {
    require_once("./pgs/config.inc.php");
} else {
    die("config.inc.php does not exist.");
}

if (!class_exists('ParseXML')) require_once("./pgs/class.parsexml.php");
if (!class_exists('Node')) require_once("./pgs/class.node.php");
if (!class_exists('xReflector')) require_once("./pgs/class.reflector.php");
if (!class_exists('Station')) require_once("./pgs/class.station.php");
if (!class_exists('Peer')) require_once("./pgs/class.peer.php");
if (!class_exists('Interlink')) require_once("./pgs/class.interlink.php");

$Reflector = new xReflector();
$Reflector->SetFlagFile("./pgs/country.csv");
$Reflector->SetPIDFile($Service['PIDFile']);
$Reflector->SetXMLFile($Service['XMLFile']);

$Reflector->LoadXML();

if ($CallingHome['Active']) {

    $CallHomeNow = false;
    if (!file_exists($CallingHome['HashFile'])) {
        $Hash = CreateCode(16);
        $LastSync = 0;
        $Ressource = @fopen($CallingHome['HashFile'], "w");
        if ($Ressource) {
            @fwrite($Ressource, "<?php\n");
            @fwrite($Ressource, "\n" . '$LastSync = 0;');
            @fwrite($Ressource, "\n" . '$Hash     = "' . $Hash . '";');
            @fwrite($Ressource, "\n\n" . '?>');
            @fclose($Ressource);
            @exec("chmod 777 " . $CallingHome['HashFile']);
            $CallHomeNow = true;
        }
    } else {
        include($CallingHome['HashFile']);
        if ($LastSync < (time() - $CallingHome['PushDelay'])) {
            $Ressource = @fopen($CallingHome['HashFile'], "w");
            if ($Ressource) {
                @fwrite($Ressource, "<?php\n");
                @fwrite($Ressource, "\n" . '$LastSync = ' . time() . ';');
                @fwrite($Ressource, "\n" . '$Hash     = "' . $Hash . '";');
                @fwrite($Ressource, "\n\n" . '?>');
                @fclose($Ressource);
            }
            $CallHomeNow = true;
        }
    }

    if ($CallHomeNow || isset($_GET['callhome'])) {
        $Reflector->SetCallingHome($CallingHome, $Hash);
        $Reflector->ReadInterlinkFile();
        $Reflector->PrepareInterlinkXML();
        $Reflector->PrepareReflectorXML();
        $Reflector->CallHome();
    }
} else {
    $Hash = "";
}
// check if we want to set the motd cookie
$cookieset = FALSE;
if (isset($_POST['motd'])) {
    $cookieset = TRUE;
    setcookie(MOTDCOOKIE, strval(filemtime(MOTDFILE)), time() + 60*60*24*30);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo $PageOptions['MetaDescription']; ?>"/>
    <meta name="keywords" content="<?php echo $PageOptions['MetaKeywords']; ?>"/>
    <meta name="author" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>
    <meta name="revisit" content="<?php echo $PageOptions['MetaRevisit']; ?>"/>
    <meta name="robots" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>

    <title><?php echo $Reflector->GetReflectorName(); ?> Reflector Dashboard</title>
    <link rel="icon" href="./favicon.ico" type="image/vnd.microsoft.icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css?v=3" rel="stylesheet">
    <?php
    if ($PageOptions['PageRefreshActive']) {
        echo '
   <script>
      var PageRefresh;

      function ReloadPage() {';
        if (($_SERVER['REQUEST_METHOD'] === 'POST') || isset($_GET['do'])) {
          echo '
         document.location.href = "./index.php';
          if (isset($_GET['show'])) {
            echo '?show=' . $_GET['show'];
          }
          echo '";';
        } else {
          echo '
         $( "#mainbox" ).load( document.location.href + " #mainbox");
         ';
        }
        echo '
      }';

        if (!isset($_GET['show']) || (($_GET['show'] != 'liveircddb') && ($_GET['show'] != 'reflectors') && ($_GET['show'] != 'interlinks'))) {
            echo '
      PageRefresh = setInterval(ReloadPage, ' . $PageOptions['PageRefreshDelay'] . ');';
        }
        echo '

      function SuspendPageRefresh() {
        cancelInterval(PageRefresh);
      }
   </script>';
    }
    if (!isset($_GET['show'])) $_GET['show'] = "";
    ?>
</head>
<body>
<?php if (file_exists("./tracking.php")) {
    include_once("tracking.php");
} ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="img/xlx299logot.svg" alt="XLX299 Multiprotocol Gateway" class="d-inline-block align-top me-5" height="54" width="310"></a>
	<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-expanded="false" aria-controls="navbarSupportedContent" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
		<li class="nav-item">
                    <a class="nav-link <?php echo (($_GET['show'] == "users") || ($_GET['show'] == "")) ? ' active' : ''; ?>"
                            href="./index.php">Users</a></li>
		<li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['show'] == "repeaters") ? ' active' : ''; ?>"
                            href="./index.php?show=repeaters">Nodes (<?php echo $Reflector->NodeCount(); ?>)</a></li>
		<li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['show'] == "peers") ? ' active' : ''; ?>" href="./index.php?show=peers">Peers
                        (<?php echo $Reflector->PeerCount(); ?>)</a></li>
		<li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['show'] == "reflectors") ? 'active' : ''; ?>"
                            href="./index.php?show=reflectors">Reflectorlist</a></li>
		<!--<li class="nav-item">
                    <a class="nav-link <?php echo ($_GET['show'] == "liveircddb") ? ' active"' : ''; ?>"
                            href="./index.php?show=liveircddb">D-Star live</a></li>-->
		<!--<li class="nav-item">
                    <a class="nav-link<?php echo ($_GET['show'] == "traffic") ? ' active' : ''; ?>"
                            href="./index.php?show=traffic">Traffic</a></li>-->
		<li class="nav-item">
                    <a class="nav-link<?php echo ($_GET['show'] == "modules") ? ' active' : ''; ?>"
                            href="./index.php?show=modules">Modules</a></li>
		<li class="nav-item">
                    <a class="nav-link<?php echo ($_GET['show'] == "info") ? ' active' : ''; ?>"
                            href="./index.php?show=info">Information</a></li>
		<li class="nav-item">
                    <a class="nav-link<?php echo ($_GET['show'] == "thanks") ? ' active' : ''; ?>"
                            href="./index.php?show=thanks">Thanks</a></li>
            </ul>
        </div>
    </div>
</nav>

<div id="mainbox" class="container-fluid mainbox">
    <div class="row">
        <div class="col main">

            <?php
            if ($CallingHome['Active']) {
                if (!is_readable($CallingHome['HashFile']) && (!is_writeable($CallingHome['HashFile']))) {
                    echo '
         <div class="error">
            your private hash in ' . $CallingHome['HashFile'] . ' could not be created, please check your config file and the permissions for the defined folder.
         </div>';
                }
            }

            // if an motd file exists and user hasn't dismissed it, display
            $motdts = filemtime(MOTDFILE);
            if (!$cookieset
                && ($motdts !== FALSE)
                && (!isset($_COOKIE[MOTDCOOKIE]) || ($_COOKIE[MOTDCOOKIE] < $motdts))) {
                if (($motd = file_get_contents(MOTDFILE)) !== FALSE && strlen(trim($motd)) > 0) {
                    echo '
                <div id="motd" class="col-md-9 alert alert-info alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>';
                    echo $motd;
                    echo '</div>';
                }
            }
            switch ($_GET['show']) {
                case 'users'      :
                    require_once("./pgs/users.php");
                    break;
                case 'repeaters'  :
                    require_once("./pgs/repeaters.php");
                    break;
                case 'liveircddb' :
                    require_once("./pgs/liveircddb.php");
                    break;
                case 'peers'      :
                    require_once("./pgs/peers.php");
                    break;
                case 'reflectors' :
                    require_once("./pgs/reflectors.php");
                    break;
		case 'traffic'    : 
			require_once("./pgs/traffic.php");
			 break;
		case 'info'    :
                        require_once("./pgs/info.php");
                         break;
		case 'thanks'    :
                        require_once("./pgs/thanks.php");
                         break;
		case 'modules'    :
                        require_once("./pgs/modules.php");
                         break;
                default           :
                    require_once("./pgs/users.php");

            }

            ?>

        </div>
    </div>
</div>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-sm">
        <span class="text-muted">Hosted by DVNZ Group
        <a href="mailto:<?php echo $PageOptions['ContactEmail']; ?>"><?php echo $PageOptions['ContactEmail']; ?></a>
<br/><?php echo $Reflector->GetReflectorName(); ?>&nbsp;v<?php echo $Reflector->GetVersion(); ?>&nbsp;&nbsp;&nbsp;Service Uptime: <span id="suptime"><?php echo FormatSeconds($Reflector->GetServiceUptime()); ?></span>
        </span>
            </div>
            <div class="col-sm">
                <a href="https://t.me/+H8KgiQPdncUyMDgz" target="_blank"><span class="material-icons">telegram</span>&nbsp;Join DVNZ on Telegram</a>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

<script>
    $("#motd").on("close.bs.alert", function() {
        clearTimeout(PageRefresh); // allow animation to run
    });
    $("#motd").on("closed.bs.alert", function() {
        var form = document.createElement('form');
        var element = document.createElement('input');
        element.name = "motd";
        element.value = "1";

        form.method = 'POST';
<?php
        $formaction = '/index.php';
        if (isset($_GET['show'])) {
            $formaction .= '?show=' . $_GET['show'];
        }
        echo "form.action = '$formaction';";
?>
        form.style.display = 'hidden';

        form.appendChild(element);
        document.body.appendChild(form)
        form.submit();
    });
</script>
</body>
</html>
