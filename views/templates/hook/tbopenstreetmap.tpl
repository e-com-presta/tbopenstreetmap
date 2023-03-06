{if !empty($stores)}
<div id="osmap"></div>
<div id="osmap-table" class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                {if $show_image}
                    <th class="text-center">&nbsp;</th>
                {/if}
                <th class="text-center">&nbsp;</th>
                <th>{l s='Shop name' mod='tbopenstreetmap'}</th>
                <th>{l s='Adress' mod='tbopenstreetmap'}</th>
                <th>{l s='City' mod='tbopenstreetmap'}</th>
                <th>{l s='Country' mod='tbopenstreetmap'}</th>
                <th>{l s='Phone' mod='tbopenstreetmap'}</th>
                <th>{l s='E-mail' mod='tbopenstreetmap'}</th>
                <th class="text-center">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$stores item=st}
            <tr>
                {if $show_image}
                    <td class="shop-img text-center">
                    {if $st.has_picture}
                        <img src="{Link::getGenericImageLink(
                            'stores',
                            $st.id_store,
                            'medium_default',
                            (ImageManager::retinaSupport()) ? '2x' : ''
                            )|escape:'htmlall':'UTF-8'}"
                            alt="{$st.name|escape:'html':'UTF-8'}"
                            width="{$mediumSize.width}"
                            height="{$mediumSize.height}"
                            class="img-responsive"
                        >
                    {/if}
                    </td>
                {/if}
                <td class="text-center">
                {if isset($st.working_hours)}
                    <a title="{l s='Working hours' mod='tbopenstreetmap'}" data-toggle="osmpopover" data-content="{foreach from=$st.working_hours item=one_day}{l s=$one_day.day mod='tbopenstreetmap'}: {$one_day.hours}<br>{/foreach}">
                        <i class="icon icon-calendar icon-lg"></i>
                    </a>
                {/if}
                </td>
                <td>{$st.name|escape:'html':'UTF-8'}</td>
                <td>{$st.address1|escape:'html':'UTF-8'}</td>
                <td>{$st.postcode|escape:'html':'UTF-8'} {$st.city|escape:'html':'UTF-8'}</td>
                <td>{$st.country|escape:'html':'UTF-8'}</td>
                <td>{if $st.phone}<a href="tel:{$st.phone}">{$st.phone|escape:'html':'UTF-8'}</a>{/if}</td>
                <td>{if $st.email}{mailto address=$st.email|escape:'html':'UTF-8' encode="hex"}{/if}</td>
                <td class="text-center">
                    <a href="https://www.openstreetmap.org/directions?to={$st.latitude}%2C{$st.longitude}" target="_blank" rel="noopener nofollow" title="{l s='Directions' mod='tbopenstreetmap'}">
                        <i class="icon icon-car icon-lg"></i>
                    </a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>

<script>
{literal}
var shopIcon = L.icon({
    iconUrl: '{/literal}{$img_ps_dir}{$store_icon}{literal}',
    iconSize: [34, 34],
    iconAnchor: [17, 34],
    popupAnchor: [0, -35]
});
var map = L.map('osmap', {
    zoomControl: false,
    fullscreenControl: {
        pseudoFullscreen: false,
        title: {
            'false': '{/literal}{l s='View Fullscreen' mod='tbopenstreetmap'}{literal}',
            'true': '{/literal}{l s='Exit Fullscreen' mod='tbopenstreetmap'}{literal}'
        }
    }
}).setView({lon: {/literal}{$center_lon}{literal}, lat: {/literal}{$center_lat}{literal}}, 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: 'OpenStreetMap'
}).addTo(map);
L.control.zoom({
    position: 'topright',
    zoomInTitle: '{/literal}{l s='Zoom In' mod='tbopenstreetmap'}{literal}',
    zoomOutTitle: '{/literal}{l s='Zoom Out' mod='tbopenstreetmap'}{literal}'
}).addTo(map);
var markers = [
    {/literal}{foreach from=$stores item='st'}{literal}
        [ {/literal}{$st.longitude}{literal}, {/literal}{$st.latitude}{literal}, '<b>{/literal}{$st.name}{literal}</b> <br>{/literal}{$st.address1}{literal} <br>{/literal}{$st.postcode} {$st.city}{literal} <br>{/literal}{$st.country}{literal} <br><span class="phone">{/literal}{l s='Phone:' mod='tbopenstreetmap'}{literal} <a href="tel:{/literal}{$st.phone}{literal}">{/literal}{$st.phone}{literal}</a></span> <span class="email">{/literal}{l s='E-mail:' mod='tbopenstreetmap'}{literal} {/literal}{mailto address=$st.email|escape:'html':'UTF-8' encode="hex"}{literal}</span> <span class="direction"><a href="https://www.openstreetmap.org/directions?to={/literal}{$st.latitude}{literal}%2C{/literal}{$st.longitude}{literal}" target="_blank" rel="noopener nofollow">{/literal}{l s='Directions' mod='tbopenstreetmap'}{literal}</a></span>' ],
    {/literal}{/foreach}{literal}
];
for (var i=0; i<markers.length; ++i) {
    L.marker({lon: markers[i][0], lat: markers[i][1]}, {icon: shopIcon}).bindPopup(markers[i][2]).addTo(map);
}
{/literal}
</script>

<script>
$(document).ready(function() {
    var is_touch_device = ("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch;
    $('[data-toggle="osmpopover"]').popover({
        html: true,
        placement: 'top',
        trigger: is_touch_device ? "click" : "hover"
    });
});
</script>
{/if}

{* The following lines allow translations in back-office and has to stay commented
    {l s='Monday' mod='tbopenstreetmap'}
    {l s='Tuesday' mod='tbopenstreetmap'}
    {l s='Wednesday' mod='tbopenstreetmap'}
    {l s='Thursday' mod='tbopenstreetmap'}
    {l s='Friday' mod='tbopenstreetmap'}
    {l s='Saturday' mod='tbopenstreetmap'}
    {l s='Sunday' mod='tbopenstreetmap'}
*}
