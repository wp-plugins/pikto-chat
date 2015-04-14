<style>
    #pikto-chat-link {
        background-color:#378de5;
        -moz-border-radius:6px;
        -webkit-border-radius:6px;
        border-radius:6px;
        display:inline-block;
        cursor:pointer;
        color:#ffffff;
        font-family:arial;
        font-size:15px;
        font-weight:bold;
        padding:6px 24px;
        text-decoration:none;
        text-shadow:0px 1px 0px #528ecc;
    }
    #pikto-chat-link:hover {
        background-color:#79bbff;
    }
    #pikto-chat-link:active {
        background-color:#378de5;
    }
</style>

<a href="javascript:;" id="pikto-chat-link" onclick="displayChat();">Chat!</a>

<script>
    function displayChat() {
        document.getElementsByTagName("body")[0].appendChild(document.getElementById("piktochatbox"));
        document.getElementById("piktochatbox").style.display = (document.getElementById("piktochatbox").style.display === "block") ? "none" : "block";
    }
</script>
