<?php require("session.php"); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['startProcess'])) {
        $argumentForce = '';
        if(isset($_POST['forceScheduler']) && $_POST['forceScheduler'] == 'enable') {
            $argumentForce = '--force';
        }
        exec("sudo /opt/vcron/scheduler.pl $argumentForce > /dev/null 2>&1 &");
        # run scheduler
        header("Location: status.php");
    }
}
$title = "One time Report, aka manual override";
require("header.php");
require("helper.php");
?>
<style>
.btn-danger, .btn-success {
    color: #333;
    background-color: #fff;
    border-color: #ccc;
}
.modulePath {
    font-style: italic;
    font-size: small;
}
</style>

    <div class="container"><br/>
        <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title">One Time Report</h3></div>
            <div class="panel-body"><ul>
                <li>This page will let you run the scheduler manually. It can be useful to generate one time up to date report.</li>
                <li>You will not be able to manually run the scheduler if it's already running (to avoid multiple execution).</li>
                <li>Beware as manually running scheduler with <code>--force</code> switch will bypass all schedule and run all modules, it can be time sensitive!</li>
                <li>After starting the scheduler manual run, you will be automatically forwarded to its status page to display process information.</li>
                <li>Please refer to the <a href="http://www.sexiauditor.fr/">project website</a> and documentation for more information.</li>
            </ul></div>
        </div>

        <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <div class="form-group">
            <label for="forceScheduler" class="col-sm-6 control-label">Force scheduler to bypass all schedule and run all modules</label>
            <div class="col-sm-4">
            <div class="btn-group" data-toggle="buttons">
                <button name="radio" class="btn btn-danger active"><input type="radio" name="forceScheduler" value="disable">No</button>
                <button name="radio" class="btn btn-success"><input type="radio" name="forceScheduler" value="enable">Yes</button>
            </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-6 col-sm-6">
              <button type="submit" class="btn btn-default" name="startProcess">Start Scheduler Process</button>
            </div>
        </div>
        </form>

    </div>
<?php require("footer.php"); ?>
