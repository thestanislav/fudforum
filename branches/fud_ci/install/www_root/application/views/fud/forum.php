<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" ></meta>
    <link rel="stylesheet/less" type="text/css" href="/theme/default/theme.css" ></link>
    <script src="/js/less-1.3.0.min.js" type="text/javascript"></script>
</head>

<body>

<?php echo $navigation ?>

<?php echo $pagination ?>

<table id="topics_table" class="fud_table" border="0" cellspacing="1" cellpadding="2" >
    <thead class="table_header">
        <th class="th topic">Topic</th>
        <th class="th replies">Replies</th>
        <th class="th views">Views</th>
        <th class="th last_message">Last message</th>
    </thead>
    <tbody>
<?php
    for( $i=0; $i<count($topics); ++$i )
    {
        $topic = $topics[$i];
        //die("<pre>".print_r($topic,true)."</pre>");
        $row_cl = "";
        if( $i % 2 )
            $row_cl .= "odd";
        else
            $row_cl .= "even";

        $author = $topic->root_message->login;
        $desc = $topic->tdescr;
        $date = date( "D, j F Y", $topic->root_message->post_stamp );
        $last_author = $topic->last_message->login;
        $last_date = date( "D, j F Y H:m", $topic->last_message->post_stamp );

        $out = "        " .
               "<tr class=\"{$row_cl}\" >" .
               "<td class=\"topic\">".
               "<div>".
               "<div class=\"width_100\"><a href=\"/topic/{$cid}/{$fid}/{$topic->topic_id}\">{$topic->subject}</a></div>".
               "<div class=\"width_100 inline_block\">{$desc}</div>".
               "<div class=\"width_100 author_and_date\"><span class=\"author\">by {$author}</span> <span class=\"date\">on {$date}</span></div>" .
               "</div>" .
               "</td>" .
               "<td class=\"replies\">{$topic->replies}</td>" .
               "<td class=\"views\">{$topic->views}</td>" .
               "<td class=\"last_message\"><span class=\"date\">{$last_date}</span><br/><span class=\"author\">by {$last_author}</span></td>" .
               "</tr>" .
               "\n";
        echo $out;
    }
?>
    </tbody>
</table>

<?php echo $pagination ?>

</body>
</html>
