    <div id="wrapper-grid" class="pure-g pure-skin-fud">
      <div class="pure-u-1-24 pure-skin-fud">&nbsp;</div>
      <div id="wrapper-unit" class="pure-u-22-24 pure-skin-fud">          
        <!-- Header -->
        {header}
        <!-- Menu -->
        {site_navigation}
        <!-- Navigation -->
        {path_navigation}
        <!-- Pagination -->
        {pagination}
        <!-- Contents table -->
        <table id="topics_table" 
                class="fud_table pure-table pure-table-striped pure-skin-fud" 
                border="0" cellspacing="1" cellpadding="2" >
          <thead class="table_header">
            <th class="th topic fud_first_col">Topic</th>
            <th class="th replies">Replies</th>
            <th class="th views">Views</th>
            <th class="th last_message">Last message</th>
          </thead>
          <tbody>
          {topics}
            <tr>
              <td class="topic fud_first_col">
                <div>
                  <div class="width_100"><a href="{t_url}">{t_subject}</a></div>
                  <div class="width_100 inline_block">{t_description}</div>
                  <div class="width_100 author_and_date">
                    <span class="author">by {t_author}</span>
                    <span class="date">on {t_date}</span>
                  </div>
                </div>
              </td>
              <td class="replies">{t_replies}</td>
              <td class="views">{t_views}</td>
              <td class="last_message">
                <span class="date">{t_last_date}</span><br/>
                <span class="author">by {t_last_author}</span>
              </td>
            </tr>
          {/topics}
          </tbody>
        </table>
        <!-- Pagination -->
        {pagination}
      </div>
      <div class="pure-u-1-24 pure-skin-fud">&nbsp;</div>
    </div>