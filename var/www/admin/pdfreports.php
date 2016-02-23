<?php require("session.php"); ?>
<?php
$title = "PDF Reports";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/moment.js',
                            'js/datetime-moment.js',
                            'js/file-size.js');
require("header.php");
require("helper.php");
?>
  <div class="container">
    <h1><i class="glyphicon glyphicon-print"></i> <?php echo $title; ?></h1>
    <h2>One Time Generation</h2>
    <div class="col-lg-12">
      To generate a new global report and display it, please push <del>gently</del> right here:
      <a href="pdfgenerate.php" class="btn btn-primary" style="margin:10px;">Generate Report</a>
    </div>
    <h2>History</h2>
    <div class="col-lg-12">
    <table id="pdfReports" class="table table-hover">
      <thead><tr>
        <th class="col-sm-8 text-left">Name</th>
        <th class="col-sm-2 text-right">Size</th>
        <th class="col-sm-2 text-right">Modified</th>
      </thead>
      <tbody>
<?php
  $dir = "/var/www/admin/reports/";
	if ($handle = opendir($dir)) {
    $numReport = 1;
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != ".gitignore") {
        $lastModified = date('F d Y, H:i:s',filemtime($dir.$file));
        echo '            <tr><td class="text-left"><i class="glyphicon glyphicon-file"></i> <a href="#report'.$numReport.'" data-toggle="modal" rel="external">' . $file . '</td><td class="text-right">' . human_filesize(filesize($dir.$file)) . '</a></td><td class="text-right">' . $lastModified . '</td></tr>';
        echo '
          <div id="report'.$numReport.'" class="modal fade">
              <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">' . $file . '</h4>
                          <small class="text-muted" id="file-meta">' . human_filesize(filesize($dir.$file)) . '</small>
                      </div>
                      <div class="modal-body">
                        <div class="embed-responsive embed-responsive-4by3">
                          <iframe class="embed-responsive-item" src="reports/' . $file . '" type="application/pdf" scale="aspect" frameborder="0"></iframe>
                        </div>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          <a class="btn btn-primary fullview" data-button="Open" role="button" href="reports/' . $file . '">Open</a>
                      </div>
                  </div>
              </div>
          </div>';
          $numReport++;
		    }
		}
		closedir($handle);
    echo '    	</tbody></table>';
}
?>
            </tbody>
        </table>
        </div>
        <script type="text/javascript">
        $(document).ready( function () {
            $.fn.dataTable.moment( 'MMMM DD YYYY, HH:mm:ss' );
            $('#pdfReports').DataTable( {
                "search": {
                    "smart": false,
                    "regex": true
                },
				        "columnDefs": [{ type: 'file-size', targets: [ 1 ] }]
            } );
         } );
        </script>
</div>
<?php require("footer.php"); ?>
