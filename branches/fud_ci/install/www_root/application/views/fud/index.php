
<?php echo $navigation; ?>

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
    $forums = $visibleForums[$cat->id];

	$cat_url = site_url( "category/{$cat->id}" );

    $out .= "<tr><td class=\"category\" colspan=\"4\" ><a href=\"{$cat_url}\">{$cat->name}</a> {$cat->description}</td></tr>";

    foreach( $forums as $forum )
    {
        $desc = empty($forum->descr) ? "&nbsp" : $forum->descr;

        $last_date = null;
        if( isset($forum->last_post->post_stamp) )
            $last_date = date( "D, j F Y", $forum->last_post->post_stamp );
		
		$forum_url = site_url( "forum/{$cat->id}/{$forum->id}" );
		
        $out .= "<tr>";
        $out .= "<td class=\"forum\"><a href=\"{$forum_url}\">{$forum->name}</a><br/>{$desc}</td>";
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
