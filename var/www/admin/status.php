<?php require("session.php"); ?>
<?php
$title = "Scheduler Status";
$additionalStylesheet = array('css/jquery.dataTables.min.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/echarts-all-english-v2.js' );
require("header.php");
require("helper.php");

putenv('COLUMNS=1000');
$psList = shell_exec("ps auxwww | awk 'NR==1 || /scheduler.pl/' | egrep -v 'awk|\/bin\/sh|sudo'");
$processes = explode("\n", trim($psList));
$heads = preg_split('/\s+/', strToLower(trim(array_shift($processes))));
$count = count($heads);
$nbProcess = count($processes);
$procs = array();
foreach($processes as $i => $process){
    $parts = preg_split('/\s+/', trim($process), $count);
    foreach ($heads as $j => $head) {
        $procs[$i][$head] = str_replace('"', '\"', $parts[$j]);
    }
}
?>
    <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
	<div class="row">
		<div class="col-lg-12 alert alert-info" style="margin-top: 20px; text-align: center;">
			<h1 style="margin-top: 10px;">Scheduler Status<small> at <?php echo (new DateTime)->format('l jS F Y, H:i:s') . "(UTC)"; ?></small></h1>
		</div>
	</div>
<?php if($nbProcess == 0): ?>
    <div class="alert alert-success row" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Info:</span> Scheduler is not running at the moment, feel free to visit <a href="index.php">check pages</a> to display information about your platform.</div>
<?php else: ?>
<?php if($nbProcess > 1) : ?>
    <div class="alert alert-danger row" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> There are multiple scheduler processes running, please check...</div>
<?php endif; ?>
       <div class="col-lg-12">
        <table id="procInfo" class="table table-hover">
            <thead><tr>
                <th>user</th>
                <th>pid</th>
                <th>%cpu</th>
                <th>%mem</th>
                <th>vsz</th>
                <th>rss</th>
                <th>start</th>
                <th>time</th>
                <th>command</th>
            </thead>
            <tbody>
<?php
    foreach ($procs as $processInfo) {
        echo '            <tr><td>' . $processInfo['user'] . '</td><td>' . $processInfo['pid'] . '</td><td>' . $processInfo['%cpu'] . '</td><td>' . $processInfo['%mem'] . '</td><td>' . $processInfo['vsz'] . '</td><td>' . $processInfo['rss'] . '</td><td>' . $processInfo['start'] . '</td><td>' . $processInfo['time'] . '</td><td>' . $processInfo['command'] . '</td></tr>';
    }
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $('#procInfo').DataTable( {
                "paging": false,
                "ordering": false,
                "info": false,
                "searching": false
            } );
         } );
		setTimeout(function(){ window.location.reload(1); }, 5000);
        </script>
<?php endif; ?>
    </div>

<?php require("footer.php"); ?>
