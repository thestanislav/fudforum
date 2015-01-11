    <!-- Menu -->
    {site_navigation}
    <!-- Navigation -->
    {path_navigation}
    <table id="fora_table" class="fud_table pure-table pure-table-striped pure-skin-fud" border="0" cellspacing="1" cellpadding="2">
        <script>
          // TODO(nexus): move in common script file (?)
          $().ready( function() {
            $(".toggler").click( function() {
              var id =  this.id;  
              if( $(this).html() == "-" ) {
                $(".cat_"+id.slice(0,id.length-8)+"_child").slideUp();
                $(this).html("+");
              } else {
                $(".cat_"+id.slice(0,id.length-8)+"_child").slideDown();
                $(this).html("-");
              }
            })
          });
        </script>
        <thead class="table_header" >
            <th class="th forum fud_first_col">Forum</th>
            <th class="th messages">Messages</th>
            <th class="th topics">Topics</th>
            <th class="th last_message">Last message</th>
        </thead>
        <tbody>
        {categories}
          <tr id="cat_{c_id}">
            <td class="category fud_first_col" colspan="4" >
              <span id="{c_id}_toggler" class="toggler pure-button pure-skin-fud">-</span>
              <span id="{c_id}_link"><a href="{c_url}">{c_name}</a></span>
              <span id="{c_id}_description">{c_description}</span> 
              
            </td>
          </tr>
          {forums}
            <tr class="cat_{c_id}_child">
              <td class="forum fud_first_col"><a href="{f_url}">{f_name}</a><br/>{f_description}</td>
              <td class="messages">{f_post_count}</td>
              <td class="topics">{f_thread_count}</td>
              <td class="last_message">
                <span class="date">{f_last_date}</span>
                <span class="author">{f_last_author}</span>
              </td>
            </tr>
          {/forums}
        {/categories}
        </tbody>
    </table>
