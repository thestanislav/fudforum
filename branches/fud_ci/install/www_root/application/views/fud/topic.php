        <div id="table_wrapper_grid" class="pure-g">
          <div id="table_wrapper_unit" class="pure-u-1">
            <div id="contents">
              <!-- Navigation -->
              {path_navigation}
              <!-- Pagination -->
              {pagination}
              <!-- Contents -->
              <div id="messages">
                {messages}
                <div id="post_{m_id}" class="fud-message" >
                  <div class="fud-message-header">
                    <div class="width_75 inline_block">
                      <span class="fud-message-subject">{m_subject}</span>
                      <span class="fud-message-id">[Message #: {m_id}]</span>
                    </div>
                    <div class="width_auto inline_block text_right">
                      <span class="fud-message-date">{m_date}</span>
                    </div>
                  </div>
                  <div class="fud-message-author" >
                    <div class="vertical_top fud-message-author-info">
                      <img class="fud-message-author-avatar" src="{m_avatar_url}">
                      <div class="fud-message-author-textual-info">
                        <div class="fud-message-author-name">{m_login}</div>
                      </div>
                    </div>
                    <div class="fud-contact-actions-container">
                      <div class="fud-contact-actions-button-set">
                        <a class="pure-button button-small" href="#">Profile</a>
                      </div>
                      <div class="fud-contact-actions-button-set">
                        <a class="pure-button button-small" href="#">PM</a>
                        <a class="pure-button button-small" href="#">Email</a>
                      </div>
                      <div class="fud-contact-actions-button-set">
                        <a class="pure-button button-small" href="#">Add buddy</a>
                        <a class="pure-button button-small" href="#">Ignore</a>
                      </div>
                    </div>                    
                  </div>
                  <div class="clear fud-message-body">
                    <span>
                      {m_body}
                    </span>
                  </div>
                  <div class="fud-message-actions">
                    <?php if($can_reply): ?>
                    <a class="pure-button" href="{m_reply_url}">{m_reply_text}</a>
                    <a class="pure-button" href="{m_quote_url}">{m_quote_text}</a>
                    <?php endif; ?>
                  </div>
                  <div class="clear"></div>
                </div>
                {/messages}
              </div>
              <!-- End Contents -->
            </div>
          </div>
        </div>
