<?php
require_once("./includes/upb.initialize.php");
$where = "Frequently Asked Questions - FAQ";
require_once("./includes/header.php");
echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
echo "
			<tr>
				<th>Using BBcode</th>
			</tr>
			<tr>
				<td class='area_2'>
This guide outlines how BBcode works. Ideally you would use the buttons and lists above the message entry box to create the BBcode.
<p>
<strong>Formatting Text</strong><p>
Using BBcode you can format text using various tags, e.g. [b]bold[/b] would make the text <strong>Bold</strong> and [i]italic[/i] would make it <i>italic</i>
The text formatting tags are outlined below:
<ul class='tabstyle_2'>
<li class='tabstyle_2'>[b][/b]  - bold text</li>
<li class='tabstyle_2'>[i][/i] - italic text</li>
<li class='tabstyle_2'>[u][/u] - underline text</li>
<li class='tabstyle_2'>[left][/left] - left align text</li>
<li class='tabstyle_2'>[right][/right] - right align text</li>
<li class='tabstyle_2'>[center][/center] - center text</li>
<li class='tabstyle_2'>[justify][/justify] - fully justify text</li>
</ul></p>
<p>
<strong>Changing text font</strong><br />
To use a different font for part or all of your message use [font=font name]Text[/font] replacing 'font name' with the font of your choice.</p>
<p><strong>Changing text color</strong><br />
To colorize your text use [color=hex code]Text[/color] where hex code is the hexadecimal code for the color you wish to use e.g. [color=#FF0000] is red, [color=#0000FF] is blue and [color=#000000] is black (invalid codes default to black). A list of common color hex codes is available <a href='http://users.adelphia.net/~technet/docs/colors4.gif'>here</a></p>
<p><strong>Changing text size</strong><br />
To resize the text use [size=size in pixels] replacing 'size in pixels' with the text you require. The default size is 11 pixels</p>
<p>
All text formatting BBcode tags can be combined with each other so Bold Italic Size 24pixels Blue text, right aligned written in Courier would be [b][i][size=24][color=#0000FF][right][font=Courier]Text[/font][/right][/color][/size][/i][/b] and appear as <div style='text-align:right;font-family:Courier;font-weight:bold;font-style: italic;font-size:24px;color:#0000FF;'>Text</div>

<p><strong>Adding links</strong><br />
A link can be added into a post in several ways. If you wish to add a clickable text link like
'http://".$_SERVER['HTTP_HOST']."' use this code [url]http://".$_SERVER['HTTP_HOST']."[/url].
<br />
If you wish to put a link behind a text use this code [url=http://".$_SERVER['HTTP_HOST']."]this site[/url] appears as <span class='sample_link' title='http://".$_SERVER['HTTP_HOST']."'>this site</span>
<p>
The same can be done for links to email addresses e.g. [email]myemail@myserver.com[/email] or [email=someone@somewhere.com]email someone[/email]

<p><strong>Creating lists</strong>
You can created ordered or unordered lists using [ul][/ul] or [ol][/ol] respectively.
Items in the list are tagged using [*][/*]<br />
e.g. [ul][*]Item 1[/*][*]Item 2[/*][*]Item 3[/*][/ul] would appear as
<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>

<p><strong>Miscellaneous BBcode</strong><br />
To add a picture, just place the URL of the image between [img][/img] tags e.g. [img]http://www.images.com/mypicture.jpg[/img]<p>
To add a quote place the quote in [quote][/quote] tags. If you press the quote button on a post it will enter these tags for you
<p>If you want horizontally scrolling text you can place the text between [move][/move] tags
<p>
Offtopic text can be placed using [offtopic][/offtopic] tags or you can just use an offtopic smilie if one is available
</p>
<p>If you want to post code for help with programming or a bug fix you can use [code][/code] tags
      </td>
      </tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
//dump($_SERVER);
echo "<tr><th>Using Smilies</th></tr><tr><td class='area_2'>Smilies can be inserted by either clicking the image under the message box or entering the text.<p>
Default smilies codes include: <ul class='tabstyle_2'><li class='tabstyle_2'>:) - a happy face which is replaced by <img src='./smilies/smile.gif' alt='smile' title='smile' class='example' /><li class='tabstyle_2'>:( - a frown which is replaced by <img src='./smilies/frown.gif' alt='frown' title='frown' class='example' /><li class='tabstyle_2'>;) - a wink which is replaced by <img src='./smilies/wink.gif' alt='wink' title='wink' class='example' /></ul></p></td></tr>
<tr>

				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";

echo "<tr><th>RSS Feeds</th></tr><tr><td class='area_2'>The <img src='images/rss.png' class='rss' alt='RSS' /> icon indicates there is an RSS Feed available.<p>This allows you to keep track of new topics or posts without visiting the forum.</p><p>Just click on the image and subscribe to the feed using your browser or paste the URL into the RSS Reader software you are using.</p></td></tr>";
echo "
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
echoTableFooter(SKIN_DIR);
require_once("./includes/footer.php");
?>