{*  remove javascript
<script type="text/javascript" src="{$theme_dir}/js/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="{$theme_dir}/js/jplayer.playlist.min.js"></script>
*}
{literal}
<script type="text/javascript">
    $(document).ready(function(){

        new jPlayerPlaylist({
            jPlayer: "#jquery_jplayer_1",
            cssSelectorAncestor: "#jp_container_1"
        }, [
{/literal}
        {foreach from=$FILE key=ID_FILE item=ITEM}{literal}
            {
                title:"{/literal}{$ITEM.filename}",
                artist:"{$src}",
                wav:"{$ITEM.url}"
    {literal}},{/literal}
        {/foreach}  {literal}
        ], {
            swfPath: "js",
            supplied: "webmv, ogv, m4v, oga, mp3, wav",
            smoothPlayBar: true,
            keyEnabled: true,
            audioFullScreen: true
        });
    });
    //]]>
</script>
{/literal}
<div id="jp_container_1" class="jp-video jp-video-270p">
    <div class="jp-type-playlist">
    <div id="jquery_jplayer_1" class="jp-jplayer" style="display: none;"></div>
    <div class="jp-gui">
        <div class="jp-video-play">
            <a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
        </div>
        <div class="jp-interface">
            <div class="jp-progress">
                <div class="jp-seek-bar">
                    <div class="jp-play-bar"></div>
                </div>
            </div>
            <div class="jp-current-time"></div>
            <div class="jp-duration"></div>
            <div class="jp-controls-holder">
                <ul class="jp-controls">
                    <li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
                    <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                    <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                    <li><a href="javascript:;" class="jp-next" tabindex="1">next</a></li>
                    <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                    <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                    <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                    <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
                </ul>
                <div class="jp-volume-bar">
                    <div class="jp-volume-bar-value"></div>
                </div>
                <ul class="jp-toggles">
                    <li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
                    <li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
                    <li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>
                    <li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>
                    <li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
                    <li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
                </ul>
            </div>
            <div class="jp-title">
                <ul>
                    <li></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="jp-playlist">
        <ul>
            <!-- The method Playlist.displayPlaylist() uses this unordered list -->
            <li></li>
        </ul>
</div></br>
<div id="call_info">
    <table style="font-size:12px">
    <tr>
        <td width="18%"><b>Gọi từ:</b></td>
        <td width="40%" id="src"></td>
        <td width="20%"><b>Gọi đến:</b></td>
        <td width="20%" id="dst"></td>
    </tr>
    <tr>
        <td><b>Từ kênh:</b></td>
        <td id="channel"></td>
        <td><b>Đến kênh:</b></td>
        <td id="dstchannel"> </td>
    </tr>
    <tr>
        <td><b>Ngày giờ:</b></td>
        <td id="calldate"></td>
        <td><b>Thời gian:</b></td>
        <td id="billsec"></td>
    </tr>
    </table>
</div>
<div class="jp-no-solution">
    <span>Update Required</span>
    To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
</div>
</div>
</div