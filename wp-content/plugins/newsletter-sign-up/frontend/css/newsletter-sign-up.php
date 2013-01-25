<?php header('Content-type: text/css'); 

if (isset($_GET['checkbox_reset']) && $_GET['checkbox_reset'] == 1) { ?>
    p#ns-checkbox{ clear:both; display:block; }
    p#ns-checkbox input{ margin:0 5px 0 0; display:inline-block; width:13px; height:13px; }
    p#ns-checkbox label{ display:inline-block; }
<?php } 
if (isset($_GET['form_css']) && $_GET['form_css'] == 1) { ?>
    .nsu-form { margin:10px 0; }

    .nsu-form p,.nsu-text-before-form,.nsu-text-after-form{ clear:both; display:block; margin:5px 0; }
    .nsu-text-before-form ul,nsu-text-after-form ul,.nsu-text-before-form ol,nsu-text-after-form ol{ margin-left:15px; }

    .nsu-form label{ display:block; font-weight:bold; }
    .nsu-form input{ padding:2px; display:block; color:#999; }
    .nsu-form input:focus, .nsu-form input:active{ color:#000; }
    
    .nsu-field{ background:white; }
    input.nsu-submit{ color:#000; }
    
    .nsu-error { font-size:75%; font-style:italic; }
<?php } ?>