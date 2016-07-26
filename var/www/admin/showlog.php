<?php require("session.php"); ?>
<?php if (!isset($_SESSION['role']) || $_SESSION['role'] != '1') { header('Location: logout.php'); } ?>

<?php
class PHPTail {
    /* Source: https://github.com/taktos/php-tail */

    /**
     * Location of the log file we're tailing
     * @var string
     */
    private $log = "";
    /**
     * The time between AJAX requests to the server.
     *
     * Setting this value too high with an extremly fast-filling log will cause your PHP application to hang.
     * @var integer
     */
    private $updateTime;
    /**
     * This variable holds the maximum amount of bytes this application can load into memory (in bytes).
     * @var string
     */
    private $maxSizeToLoad;
    /**
     *
     * PHPTail constructor
     * @param string $log the location of the log file
     * @param integer $defaultUpdateTime The time between AJAX requests to the server.
     * @param integer $maxSizeToLoad This variable holds the maximum amount of bytes this application can load into memory (in bytes). Default is 2 Megabyte = 2097152 byte
     */
    public function __construct($log, $defaultUpdateTime = 2000, $maxSizeToLoad = 2097152) {
        $this->log = is_array($log) ? $log : array($log);
        $this->updateTime = $defaultUpdateTime;
        $this->maxSizeToLoad = $maxSizeToLoad;
    }
    /**
     * This function is in charge of retrieving the latest lines from the log file
     * @param string $lastFetchedSize The size of the file when we lasted tailed it.
     * @param string $grepKeyword The grep keyword. This will only return rows that contain this word
     * @return Returns the JSON representation of the latest file size and appended lines.
     */
    public function getNewLines($file, $lastFetchedSize, $grepKeyword, $invert) {

        /**
         * Clear the stat cache to get the latest results
         */
        clearstatcache();
        /**
         * Define how much we should load from the log file
         * @var
         */
        if(empty($file)) {
            $file = key(array_slice($this->log, 0, 1, true));
        }
        $fsize = filesize($this->log[$file]);
        $maxLength = ($fsize - $lastFetchedSize);
        /**
         * Verify that we don't load more data then allowed.
         */
        if($maxLength > $this->maxSizeToLoad) {
            $maxLength = ($this->maxSizeToLoad / 2);
        }
        /**
         * Actually load the data
         */
        $data = array();
        if($maxLength > 0) {

            $fp = fopen($this->log[$file], 'r');
            fseek($fp, -$maxLength , SEEK_END);
            $data = explode("\n", fread($fp, $maxLength));

        }
        /**
         * Run the grep function to return only the lines we're interested in.
         */
        if($invert == 0) {
            $data = preg_grep("/$grepKeyword/",$data);
        }
        else {
            $data = preg_grep("/$grepKeyword/",$data, PREG_GREP_INVERT);
        }
        /**
         * If the last entry in the array is an empty string lets remove it.
         */
        if(end($data) == "") {
            array_pop($data);
        }
        return json_encode(array("size" => $fsize, "file" => $this->log[$file], "data" => $data));
    }
    /**
     * This function will print out the required HTML/CSS/JS
     */
    public function generateGUI() {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log Viewer</title>

<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/sexiauditor.css">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<script src="js/jquery.min.js"></script>

<script type="text/javascript">
    /* <![CDATA[ */
    //Last know size of the file
    lastSize = 0;
    //Grep keyword
    grep = "";
    //Should the Grep be inverted?
    invert = 0;
    //Last known document height
    documentHeight = 0;
    //Last known scroll position
    scrollPosition = 0;
    //Should we scroll to the bottom?
    scroll = true;
    lastFile = window.location.hash != "" ? window.location.hash.substr(1) : "";
    console.log(lastFile);
    $(document).ready(function() {
        $(".file").click(function(e) {
            $("#results").text("");
            lastSize = 0;
            lastFile = $(e.target).text();
			console.log(e);
        });

        //Set up an interval for updating the log. Change updateTime in the PHPTail contstructor to change this
        // setInterval("updateLog()", <?php echo $this->updateTime; ?>);
        setInterval("updateLog()", 2000);
        //Some window scroll event to keep the menu at the top
        $(window).scroll(function(e) {
            if ($(window).scrollTop() > 0) {
                $('.float').css({
                    position : 'fixed',
                    top : '0',
                    left : 'auto'
                });
            } else {
                $('.float').css({
                    position : 'static'
                });
            }
        });
        //If window is resized should we scroll to the bottom?
        $(window).resize(function() {
            if (scroll) {
                scrollToBottom();
            }
        });
        //Handle if the window should be scrolled down or not
        $(window).scroll(function() {
            documentHeight = $(document).height();
            scrollPosition = $(window).height() + $(window).scrollTop();
            if (documentHeight <= scrollPosition) {
                scroll = true;
            } else {
                scroll = false;
            }
        });
        scrollToBottom();
    });
    //This function scrolls to the bottom
    function scrollToBottom() {
        $("html, body").animate({scrollTop: $(document).height()}, "fast");
    }
    //This function queries the server for updates.
    function updateLog() {
        $.getJSON('?ajax=1&file=' + lastFile + '&lastsize=' + lastSize + '&grep=' + grep + '&invert=' + invert, function(data) {
            lastSize = data.size;
            $("#current").text(data.file);
            $.each(data.data, function(key, value) {
                $("#results").append('' + value + '<br/>');
            });
            if (scroll) {
                scrollToBottom();
            }
        });
    }
    /* ]]> */
</script>
</head>
<body>
	<div id="wrapper">
	<nav class="navbar navbar-default navbar-fixed-bottom navbar-danger">
		<div class="">Beware of these awesome admin rights, with power comes great responsibility-ish !!!</div>
	</nav>
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
		<p class="navbar-text navbar-left" style="padding-top: 0px;" id="current"></p>
    <div class="navbar-brand">SexiAuditor</div>
      <ul class="nav navbar-top-links navbar-right">
        <li>
  				<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true">
  					<i class="glyphicon glyphicon-file"></i>  <i class="glyphicon glyphicon-triangle-bottom" style="font-size: 0.8em;"></i>
  				</a>
  				<ul class="dropdown-menu">
  <?php foreach ($this->log as $title => $f): ?>
  					<li><a class="file" href="#<?php echo $title;?>"><?php echo $title;?></a></li>
  <?php endforeach; ?>
  				</ul>
  			</li>
  			<li><i class="glyphicon glyphicon-option-vertical" style="color: #BBB;"></i></li>
          <li><a href="passwordupdate.php">Welcome <?php echo (isset($_SESSION['displayname']) ? $_SESSION['displayname'] : $_SESSION['username']) . ((isset($_SESSION['role']) && $_SESSION['role'] == '1') ? ' <i class="glyphicon glyphicon-star"></i>' : ''); ?></a></li>
          <li><i class="glyphicon glyphicon-option-vertical" style="color: #BBB;"></i></li>
          <li class="dropdown">
              <a id="dLabel" class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                  <i class="glyphicon glyphicon-tasks"></i>  <i class="glyphicon glyphicon-triangle-bottom" style="font-size: 0.8em;"></i>
              </a>
  <?php
  if (isset($_SESSION['role']) && $_SESSION['role'] == '1') {
  	$nbColumn = " columns-3";
  	$widthColumn = "col-sm-4";
  } else {
  	$nbColumn = " columns-2";
  	$widthColumn = "col-sm-6";
  }
  ?>
  					<ul class="dropdown-menu multi-column<?php echo $nbColumn; ?>">
  						<div class="row">
  							<div class="<?php echo $widthColumn; ?>">
  								<ul class="multi-column-dropdown">
  									<li><a href="index.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> User Dashboard</a></li>
  									<li class="divider"></li>
  									<li><a href="check-vcenter.php"><img src="images/vc-vcenter.gif" class="glyphicon-custom" /> vCenter Checks</a></li>
  									<li><a href="check-cluster.php"><img src="images/vc-cluster.gif" class="glyphicon-custom" /> Cluster Checks</a></li>
  									<li><a href="check-host.php"><img src="images/vc-host.gif" class="glyphicon-custom" /> Host Checks</a></li>
  									<li><a href="check-datastore.php"><img src="images/vc-datastore.gif" class="glyphicon-custom" /> Datastore Checks</a></li>
  									<li><a href="check-network.php"><img src="images/vc-network.gif" class="glyphicon-custom" /> Network Checks</a></li>
  									<li><a href="check-vm.php"><img src="images/vc-vm.gif" class="glyphicon-custom" /> VM Checks</a></li>
  									<li><a href="inv.php"><i class="glyphicon glyphicon-list-alt glyphicon-custom"></i> Global Inventory</a></li>
  									<!-- <li class="divider"></li> -->
  								</ul>
  							</div>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] == '1'): ?>
  							<div class="<?php echo $widthColumn; ?>">
  								<ul class="multi-column-dropdown">
  									<li><a href="admin.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> Admin Dashboard</a></li>
  									<li class="divider"></li>
  									<li><a href="credstore.php"><i class="glyphicon glyphicon-briefcase glyphicon-custom"></i> Credential Store</a></li>
  									<li><a href="updater.php"><i class="glyphicon glyphicon-hdd glyphicon-custom"></i> Package Updater</a></li>
  									<li><a href="moduleselector.php"><i class="glyphicon glyphicon-check glyphicon-custom"></i> Module Selector</a></li>
  									<!-- <li><a href="refresh-inventory.php"><i class="glyphicon glyphicon-th-list glyphicon-custom"></i> Refresh Inventory</a></li> -->
  									<li><a href="showlog.php"><i class="glyphicon glyphicon-search glyphicon-custom"></i> Log Viewer</a></li>
  									<li><a href="timetobuild.php"><i class="glyphicon glyphicon-time glyphicon-custom"></i> Time To Build</a></li>
  									<!-- <li class="divider"></li> -->
  								</ul>
  							</div>
  <?php endif; ?>
  							<div class="<?php echo $widthColumn; ?>">
  								<ul class="multi-column-dropdown">
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] == '1'): ?>
  									<li><a href="config.php"><i class="glyphicon glyphicon-pencil glyphicon-custom"></i> Module Settings</a></li>
  									<li><a href="users.php"><i class="glyphicon glyphicon-user glyphicon-custom"></i> Users Management</a></li>
  <?php endif; ?>
  									<li><a href="onetime.php"><i class="glyphicon glyphicon-book glyphicon-custom"></i> One Time Report</a></li>
  									<li><a href="pdfreports.php"><i class="glyphicon glyphicon-print glyphicon-custom"></i> PDF Reports</a></li>
  									<li><a href="status.php"><i class="glyphicon glyphicon-screenshot glyphicon-custom"></i> Scheduler Status</a></li>
  									<li class="divider"></li>
  									<li><a href="passwordupdate.php"><i class="glyphicon glyphicon-asterisk glyphicon-custom"></i> Update Password</a></li>
  									<li><a href="logout.php"><i class="glyphicon glyphicon-log-out glyphicon-custom"></i> Logout</a></li>
  								</ul>
  							</div>
  						</div>
  					</ul>
          </li>
      </ul>
  </nav>
	</div>
	<div class="contents">
		<div id="results" class="results"></div>
	</div>
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
        <?php
    }
}

/**
 * Initilize a new instance of PHPTail
 * @var PHPTail
 */

$tail = new PHPTail(array(
    "vCron Scheduler" => "/var/log/sexiauditor/vcronScheduler.log"
));

/**
 * We're getting an AJAX call
 */
if(isset($_GET['ajax']))  {
    echo $tail->getNewLines($_GET['file'], $_GET['lastsize'], $_GET['grep'], $_GET['invert']);
    die();
}

/**
 * Regular GET/POST call, print out the GUI
 */
$tail->generateGUI();
?>
