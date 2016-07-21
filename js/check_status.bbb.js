function bbbCheckStatus(){
    var url = bbb_check_status_url;
    $.getJSON(url, function(data) {
        if (data.running == true) {
            location.href = location.href + '/meeting/attend';
        }
    });
};

$(document).ready(function(){
    bbbCheckStatus();
    setInterval("bbbCheckStatus();", 5000);
});
