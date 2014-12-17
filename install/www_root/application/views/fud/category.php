      <!-- Navigation -->
      {navigation}
      <!-- Contents table -->
      <table id="fora_table" class="fud_table" border="0" cellspacing="1"
      cellpadding="2">
      <thead class="table_header" >
        <th class="th forum">Forum</th>
        <th class="th messages">Messages</th>
        <th class="th topics">Topics</th>
        <th class="th last_message">Last message</th>
      </thead>
      <tbody>
        {forums}
        <tr>
          <td class="forum"><a href="{f_url}">{f_name}</a><br/>{f_description}</td>
          <td class="messages">{f_post_count}</td>
          <td class="topics">{f_thread_count}</td>
          <td class="last_message">
            <span class="date">{f_last_date}</span>
            <span class="author">{f_last_author}</span>
          </td>
        </tr>
        {/forums}
      </tbody>
    </table>
