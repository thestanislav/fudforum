      {error_message}
      <form id="login" method="post" action="{login_url}">
        <table>
          <tbody><tr>
          	<th colspan="3">Login Form</th>
          </tr>
          <tr>
          	<td class="" colspan="3">

          		You are not logged in. This could be due to one of several reasons:
          		<ol>
          			<li class="GenText">Your cookie has expired, and you need to login to renew your cookie.</li>
          			<li class="GenText">You do not have permission to access the requested resource as an anonymous user.
                  You must login to gain permission.</li>
          		</ol>	</td>
          </tr>
          <tr class="">
          	<td class="">Login:</td>
          	<td><input tabindex="1" name="login" type="text"></td>
          	<td class="nw"><a href="{register_url}">Want to register?</a></td>
          </tr>
          <tr class="">
          	<td class="GenText">Password:</td>
          	<td><input tabindex="2" name="password" type="password"></td>
          	<td class="nw"><a href="{passowrd_reset_url}">Forgot password</a></td>
          </tr>
          <tr>
          	<td colspan="3" class="">
              <input class="button" tabindex="3" value="Login" type="submit">
            </td>
          </tr>
          </tbody>
        </table>
      </form>
