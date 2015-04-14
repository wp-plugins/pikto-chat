<style type="text/css">
    #piktochatbox {
        display: none;
        z-index: 10001;
        width: 300px;
        height: 415px;
        position: fixed;
        bottom: 0;
        right: 50px;
        border-top-left-radius: 0.7em;
        border-top-right-radius: 0.7em;
    }
    #pikto-chat-header {
        position:relative;
        height: 40px;
        border-top-left-radius: 0.7em;
        border-top-right-radius: 0.7em;
        background-color: #000;
        color: #fff;
    }
    #pikto-chat-header span {
        position:relative;
        top:10px;
        left:10px;
        font-size:15px;
        font-weight:bold;
    }
    #pikto-chat-header img {
        float: right;
        width: 15px;
        margin-top: 13px;
        margin-right: 15px;
    }
    #piktotext {
        height: 337px;
        width: 300px;
        font-family: sans-serif;
        font-size:12px;
        padding-left: 6px;
        padding-top: 5px;
        word-wrap: break-word;
        overflow-y: scroll;
    }
    #piktochatbox input[type="text"] {
        width: 300px;
        height:38px;
        padding-left: 10px;
        padding-right: 10px;
        font-family: sans-serif;
        font-size: 13px;
        font-weight: 500;
    }
    .pikto-color-1 {
        background-color: #000;       /* Background Color : Black */
        color: #fff;                  /* Text Color       : White */
    }
    .pikto-color-2 {
        background-color: #fff;       /* Background Color : White */
        color: #000;                  /* Text Color       : Black */
        border-top  : 1px solid #000; /* Top Border Color : Black */
    }
    .pikto-color-3 {
        background-color: #003366;    /* Background Color : Blue  */
        color: #99cc99;               /* Text Color       : Green */
    }
    .pikto-color-4 {
        background-color: #99cc99;    /* Background Color : Green  */
        color: #003366;               /* Text Color       : Blue   */
    }
</style>

<div id="piktochatbox">
    <div id="pikto-chat-header" align="center">
        <?php $userdata = get_userdata(get_current_user_id()); ?>
        <span id="username"><?php echo $userdata->display_name; ?></span>
        <a href=javascript:; style="color:#fff;position:absolute;right:20px;bottom:5px;" onclick="document.getElementById('piktochatbox').style.display = (document.getElementById('piktochatbox').style.display === 'none') ? 'block' : 'none';">x</a>
    </div>
    <div id="piktotext" class="<?php echo get_option( 'widget_piktochat' )[2]['color']; ?>"></div>
    <div><input type="text" placeholder="<?php _e( 'Write a new message...', 'piktochat' ); ?>" name="pikto_fld_msg" id="pikto_fld_msg" autocomplete="off" /></div>    
</div>

<script>
    var prevCount = 0;
    var interval = setInterval(function () {
        var xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

        xmlHttp.onreadystatechange = function () {
            var count = 0;
            if (xmlHttp.readyState === 4)
            {
                var result = JSON.parse(xmlHttp.responseText);

                document.getElementById("piktotext").innerHTML = "";

                for (var i in result) {
                    document.getElementById("piktotext").innerHTML += "<div style='margin-bottom:5px;'><span style='font-size:14px;'><b>" + result[i].sender + "</span> (" + result[i].senddate + ")</b><br /><span>" + result[i].message + "</span></div>";
                    count++;
                }

                if( count > prevCount ) {
                    document.getElementById("piktotext").scrollTop = document.getElementById("piktotext").scrollHeight;
                    prevCount = count;
                }
            }
        };

        xmlHttp.open(
                "POST",
                "<?php echo admin_url('admin-ajax.php'); ?>",
                true
                );
        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlHttp.send("action=piktochat_read_msg");

    }, 1000);

    document.getElementById("pikto_fld_msg").addEventListener("keypress", function (e) {
        if (e.keyCode === 13) {
            var name = document.getElementById("pikto_fld_msg").value;

            //If there is text in the field, execute Ajax call
            if (name !== '') {

                var xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

                xmlHttp.onreadystatechange = function () {
                    if (xmlHttp.readyState === 4)
                    {
                        document.getElementById("pikto_fld_msg").value = '';
                    }
                };

                xmlHttp.open(
                        "POST",
                        "<?php echo admin_url('admin-ajax.php'); ?>",
                        true
                        );
                xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlHttp.send("action=piktochat_insert_msg&pikto_fld_msg=" + name);
            }
        }
    });
</script>
