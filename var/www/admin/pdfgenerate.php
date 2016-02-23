<?php require("session.php"); ?>
<?php
# TODO clean tmep files
# TODO check for XML readable before going on
# TODO display module on if selected
# TODO use helper start path for XML

$xmlSettingsFile = "/var/www/admin/conf/modulesettings.xml";
if (is_readable($xmlSettingsFile)) {
    $xmlSettings = simplexml_load_file($xmlSettingsFile);
    # hash table initialization with settings XML file
    $h_modulesettings = array();
    foreach ($xmlSettings->xpath('/settings/setting') as $setting) { $h_modulesettings[(string) $setting->id] = (string) $setting->value; }
} else {
    require("header.php");
    echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlSettingsFile . ' is not existant or not readable</div>';
    require("footer.php");
    exit();
}

# XML files loading
$xmlClusterFile = "/opt/vcron/data/latest/clusters-global.xml";
$xmlCluster = simplexml_load_file($xmlClusterFile);
$xmlHostFile = "/opt/vcron/data/latest/hosts-global.xml";
$xmlHost = new DOMDocument;
$xmlHost->load($xmlHostFile);
$xmlHost = new DOMXPath($xmlHost);
$xmlHostSimple = simplexml_load_file($xmlHostFile);
$xmlVMFile = "/opt/vcron/data/latest/vms-global.xml";
$xmlVM = new DOMDocument;
$xmlVM->load($xmlVMFile);
$xmlVM = new DOMXPath($xmlVM);
$xmlLicenseFile = "/opt/vcron/data/latest/licenses-global.xml";
$xmlLicenseSimple = simplexml_load_file($xmlLicenseFile);
$xmlLicense = new DOMDocument;
$xmlLicense->load($xmlLicenseFile);
$xpathLicense = new DOMXPath($xmlLicense);
$a_Session = array();
$a_SessionAge = array();
$a_SessionUser = array();
$xmlSessionFile = "/opt/vcron/data/latest/sessions-global.xml";
if (is_readable($xmlSessionFile)) {
	$xmlSession = simplexml_load_file($xmlSessionFile);
	$xpathFullSession = $xmlSession->xpath("/sessions/session");
    foreach ($xpathFullSession as $session) {
		$a_Session[] = array('username' => explode("\\", (string) $session->userName)[1], 'age' => DateTime::createFromFormat('Y-m-d', substr($session->lastActiveTime, 0, 10))->diff(new DateTime("now"))->format('%a'));
	}
	foreach ($a_Session as $key => $row) { $age[$key] = $row['age']; }
	array_multisort($age, SORT_DESC, $a_Session);
	for ($i = 0; $i < 10; $i++) {
		$a_SessionAge[] = $a_Session[$i]['age'];
		$a_SessionUser[] = $a_Session[$i]['username'];
	}
}

$xmlDatastoreFile = "/opt/vcron/data/latest/datastores-global.xml";
if (is_readable($xmlDatastoreFile)) {
	$xmlDatastore = simplexml_load_file($xmlDatastoreFile);
}


$module = "Global Checks";
require_once('tcpdf/tcpdf.php');
require('helper.php');

class SEXIPDF extends TCPDF {
	function __construct() {
		# construct overload to include pChart library
        parent::__construct();
		include("pchart/class/pData.class.php");
		include("pchart/class/pDraw.class.php");
		include("pchart/class/pImage.class.php");
		include("pchart/class/pPie.class.php");
    }

    /** Overwrite Header() method */
    public function Header() {
        global $module;
        if ($this->page == 1) {
            $this->Image('images/corner.jpg', $x='170', $y='0', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
        } else {
            # Header on second page+
            $this->Image('images/header.jpg', $x='0', $y='0', $w=210, $h=25, $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->SetTextColor(255, 255, 255);
            $this->ln(12);
            $this->SetFont('raleway', 'B', 12);
            $this->Write(null, $module, null, false, 'R');
            $this->ln();
            $this->Write(null, " Your platform health in a single awesome report", null, false, 'R');
        }
    }

    public function Footer() {
        global $module;
        if ($this->page == 1) {
            $this->Image('images/logo-sexiauditor.jpg', $x='', $y='260', $w=0, $h='20', $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
        } else {
            # Footer on second page+
            $this->SetTextColor(55, 69, 54);
            $this->SetFont('raleway', '', 10);
            $this->Write(null, $module . ' /' . TCPDF::getAliasNumPage(), null, false, 'R');
        }
    }

	# Use for adding regular page to that report, display h1 like header if specified
	public function NewRegularPage($header=null, $title, array $descriptions) {
		$this->AddPage();
		$this->SetLeftMargin(32);
		$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html='<br/>',$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
		if (isset($header)) {
			$this->Bookmark($header, 0, 0, '', 'B', array(0,64,128));
			$this->SetFont('raleway', 'B', 18);
			$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html=$header,$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
			$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html='<br/>',$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
		}
		$this->SetFont('raleway', 'B', 12);
		$this->Bookmark($title, 1, 0, '', 'B', array(64,128,196));
		$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html=$title,$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
		$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html='<br/>',$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
		$this->SetFont('raleway', '', 10);
		foreach ($descriptions as $description) {
			$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html=$description,$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
			$this->writeHTMLCell($w=0,$h=0,$x='',$y='',$html='<br/>',$border=0,$ln=1,$fill=false,$reseth=true,$align='L',$autopadding=false);
		}
	}

	public function AddAboutPage() {
		$this->AddPage();
		$this->SetLeftMargin(32);
		$this->Ln();
		$this->Bookmark('About the Authors', 0, 0, '', 'B', array(0,64,128));
		$this->SetFont('raleway', '', 12);
		$this->WriteHTML($html='<h1>About the Authors</h1>This report was brought to you by SexiAuditor team:<br/><ul><li>Raphael SCHITZ</li><li>Frederic MARTIN</li></ul>Feel free to visit the website <a href="http://www.sexiauditor.fr">www.sexiauditor.fr</a>', $ln=true, $fill=false, $reseth=false, $cell=false, $align='');
	}

	public function AddFrontPage($module) {
		$this->AddPage();
		$this->SetTextColor(255, 69, 54);
		$this->SetFont('raleway', '', 32);
		$this->ln(30);
		$this->Write('', $module);
		$this->ln(20);
		$this->SetTextColor(55, 69, 54);
		$this->SetFont('raleway', '', 14);
		$this->Write('', 'Report generated on ' . gethostname());
		$this->Ln();
		$this->Write('', (new DateTime('now'))->format('l jS F Y'));
	}

	public function AddSexiTOC() {
		$this->addTOCPage();
		$this->Ln();
		$this->SetLeftMargin(32);
		$this->SetFont('raleway', 'B', 18);
		$this->Write('', 'Table Of Content');
		$this->Ln(14);
		$this->SetFont('raleway', '', 12);
		$this->addTOC(2, 'raleway', '.', 'Table Of Content', 'B', array(128,0,0));
		$this->endTOCPage();
	}

	public function AddPieChart($okValue, $koValue, $label, $okText, $koText) {
		$a_tmp = array();
		$points = array($okValue, $koValue);
		$a_tmp[] = "$okText (" . round(100 * $okValue / ($okValue + $koValue)) . "%)";
		$a_tmp[] = "$koText (" . round(100 * $koValue / ($okValue + $koValue)) . "%)";
		$tmpChart = new pData();
		$tmpChart->addPoints($points, $label);
		$tmpChart->addPoints($a_tmp, "Category");
		$tmpChart->setAbscissa("Category");
		$tmpPicture = new pImage(700,700,$tmpChart);
		$tmpPicture->setFontProperties(array("FontName"=>"pchart/fonts/raleway.ttf","FontSize"=>10));
		$PieChart = new pPie($tmpPicture,$tmpChart);
		$PieChart->setSliceColor(0,array("R"=>92,"G"=>184,"B"=>92));
		$PieChart->setSliceColor(1,array("R"=>217,"G"=>83,"B"=>79));
		$PieChart->draw2DPie(250,220,array("Radius"=>150,"DataGapAngle"=>10,"DataGapRadius"=>6,"Border"=>FALSE,"DrawLabels"=>FALSE));
		$PieChart->drawPieLegend(170,450,array("R"=>255,"G"=>255,"B"=>255));
		$tmpPicture->Render("pchart/tmp/$label.png");
		$this->Image("pchart/tmp/$label.png", $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi='', $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
	}

	public function AddHistoBar($xPoints, $xLabel, $xName, $yPoints, $yLabel) {
		$tmpChart = new pData();
		$tmpChart->addPoints($xPoints,$xLabel);
		$tmpChart->setAxisName(0,$xName);
		$tmpChart->addPoints($yPoints,$yLabel);
		$tmpChart->setSerieDescription($yLabel,$yLabel);
		$tmpChart->setAbscissa($yLabel);
		$tmpChart->setPalette($xLabel, array("R"=>35,"G"=>141,"B"=>156,"Alpha"=>100));
		$tmpPicture = new pImage(700,500,$tmpChart);
		$tmpPicture->Antialias = TRUE;
		$tmpPicture->setFontProperties(array("FontName"=>"pchart/fonts/raleway.ttf","FontSize"=>10));
		$tmpPicture->setGraphArea(60,40,550,200);
		$tmpPicture->drawScale( array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"LabelRotation"=>45,"Mode"=>SCALE_MODE_START0) );
		$tmpPicture->drawBarChart( );
		$tmpPicture->render("pchart/tmp/$xLabel.png");
		$this->Image("pchart/tmp/$xLabel.png", $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi='', $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
	}

	public function AddVerticalHistoBar($xPoints, $xLabel, $yPoints, $yLabel, $yPoints2, $yLabel2) {
		$tmpChart = new pData();
		$tmpChart->addPoints($xPoints,$xLabel);
		$tmpChart->addPoints($yPoints,$yLabel);
		$tmpChart->addPoints($yPoints2,$yLabel2);
		$tmpChart->setAbscissa($xLabel);
		$tmpChart->setPalette($yLabel2, array("R"=>92,"G"=>184,"B"=>92,"Alpha"=>100));
		$tmpChart->setPalette($yLabel, array("R"=>217,"G"=>83,"B"=>79,"Alpha"=>100));
		$tmpPicture = new pImage(700,600,$tmpChart);
		$tmpPicture->Antialias = TRUE;
		$tmpPicture->setFontProperties(array("FontName"=>"pchart/fonts/calibri.ttf","FontSize"=>10));
		$tmpPicture->setGraphArea(140,40,550,500);
		$tmpPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10, "Pos"=>SCALE_POS_TOPBOTTOM, "Mode"=>SCALE_MODE_START0));
		$tmpPicture->drawBarChart( array("DisplayValues"=>TRUE) );
		$tmpPicture->drawLegend(120,520,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
		$tmpPicture->render("pchart/tmp/$xLabel.png");
		$this->Image("pchart/tmp/$xLabel.png", $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi='', $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
	}
} // end of class overload

$pdf = new SEXIPDF('P', 'mm', 'A4', true, 'UTF-8', false);
# adding new font
#echo TCPDF_FONTS::addTTFfont('fonts/Raleway-Bold.ttf', 'TrueTypeUnicode', '', 96);
$pdf->SetCreator($h_modulesettings['pdfAuthor']);
$pdf->SetAuthor($h_modulesettings['pdfAuthor']);
$pdf->SetTitle('Report ' . $module);
$pdf->SetSubject($module);
$pdf->SetKeywords('SexiAuditor PDF');
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);
$pdf->AddFrontPage($module);

##################
# VCENTER REPORT #
##################
$pdf->NewRegularPage('vCenter Check', 'Session Age', array('Please find below the 10 oldest idle sessions on your platform (based on all vCenter servers you had set-up on this appliance). As these are idle session, you should check and kill them if needed.', 'You can also use the module that will terminate idle sessions that goes beyond specified threshold to automate old session purge.'));
$pdf->AddHistoBar($xPoints=$a_SessionAge, $xLabel="Age", $xName="Days", $yPoints=$a_SessionUser, $yLabel="Username");

# vcLicenseReport
$pdf->NewRegularPage(null, 'License Report', array('Please find below the usage of your licenses. It will display, per edition, the number of licenses used next to how many you own.', 'You should check for license issues if used is greater than owned.'));

$a_license = array();
$a_licenseName = array();
$a_licenseUsed = array();
$a_licenseTotal = array();
$long_to_short = array(	"Enterprise" => "Ent",
						"Standard" => "Std",
						" Plus" => "+",
						"VMware " => "",
						"Server " => "",
						"Advanced" => "Adv");
foreach (array_diff(array_count_values(array_map("strval", $xmlLicenseSimple->xpath("/licenses/license/name"))), array("1")) as  $key => $value) {
	$a_license[] = array('name' => strtr($key, $long_to_short), 'used' => (int) $xpathLicense->evaluate('sum(/licenses/license[name="' . $key . '"]/used)'), 'total' => (int) $xpathLicense->evaluate('sum(/licenses/license[name="' . $key . '" and not(licenseKey = following-sibling::license/licenseKey)]/total)'));
}
foreach ($a_license as $key => $row) { $licenseName[$key]  = $row['name']; }
array_multisort($licenseName, SORT_ASC, $a_license);
foreach ($a_license as $license) {
	$a_licenseName[] = $license['name'];
	$a_licenseUsed[] = $license['used'];
	$a_licenseTotal[] = $license['total'];
}

$pdf->AddVerticalHistoBar($xPoints=$a_licenseName, $xLabel="licenseName", $yPoints=$a_licenseUsed, $yLabel="Used", $yPoints2=$a_licenseTotal, $yLabel2="Owned");

##################
# CLUSTER REPORT #
##################
$pdf->NewRegularPage('Cluster Check', 'Cluster with Configuration Issues', array('Please find below the report of cluster health, with configuration issues. If you have any cluster that is not healthy, you should check and fix the issue.'));

$nbCluster = count($xmlCluster->xpath("/clusters/cluster"));
$impactedCluster = count($xmlCluster->xpath("/clusters/cluster[lastconfigissue!='0']"));
$pdf->AddPieChart($okValue=($nbCluster - $impactedCluster), $koValue=$impactedCluster, $label="ClusterIssues", $okText="Healthy Clusters", $koText="Clusters with issues");
$pdf->NewRegularPage(null, 'Cluster Without HA', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$impactedCluster = count($xmlCluster->xpath("/clusters/cluster[dasenabled!='1']"));
$pdf->AddPieChart($okValue=($nbCluster - $impactedCluster), $koValue=$impactedCluster, $label="ClusterHA", $okText="Clusters with HA", $koText="Clusters without HA");
$pdf->NewRegularPage(null, 'Hosts Build Number Mismatch', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

$mismatchCluster = 0;
$matchCluster = 0;
foreach (array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
	foreach (array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
		if ($key_cluster == 'Standalone') { continue; }
		$mismatchMatches = array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/esxbuild"))), array("1"));
		(count($mismatchMatches) > 1) ? $mismatchCluster++ : $matchCluster++;
	}
}
$pdf->AddPieChart($okValue=$matchCluster, $koValue=$mismatchCluster, $label="ClusterBuild", $okText="Clusters with build match", $koText="Clusters with build mismatch");

$pdf->NewRegularPage(null, 'Cluster With Members LUN Path Count Mismatch', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

$mismatchCluster = 0;
$matchCluster = 0;
foreach (array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host/vcenter"))), array("1")) as  $key_vcenter => $value_vcenter) {
	foreach (array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host[vcenter='".$key_vcenter."']/cluster"))), array("1")) as  $key_cluster => $value_cluster) {
		if ($key_cluster == 'Standalone') { continue; }
		$mismatchMatches = array_diff(array_count_values(array_map("strval", $xmlHostSimple->xpath("/hosts/host[vcenter='".$key_vcenter."' and cluster='".$key_cluster."']/lunpathcount"))), array("1"));
		(count($mismatchMatches) > 1) ? $mismatchCluster++ : $matchCluster++;
	}
}
$pdf->AddPieChart($okValue=$matchCluster, $koValue=$mismatchCluster, $label="ClusterLUNMatch", $okText="Clusters with equal LUN Path Count", $koText="Clusters with LUN Path Count Mismatch");

$pCPU = (int) $xmlHost->evaluate('sum(/hosts/host/numcpucore)');
$vCPU = (int) $xmlVM->evaluate('sum(/vms/vm/numcpu)');
$pdf->NewRegularPage(null, 'Ratio Virtual vs Physical CPU', array('You will find here you ratio between virtual and physical CPU.', 'As a reminder, here are some examples of ratio per type of workload:', '<ul><li>Business/Mission critical workload that are sensitive to latency => <b>1:1</b></li><li>Computation-intensive workload => <b>2:1</b></li><li>Server production workload => <b>2:1 to 6:1</b></li><li>Mixed server environment => <b>4:1 to 10:1</b></li><li>Desktop environment => <b>1:1 to 18:1</b></li></ul>', '<div style="text-align: center; font-weight: bold; font-size: 4em;"><br><table style="border:1px;"><tr><td align="right">' . (($vCPU > 0 and $pCPU > 0) ? round($vCPU / $pCPU, 1) . ' </td><td>:</td><td align="left"> 1</td></tr></table>' : 'N/A') . '</div>'));

###############
# HOST REPORT #
###############
$pdf->NewRegularPage('Host Check', 'Host Check', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host Profile Compliance', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host LocalSwapDatastore Compliance', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host SSH/shell/lockdown check', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host NTP Check', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host DNS Check', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host Syslog Check', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host configuration issues', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host Alarms', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host Hardware Status', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host Reboot required', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host FQDN/hostname mismatch', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host in maintenance mode', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host ballooning/zip/swap', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Host PowerManagement Policy', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, '+ Host Bundle backup', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

####################
# DATASTORE REPORT #
####################
$pdf->NewRegularPage('Datastore Check', 'Datastore Space report', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

$a_sort = array();
$a_Datastore = array();
$a_DatastoreName = array();
$a_DatastoreUsage = array();
foreach ($xmlDatastore->xpath("/datastores/datastore") as $datastore) {
	$usage = round(100 - (($datastore->freespace / $datastore->size) * 100));
	$a_Datastore[] = array('name' => (string) $datastore->name, 'usage' => $usage);
}
foreach ($a_Datastore as $key => $row) { $a_sort[$key] = $row['usage']; }
array_multisort($a_sort, SORT_DESC, $a_Datastore);
for ($i = 0; $i < 15; $i++) {
	$a_DatastoreName[] = $a_Datastore[$i]['name'];
	$a_DatastoreUsage[] = $a_Datastore[$i]['usage'];
}

$pdf->AddHistoBar($xPoints=$a_DatastoreUsage, $xLabel="Usage", $xName="Usage (%)", $yPoints=$a_DatastoreName, $yLabel="DSName");

$pdf->NewRegularPage(null, 'Overallocation', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

$a_sort = array();
$a_Datastore = array();
$a_DatastoreName = array();
$a_DatastoreAllocation = array();
$a_DatastoreCapacity = array();
$a_DatastoreProvisioned = array();
foreach ($xmlDatastore->xpath("/datastores/datastore") as $datastore) {
	$allocation = (int) round((($datastore->size - $datastore->freespace + $datastore->uncommitted) * 100) / $datastore->size);
	$a_Datastore[] = array('name' => (string) $datastore->name, 'allocation' => $allocation, 'capacity' => (int) round($datastore->size / (1024*1024*1024)), 'provisioned' => (int) round((($datastore->size - $datastore->freespace + $datastore->uncommitted)) / (1024*1024*1024)));
}
foreach ($a_Datastore as $key => $row) { $a_sort[$key] = $row['allocation']; }
array_multisort($a_sort, SORT_DESC, $a_Datastore);
for ($i = 0; $i < 15; $i++) {
	$a_DatastoreName[] = (strlen($a_Datastore[$i]['name']) > 15) ? substr($a_Datastore[$i]['name'], 0, 15) . "..." : $a_Datastore[$i]['name'];
	$a_DatastoreCapacity[] = $a_Datastore[$i]['capacity'];
	$a_DatastoreProvisioned[] = $a_Datastore[$i]['provisioned'];
}

$pdf->AddVerticalHistoBar($xPoints=$a_DatastoreName, $xLabel="DSName", $yPoints=$a_DatastoreProvisioned, $yLabel="Provisioned (GB)", $yPoints2=$a_DatastoreCapacity, $yLabel2="Capacity (GB)");
$pdf->NewRegularPage(null, 'Datastore with SIOC disabled', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$nbDatastore = count($xmlDatastore->xpath("/datastores/datastore"));
$impactedDatastore = count($xmlDatastore->xpath("/datastores/datastore[iormConfiguration=0]"));
$pdf->AddPieChart($okValue=($nbDatastore - $impactedDatastore), $koValue=$impactedDatastore, $label="SIOC", $okText="Datastores with SIOC", $koText="Datastores without SIOC");
$pdf->NewRegularPage(null, 'Datastore in Maintenance Mode', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$impactedDatastore = count($xmlDatastore->xpath("/datastores/datastore[maintenanceMode!='normal']"));
$pdf->AddPieChart($okValue=($nbDatastore - $impactedDatastore), $koValue=$impactedDatastore, $label="maintenanceMode", $okText="Datastores not in maintenance", $koText="Datastores in maintenance");
$pdf->NewRegularPage(null, 'Datastore not Accessible', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$impactedDatastore = count($xmlDatastore->xpath("/datastores/datastore[accessible!=1]"));
$pdf->AddPieChart($okValue=($nbDatastore - $impactedDatastore), $koValue=$impactedDatastore, $label="DSnotAccessible", $okText="Datastores accessible", $koText="Datastores not accessible");

##################
# NETWORK REPORT #
##################
$pdf->NewRegularPage('Network Check', 'DVS ports free', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'DVS profil', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));

#############
# VM REPORT #
#############
$pdf->NewRegularPage('VM Check', 'VM Snapshots Age', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'DVS profil', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM Snapshots Age', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM phantom snapshot', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM consolidation needed', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM CPU / MEM reservation', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM CPU / MEM limit', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM cpu/ram hot-add', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM vmtools pivot table', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM vHardware pivot table', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'Balloon|Swap|Compression on memory', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM with vmdk in multiwriter mode', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM with vmdk in Non persistent mode', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM with scsi bus sharing', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM invalid or innaccessible', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM in inconsistent folder', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM with removable devices', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM Alarms', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM GuestId Mismatch', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM Powered Off', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM GuestId pivot table', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));
$pdf->NewRegularPage(null, 'VM misnamed', array('Please find below the report of cluster that does not have High Availability enable. This feature is available from almost all license and should be enable on all cluster.'));


# About Page
$pdf->AddAboutPage();
# add a new page for TOC
$pdf->AddSexiTOC();

// close and output PDF document
// $pdf->Output('Report-'.$module.'.pdf', 'I');
$pdf->Output('/var/www/admin/reports/Report-'.$module.'_'.date("YmdHi").'.pdf', 'F');
$pdf->Output('/var/www/admin/reports/Report-'.$module.'_'.date("YmdHi").'.pdf', 'I');

?>
