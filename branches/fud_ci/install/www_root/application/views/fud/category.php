        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Contents table -->
              <table id="fora_table" 
                      class="fud_table pure-table pure-table-striped pure-skin-fud" 
                      border="0" cellspacing="1" cellpadding="2">
                <thead class="table_header" >
                  <th class="fud_wide_column" colspan="3" >Forum</th>
                  <th class="fud_text_center">Messages</th>
                  <th class="fud_text_center">Topics</th>
                  <th class="fud_text_center">Last&nbsp;message</th>
                </thead>
                <tbody>
                  {fora}
                  <tr>
                    <td class="fud_padding_sm fud_text_center">{f_icon}</td>
                    <td class="fud_padding_sm fud_text_center">{f_new_messages_icon}</td>
                    <td class="fud_wide_column">
                      <div><a href="{f_url}">{f_name}</a></div>
                      <div>{f_description}</div>
                    </td>
                    <td class="fud_text_center">{f_post_count}</td>
                    <td class="fud_text_center">{f_thread_count}</td>
                    <td class="">
                      <div class="date">{f_last_date}</div>
                      <div class="author">{f_last_author}</div>
                    </td>
                  </tr>
                  {/fora}
                </tbody>
              </table>
            </div>
          </div>
        </div>