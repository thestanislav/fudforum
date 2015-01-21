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
                  <th class="th forum first_column">Forum</th>
                  <th class="th messages">Messages</th>
                  <th class="th topics">Topics</th>
                  <th class="th last_message">Last&nbsp;message</th>
                </thead>
                <tbody>
                  {fora}
                  <tr>
                    <td class="forum first_column"><a href="{f_url}">{f_name}</a><br/>{f_description}</td>
                    <td class="messages">{f_post_count}</td>
                    <td class="topics">{f_thread_count}</td>
                    <td class="last_message">
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