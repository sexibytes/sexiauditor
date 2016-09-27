<?php
require("session.php");
$title = "About SexiAuditor";
$additionalStylesheet = array('css/whhg.css');
require("header.php");
?>
  <div class="container">
    <section id="apropos">
      <h1><i class="glyphicon glyphicon-question-sign"></i> What is SexiAuditor?</h1>
      <p>SexiAuditor can be summarize as a automatic web morning check on steroids. It basically handle all chekcs, aggregate through fancy web pages, and send morning check reports for VMware admin</p>
      <h1><i class="glyphicon glyphicon-book"></i> A little piece of history</h1>
      <p>For the history class, Sexiauditor was born in our twisted lazy minds, as we always wanted to have accurate morning checks to know what's going on our managed platform, but with a minimum work (we told you we were lazy). We used to deploy vCheck everywhere (as we also were active in its development), but there was some lacks. So after having released SexiLog and SexiGraf, we asked ourselves if we cannot do something more sutuable for us (and hopefully for lot of people too) :)</p>
      <h1><i class="glyphicon glyphicon-user"></i> Who are we?</h1>
      <p>The team remain the same as development of SexiLog and SexiGraf, here we are:</p>
      <img src="img/vmdude.png" class="img-responsive" alt="vmdude">
      <img src="img/hypervisor.png" class="img-responsive" alt="hypervisor">
      <h1><i class="glyphicon glyphicon-envelope"></i> Contact information</h1>
      <p>If you want to contact us or to discover more about our work, here are some ways to keep in touch!<br/>Feel free to contact us, we'll certainly answer back (we love any feeback):</p>
      <p><i class="icon-emailalt"></i> <a href="mailto:check@sexiauditor.fr">check@sexiauditor.fr</a></p>
      <p><i class="icon-websitealt" style="color:#FF4536;"></i> <a href="https://www.sexiauditor.fr">https://www.sexiauditor.fr</a></p>
      <p><i class="icon-twitter" style="color:#4099FF;"></i> <a href="">@sexiauditor_fr</a></p>
      <h1><i class="glyphicon glyphicon-list-alt"></i> Library used</h1>
      <p>We couldn't have created SexiAuditor without the help of many people and their work, you can find below a list of all library and tools used during our development:</p>
      <ul>
        <li><strong>jQuery</strong> - Library used for frontend, <a href="https://jquery.com/">https://jquery.com/</a></li>
        <li><strong>Bootstrap</strong> - Framework used for frontend, <a href="http://getbootstrap.com">http://getbootstrap.com</a></li>
        <li><strong>typeahead</strong> - Library used for typeahead search, <a href="https://twitter.github.io/typeahead.js/">https://twitter.github.io/typeahead.js/</a></li>
        <li><strong>PHPTail</strong> - Used for displaying logs through web page, <a href="https://github.com/taktos/php-tail">https://github.com/taktos/php-tail</a></li>
        <li><strong>MysqliDb</strong> - Used for database access, <a href="http://github.com/joshcam/PHP-MySQLi-Database-Class">http://github.com/joshcam/PHP-MySQLi-Database-Class</a></li>
        <li><strong>Datatables</strong> - jQuery extension for tables handling, <a href="https://datatables.net/">https://datatables.net/</a></li>
        <li><strong>eCharts</strong> - Generation of graph, <a href="https://ecomfe.github.io/echarts/index-en.html">https://ecomfe.github.io/echarts/index-en.html</a></li>
        <li><strong>Highcharts</strong> - Generation of graph, <a href="http://www.highcharts.com/">http://www.highcharts.com/</a></li>
        <li><strong>Isotope</strong> - vOpenData layout handling, <a href="http://isotope.metafizzy.co/">http://isotope.metafizzy.co/</a></li>
        <li><strong>Moment</strong> - Date and time manipulation, <a href="http://momentjs.com/">http://momentjs.com/</a></li>
        <li><strong>vOpenData</strong> - Project for open statistics on VMware objects, <a href="https://github.com/vopendata">https://github.com/vopendata</a></li>
        <li><strong>HTML5 Pong</strong> - Used for coming soon pages, <a href="http://daverix.net/projects/pong/">http://daverix.net/projects/pong/</a></li>
      </ul>
    </section>
  </div>
<?php require("footer.php"); ?>
