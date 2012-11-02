<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" ></meta>
    <link rel="stylesheet/less" type="text/css" href="/theme/default/theme.css" ></link>
    <script src="/js/less-1.3.0.min.js" type="text/javascript"></script>
</head>

<table id="fora_table" class="fud_table" border="0" cellspacing="1" cellpadding="2">
    <thead class="table_header" >
        <th class="th forum">Forum</th>
        <th class="th messages">Messages</th>
        <th class="th topics">Topics</th>
        <th class="th last_message">Last message</th>
    </thead>
    <tbody>
<?php
$i = 0;

$out = "		";
foreach( $cats as $cat )
{
	if( !array_key_exists( $cat->id, $catForums ) )
		continue;
    $forums = $catForums[$cat->id];
    if( !is_array($forums) )
        $forums = array($forums);

    $out .= "<tr><td class=\"category\" colspan=\"4\" ><a href=\"/category/{$cat->id}\">{$cat->name}</a> {$cat->description}</td></tr>";

    foreach( $forums as $forum )
    {
        //die(print_r($forum,true));
        $row_cl = "";
        if( $i % 2 )
            $row_cl .= " odd";
        else
            $row_cl .= " even";

        $desc = empty($forum->descr) ? "&nbsp" : $forum->descr;

        $last_date = null;
        if( isset($forum->last_post->post_stamp) )
            $last_date = date( "D, j F Y", $forum->last_post->post_stamp );

        $out .= "<tr class=\"{$row_cl}\">";
        $out .= "<td class=\"forum\"><a href=\"/forum/{$cat->id}/{$forum->id}\">{$forum->name}</a><br/>{$desc}</td>";
        $out .= "<td class=\"messages\">{$forum->post_count}</td>";
        $out .= "<td class=\"topics\">{$forum->thread_count}</td>";
        $out .= "<td class=\"last_message\" >";
        if( $last_date )
        {
            $out .= "<span class=\"date\">{$last_date}</span><br/><span class=\"author\">by {$forum->last_post->login}</span>";
        }
        $out .= "</td>";
        $out .= "</tr> \n";

        ++$i;
    }

}
echo $out;
?>
    </tbody>
</table>
</html>
