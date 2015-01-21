        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Pagination -->
              {pagination}
              <!-- Contents -->
              {messages}
              <div id="post_{m_id}" class="fud_post" >
                <div class="header">
                  <div class="width_75 inline_block">
                    <span class="subject">{m_subject}</span>
                    <span class="id">[Message #: {m_id}]</span>
                  </div>
                  <div class="width_auto inline_block text_right">
                    <span class="date">{m_date}</span>
                  </div>
                </div>
                <div class="author" style="height:64px">
                  {m_avatar}
                  <div class="inline_block vertical_top">
                    <span class="author">{m_login}</span>
                  </div>
                </div>
                <div class="clear body">
                  <span>{m_body}</span>
                </div>
                <div class="actions width_100 float_left">
                  <span class="float_left ">Profile</span> <span class="float_right">{m_reply_buttons}</span>
                </div>
                <div class="clear"></div>
              </div>
              {/messages}
              <!-- End Contents -->
            </div>
          </div>
        </div>
