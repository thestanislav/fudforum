        <div id="table_wrapper" class="pure-g">
          <div class="pure-u-1-24">&nbsp;</div>
          <div class="pure-u-22-24">
            <!-- Navigation -->
            {path_navigation}                
            <table id="fora_table" 
                  class="fud_table pure-table pure-table-striped" >
                <script>
                  // TODO(nexus): move in common script file (?)
                  $().ready( function() {
                    $(".toggler").click( function() {
                      var id =  this.id;  
                      if( $(this).html() == "-" ) {
                        $(".cat_"+id.slice(0,id.length-8)+"_child").hide();
                        $(this).html("+");
                      } else {
                        $(".cat_"+id.slice(0,id.length-8)+"_child").show();
                        $(this).html("-");
                      }
                    })
                  });
                </script>
                <thead class="table_header" >
                    <th class="first_column">Forum</th>
                    <th class="">Messages</th>
                    <th class="">Topics</th>
                    <th class="">Last&nbsp;message</th>
                </thead>
                <tbody>
                {categories}
                  <tr id="cat_{c_id}">
                    <td class="category first_column" colspan="4" >
                      <span id="{c_id}_toggler" class="toggler pure-button">-</span>
                      <span id="{c_id}_link"><a href="{c_url}">{c_name}</a></span>
                      <span id="{c_id}_description">{c_description}</span>                   
                    </td>
                  </tr>
                  {fora}
                    <tr class="cat_{c_id}_child">
                      <td class="forum first_column"><a href="{f_url}">{f_name}</a><br/>{f_description}</td>
                      <td class="messages">{f_post_count}</td>
                      <td class="topics">{f_thread_count}</td>
                      <td class="last_message">
                        <span class="date">{f_last_date}</span>
                        <span class="author">{f_last_author}</span>
                      </td>
                    </tr>
                  {/fora}
                {/categories}
                </tbody>
            </table>
          </div>
          <div class="pure-u-1-24">&nbsp;</div>
        </div>