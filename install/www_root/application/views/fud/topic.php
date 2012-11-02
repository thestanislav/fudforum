<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" ></meta>
    <link rel="stylesheet/less" type="text/css" href="/theme/default/theme.css" ></link>
    <script src="/js/less-1.3.0.min.js" type="text/javascript"></script>
</head>

<body>
<?php

echo $navigation;
echo $pagination;

if( !$permissions['READ'] )
	die( "You don't have read permissions to this forum." );

    foreach( $topic as $message )
    {
        $avt_height = 64; // TODO: Get from config
        $height = $avt_height + 4; // 2px padding top and bottom
        $avatar = $message->avatar_loc;
        $avatar = preg_replace("/width=\".*\"/", "width=\"{$avt_height}\"", $avatar );
        $avatar = preg_replace("/height=\".*\"/", "height=\"{$avt_height}\"", $avatar );
        $avatar = str_replace( "<img", "<img class=\"fud_post_author_avatar\"", $avatar);
        $date = date( "D, j F Y H:m", $message->post_stamp );
        $out = "    <div id=\"post_{$message->id}\" class=\"fud_post\" >";
        $out .= "        <div class=\"header\">";
        $out .= "        <div class=\"width_75 inline_block\"><span class=\"subject\">{$message->subject}</span> \n";
        $out .= "        <span class=\"id\">[Message #: {$message->id}]</span></div> \n";
        $out .= "        <div class=\"width_auto inline_block text_right\"><span class=\"date\">{$date}</span></div> \n";
        $out .= "        </div>";
        $out .= "        <div class=\"author\" style=\"height:{$height}px\">";
        $out .= "        {$avatar} \n";
        $out .= "        <div class=\"inline_block vertical_top\"><span class=\"author\">{$message->login}</span></div> \n";
        $out .= "        </div>";
        $out .= "        <div class=\"clear body\">";
        $out .= "        <span>{$message->body}</span> \n";
        $out .= "        </div>";
        $out .= "        <div class=\"actions width_100 float_left\">";
        $out .= "        <span class=\"float_left \">Profile</span> <span class=\"float_left\">PM</span>";
        $out .= $permissions['REPLY'] ? "        <span class=\"float_right\"><a href=\"/reply/{$message->thread_id}/{$message->id}\">Reply</a></span> <span class=\"float_right\"><a href=\"/reply/{$message->thread_id}/{$message->id}/1\">Quote</a></span>" : "";
        $out .= "        </div><div class=\"clear\"></div>";
        $out .= "    </div>\n";

        echo $out;
    }

echo $pagination;
?>
</body>
</html>
