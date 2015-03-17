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
                      <th class="fud_wide_column">Forum</th>
                      <th class="fud_text_center">Messages</th>
                      <th class="fud_text_center">Topics</th>
                      <th class="fud_text_center fud-hide-sm">Last&nbsp;message</th>
                  </thead>
                  <tbody>
                  {categories}
                    <tr id="cat_{c_id}">
                      <td class="fud_wide_column" colspan="4" >
                        <span id="{c_id}_toggler" class="toggler pure-button">-</span>
                        <span id="{c_id}_link"><a href="{c_url}">{c_name}</a></span>
                        <span id="{c_id}_description" class="fud-hide-sm">
                          {c_description}
                        </span>                   
                      </td>
                    </tr>
                    {fora}
                      <tr class="cat_{c_id}_child">
                        <td class="forum fud_wide_column">
                          <div class="fud_padding_sm fud_text_center inline_block fud-hide-sm">
                            {f_icon}
                          </div>
                          <div class="fud_padding_sm fud_text_center inline_block fud-hide-sm">
                            {f_new_messages_icon}
                          </div>
                          <div class="inline_block">
                            <div><a href="{f_url}">{f_name}</a></div>
                            <div class="fud-hide-sm">{f_description}</div>
                          </div>
                        </td>                        
                        <td class="fud_text_center">{f_post_count}</td>
                        <td class="fud_text_center">{f_thread_count}</td>
                        <td class="fud_text_center fud-hide-sm">
                          <div class="date fud-hide-sm">{f_last_date}</div>
                          <div class="author fud-hide-sm">{f_last_author}</div>
                        </td>
                      </tr>
                    {/fora}
                  {/categories}
                  </tbody>
              </table>
            </div> <!-- contents -->
          </div> <!-- table wrapper pure unit -->
        </div> <!-- table wrapper pure grid -->