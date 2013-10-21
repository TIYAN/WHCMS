<ul>
{foreach from=$tweets item=tweet key=num}
{if $num < $numtweets}
  <li><b>{$tweet.date}</b> - {$tweet.tweet}</li>
{/if}
{/foreach}
</ul>
<p>{$LANG.twitterfollowus} @ <a href="http://twitter.com/{$twitterusername}" target="_blank">www.twitter.com/{$twitterusername}</a> {$LANG.twitterfollowuswhy}</p>