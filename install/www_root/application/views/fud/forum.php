        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Pagination -->
              {pagination}
              <!-- Contents table -->
              <table id="topics_table" 
                      class="fud_table pure-table pure-table-striped pure-skin-fud" 
                      border="0" cellspacing="1" cellpadding="2" >
                <thead class="table_header">
                  <th class="th topic first_column">Topic</th>
                  <th class="th replies">Replies</th>
                  <th class="th views">Views</th>
                  <th class="th last_message">Last&nbsp;message</th>
                </thead>
                <tbody>
                {topics}
                  <tr>
                    <td class="topic first_column">
                      <div>
                        <div>
                          <a href="{t_url}">{t_subject}</a>
                          <span class="float_right">
                            <span class="author">By {t_author}</span>
                            <span class="date">on {t_date}</span>
                          </span>
                        </div>
                        <div>{t_description}</div>
                      </div>
                    </td>
                    <td class="replies">{t_replies}</td>
                    <td class="views">{t_views}</td>
                    <td class="last_message">
                      <div class="date">{t_last_date}</div>
                      <div class="author">by {t_last_author}</div>
                    </td>
                  </tr>
                {/topics}
                </tbody>
              </table>
              <!-- Pagination -->
              {pagination}
            </div>
          </div>
        </div>