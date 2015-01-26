        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
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
                      <th class="fud_wide_column" colspan="3" >Forum</th>
                      <th class="fud_text_center">Messages</th>
                      <th class="fud_text_center">Topics</th>
                      <th class="fud_text_center">Last&nbsp;message</th>
                  </thead>
                  <tbody>
                  {categories}
                    <tr id="cat_{c_id}">
                      <td class="fud_wide_column" colspan="6" >
                        <span id="{c_id}_toggler" class="toggler pure-button">-</span>
                        <span id="{c_id}_link"><a href="{c_url}">{c_name}</a></span>
                        <span id="{c_id}_description">{c_description}</span>                   
                      </td>
                    </tr>
                    {fora}
                      <tr class="cat_{c_id}_child">
                        <td class="fud_padding_sm">{f_icon}</td>
                        <td class="fud_padding_sm">{f_new_messages_icon}</td>
                        <td class="forum fud_wide_column">
                          <div><a href="{f_url}">{f_name}</a></div>
                          <div>{f_description}</div>
                        </td>                        
                        <td class="fud_text_center">{f_post_count}</td>
                        <td class="fud_text_center">{f_thread_count}</td>
                        <td class="fud_text_center">
                          <div class="date">{f_last_date}</div>
                          <div class="author">{f_last_author}</div>
                        </td>
                      </tr>
                    {/fora}
                  {/categories}
                  </tbody>
              </table>
            </div> <!-- contents -->
          </div> <!-- table wrapper pure unit -->
        </div> <!-- table wrapper pure grid -->