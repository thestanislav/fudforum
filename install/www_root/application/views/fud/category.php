        <div id="table_wrapper" class="pure-g">
          <div class="pure-u-1-24">&nbsp;</div>
          <div class="pure-u-22-24">
            <!-- Navigation -->
            {path_navigation}
            <!-- Contents table -->
            <table id="fora_table" 
                    class="fud_table pure-table pure-table-striped pure-skin-fud" 
                    border="0" cellspacing="1" cellpadding="2">
              <thead class="table_header" >
                <th class="th forum fud_first_col">Forum</th>
                <th class="th messages">Messages</th>
                <th class="th topics">Topics</th>
                <th class="th last_message">Last message</th>
              </thead>
              <tbody>
                {fora}
                <tr>
                  <td class="forum fud_first_col"><a href="{f_url}">{f_name}</a><br/>{f_description}</td>
                  <td class="messages">{f_post_count}</td>
                  <td class="topics">{f_thread_count}</td>
                  <td class="last_message">
                    <span class="date">{f_last_date}</span>
                    <span class="author">{f_last_author}</span>
                  </td>
                </tr>
                {/fora}
              </tbody>
            </table>
          </div>
          <div class="pure-u-1-24 pure-skin-fud">&nbsp;</div>
        </div>