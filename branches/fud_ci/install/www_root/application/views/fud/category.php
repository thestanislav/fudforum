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
                  <th class="">Messages</th>
                  <th class="">Topics</th>
                  <th class="">Last&nbsp;message</th>
                </thead>
                <tbody>
                  {fora}
                  <tr>
                    <td class="">{f_icon}</td>
                    <td class=""></td>
                    <td class="forum fud_wide_column">
                      <a href="{f_url}">{f_name}</a><br/>{f_description}
                    </td>
                    <td class="">{f_post_count}</td>
                    <td class="">{f_thread_count}</td>
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