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
                <div class="author" >
                  {m_avatar}
                  <div class="contact_actions inline_block">
                    <div>
                      <a class="pure-button button-xsmall" href="#">Profile</a>
                    </div>
                    <div>
                      <a class="pure-button button-xsmall" href="#">PM</a>
                    </div>
                    <div>
                      <a class="pure-button button-xsmall" href="#">Email</a>
                    </div>
                  </span>
                  </div>
                  <div class="contact_actions inline_block">
                    <div>
                      <a class="pure-button button-xsmall" href="#">Add buddy</a>
                    </div>
                    <div>
                      <a class="pure-button button-xsmall" href="#">Ignore</a>
                    </div>
                  </span>
                  </div>
                  <div class="inline_block vertical_top">
                    <span class="author">{m_login}</span>
                  </div>                  
                </div>
                <div class="clear body">
                  <span>
                    {m_body}
                  </span>
                </div>
                <div class="actions">
                  <?php if($can_reply): ?>
                  <a class="pure-button" href="{m_reply_url}">{m_reply_text}</a>
                  <a class="pure-button" href="{m_quote_url}">{m_quote_text}</a>
                  <?php endif; ?>
                </div>
                <div class="clear"></div>
              </div>
              {/messages}
              <!-- End Contents -->
            </div>
          </div>
        </div>
