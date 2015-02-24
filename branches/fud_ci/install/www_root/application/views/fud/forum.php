        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Pagination -->
              {pagination}
              <!-- Contents table -->
              <?php if( !empty($new_topic_url) ) { ?>
              <div class="pos_relative height_2em">
                <span style="" id="fud_new_topic_button_top">
                  <a class="pure-button" href="{new_topic_url}">New topic</a>
                </span>
              </div>
              <?php } ?>
              <table id="topics_table" 
                      class="fud_table pure-table pure-table-striped pure-skin-fud" 
                      border="0" cellspacing="1" cellpadding="2" >
                <thead class="table_header">
                  <th class="fud_wide_column" colspan="3">Topic</th>
                  <th>First&nbsp;message</th>
                  <th>Replies</th>
                  <th>Views</th>
                  <th>Last&nbsp;message</th>
                </thead>
                <tbody>
                {topics}
                  <tr>
                    <td class="fud_padding_sm fud_text_center">{t_icon}</td>
                    <td class="fud_padding_sm fud_text_center">{t_new_messages_icon}</td>
                    <td class="fud_wide_column">
                      <div>
                        <div><a href="{t_url}">{t_subject}</a></div>
                        <div>{t_description}</div>
                      </div>
                    </td>
                    <td>
                      <div class="date">{t_date}</div>
                      <div class="author">by {t_author}</div>  
                    </td>
                    <td class="fud_text_center">{t_replies}</td>
                    <td class="fud_text_center">{t_views}</td>
                    <td>
                      <div class="date">{t_last_date}</div>
                      <div class="author">by {t_last_author}</div>
                    </td>
                  </tr>
                {/topics}
                </tbody>
              </table>
              <?php if( !empty($new_topic_url) ) { ?>
              <div class="pos_relative height_2em">
                <span style="" id="fud_new_topic_button_bottom">
                  <a class="pure-button" href="{new_topic_url}">New topic</a>
                </span>
              </div>
              <?php } ?>
              <!-- Pagination -->
              {pagination}
            </div>
          </div>
        </div>