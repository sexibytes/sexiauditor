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
