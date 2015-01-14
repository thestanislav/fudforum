        <div id="table_wrapper" class="pure-g">
          <div class="pure-u-1-24">&nbsp;</div>
          <div class="pure-u-22-24">
            <!-- Reply form -->
            <form action="/reply/{tid}/{mid}/{do_quote}" method="post"
                  class="pure-form-aligned pure-form">
              <fieldset>
                <div class="pure-control-group">
                  <label>Reply to message # </label>
                  <input name="reply_to" type="text" readonly="true" value="{reply_to_id}">
                </div>
                <div class="pure-control-group">
                  <textarea name="reply_contents" >
                    {quote}
                  </textarea>
                </div>
                <div class="text_right"> 
                  <button name="cancel" type="cancel" 
                          class="pure-button">Cancel</button>
                  <button name="preview" type="submit" 
                          class="pure-button">Preview</button>
                  <button name="submit" type="submit" 
                          class="pure-button pure-button-primary">Submit</button>
                </div>
              </fieldset>         
            </form>
          <div class="pure-u-1-24 pure-skin-fud">&nbsp;</div>
        </div>
      
      <script type="text/javascript">
            //<![CDATA[
            // Replace the <textarea id="editor"> with an CKEditor
            // instance, using the "bbcode" plugin, shaping some of the
            // editor configuration to fit BBCode environment.
            CKEDITOR.replace( 'reply_contents',
              {
                extraPlugins : 'bbcode',
                // Remove unused plugins.
                removePlugins : 'bidi,button,dialogadvtab,div,filebrowser,flash,format,forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,stylescombo,table,tabletools,templates',
                // Width and height are not supported in the BBCode format, so object resizing is disabled.
                disableObjectResizing : true,
                // Define font sizes in percent values.
                fontSize_sizes : "30/30%;50/50%;100/100%;120/120%;150/150%;200/200%;300/300%",
                toolbar :
                [
                  ['Find','Replace','-','SelectAll','RemoveFormat'],
                  ['Link', 'Unlink', 'Image', 'Smiley'],
                  // '/',
                  ['Bold', 'Italic','Underline'],
                  ['FontSize'],
                  ['TextColor'],
                  ['NumberedList','BulletedList','-','Blockquote'],
                  ['Source','-','Undo','Redo']
                ],
                // Strip CKEditor smileys to those commonly used in BBCode.
                smiley_images :
                [
                  'regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','tounge_smile.gif',
                  'embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angel_smile.gif','shades_smile.gif',
                  'cry_smile.gif','kiss.gif'
                ],
                smiley_descriptions :
                [
                  'smiley', 'sad', 'wink', 'laugh', 'cheeky', 'blush', 'surprise',
                  'indecision', 'angel', 'cool', 'crying', 'kiss'
                ]
            } );

            //]]>
          </script>