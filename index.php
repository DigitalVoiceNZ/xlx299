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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $PageOptions['MetaDescription']; ?>"/>
    <meta name="keywords" content="<?php echo $PageOptions['MetaKeywords']; ?>"/>
    <meta name="author" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>
    <meta name="revisit" content="<?php echo $PageOptions['MetaRevisit']; ?>"/>
    <meta name="robots" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>

    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title><?php echo $Reflector->GetReflectorName(); ?> Reflector Dashboard</title>
    <link rel="icon" href="./favicon.ico" type="image/vnd.microsoft.icon">
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css?v=1" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
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
         document.location.reload();';
        }
        echo '
      }';

        if (!isset($_GET['show']) || (($_GET['show'] != 'liveircddb') && ($_GET['show'] != 'reflectors') && ($_GET['show'] != 'interlinks'))) {
            echo '
      PageRefresh = setTimeout(ReloadPage, ' . $PageOptions['PageRefreshDelay'] . ');';
        }
        echo '

      function SuspendPageRefresh() {
        clearTimeout(PageRefresh);
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
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid" id="navbar">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar"
                    aria-expanded="false" aria-controls="sidebar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

<!--            <span class="navbar-brand"><?php echo $Reflector->GetReflectorName(); ?> Multiprotocol Gateway</span> -->

<!-- Header Image xlx299logo4.png -->
	<img src="./img/xlx299logo.png">

        </div>
        <!-- <div id="logos"><img src="./img/modules/dstar.jpg"></div> -->
        </div>
    </div>
</nav>

<div id="mainbox" class="container-fluid mainbox">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar" id="sidebar">
            <ul class="nav nav-sidebar">
                <li<?php echo (($_GET['show'] == "users") || ($_GET['show'] == "")) ? ' class="active"' : ''; ?>><a
                            href="./index.php">Users / Modules</a></li>
                <li<?php echo ($_GET['show'] == "repeaters") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=repeaters">Repeaters / Nodes (<?php echo $Reflector->NodeCount(); ?>)</a></li>
                <li<?php echo ($_GET['show'] == "peers") ? ' class="active"' : ''; ?>><a href="./index.php?show=peers">Peers
                        (<?php echo $Reflector->PeerCount(); ?>)</a></li>
                <li<?php echo ($_GET['show'] == "reflectors") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=reflectors">Reflectorlist</a></li>
                <li<?php echo ($_GET['show'] == "liveircddb") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=liveircddb">D-Star live</a></li>
		<li<?php echo ($_GET['show'] == "traffic") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=traffic">Traffic</a></li>
		<li<?php echo ($_GET['show'] == "modules") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=modules">Modules</a></li>
		<li<?php echo ($_GET['show'] == "info") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=info">Information</a></li>
		<li<?php echo ($_GET['show'] == "thanks") ? ' class="active"' : ''; ?>><a
                            href="./index.php?show=thanks">Thanks</a></li>



               <!--
                <li><img class="logo" src="./img/dstar.png"><br/></li>
                <li><img class="logo" src="./img/bm.png"><br/></li>
                <li><img class="logo" src="./img/dmr.png"><br/></li> -->
<p><h4 style="color:red" align="center">Currently Connected</h4></p>
<p><h5 style="color:#4682B4" align="center"><?php echo $Reflector->PeerCount(); ?> Peers connected</h5></p>
<p><h5 style="color:#4682B4" align="center"><?php echo $Reflector->NodeCount(); ?> Nodes connected</h5></p>

<br/>
<!--                <p align="center"><img src="./img/xlx-group-logo.png"></p> -->

                <p align="center"><strong>Transcoding these modes</strong><br/></p>
                <li align="center"> <img class="logo" src="./img/dstar.png"><br/>
                <li align="center"><img class="logo" src="./img/bm.png"><br/></li>
                <li align="center"><img class="logo" src="./img/dmr.png"><br/></li>
                <li align="center"><img class="logo" src="./img/ysf.jpg"><br/></li>


            </ul>


<p>If you would like to Donate to help run</p>
<p>this reflector please press Donate</p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_donations" />
<input type="hidden" name="business" value="YPTMTJATP53TE" />
<input type="hidden" name="currency_code" value="NZD" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_NZ/i/scr/pixel.gif" width="1" height="1" />
</form>



        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

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
                if (($motd = file_get_contents(MOTDFILE)) !== FALSE) {
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

<footer class="footer">
    <div class="container">
                <!-- New Part Phil -->
               <p>
               <b>Proudly hosted by Phil Garside</b>

         <br/><a href="mailto:<?php echo $PageOptions['ContactEmail']; ?>"><?php echo $PageOptions['ContactEmail']; ?></a>
<!--         <br/>&nbsp;&nbsp;&nbsp;<?php echo $Reflector->GetReflectorName(); ?>&nbsp;v<?php echo $Reflector->GetVersion(); ?>&nbsp;-&nbsp;Dashboard v<?php echo $PageOptions['DashboardVersion']; ?> --> &nbsp;&nbsp;/&nbsp;&nbsp;Service uptime: <span id="suptime"><?php echo FormatSeconds($Reflector->GetServiceUptime()); ?>
         <br/><img class="logo" src="./img/dstar.png">
              <img class="logo" src="./img/bm.png">
              <img class="logo" src="./img/dmr.png"></span></div>

        </p>

    </div>
</footer>

<!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="js/ie10-viewport-bug-workaround.js"></script>
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
