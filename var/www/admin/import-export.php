<?php
require("session.php");
$title = "Import Export";
$additionalStylesheet = array('css/pong.css');
require("header.php");
require("helper.php");
?>
  <div class="container">
    <h1><i class="glyphicon glyphicon-transfer"></i> <?php echo $title; ?></h1>
    <p></p>
    <div class="alert alert-warning"><p><i class="glyphicon glyphicon-exclamation-sign"></i> Here you will find the import-export feature that will ease migration between appliances.</p><p>This feature is not yet available, but be assure we're working hard to bring it to you as soon as possible. In the mean time, enjoy this awesome HTML5 Pong game!</p></div>
    <div id="gamediv">
    	<div id="titleScreen">
    		<h1>Pong!</h1>
    		<p>This game is based on the HTML5 elements <b>canvas</b>.</p>
    		<p>To play this game you need a modern web browser with support for HTML5.</p>
    		<p class="vcard">Made by <a href="http://daverix.net/" class="url fn" target="_top" rel="me">David Laurell</a></p>
    		<button id="playButton">Play!</button>
    	</div>
    	<div id="playScreen">
    		<canvas width="640" height="360" id="gameCanvas">
    			<p>Your browser <b>does not</b> support HTML5!</p>
    			<p>Download <a href="http://firefox.com">Firefox3.6</a> for the full experience or another with good HTML5 support. The game is tested in Firefox 3.0+, Chromium 4+, Chrome 4 beta, Opera and Internet Explorer 8. To get the audio to work you are required to use Firefox for the moment.</p>
    			<p>Visit the <a href="http://daverix.net/projects/pong/">project page</a> for more info.</p>
    		</canvas>
    		<div id="computerScore">0</div>
    		<div id="playerScore">0</div>
    		<div class="ingamebuttons">
    			<button id="pauseButton">Pause</button>
    		</div>
    		<div id="pauseText">Paused</div>
    	</div>
    </div>
  </div>
  <script type="text/javascript" src="js/pong.js"></script>

<?php require("footer.php"); ?>
