<h1>update - Build #1535385242</h1>
<h2>Date: 2018-08-27</h2>
<div class="changelog">
</div>
<?php

TCMSLogChange::addInfoMessage('CSV product exports now quote the enclosure character if it is set to double quote (only!).
If an export already quotes, remove the custom quoting or overwrite TPkgShopProductExportCSVEndPoint::quoteFields() to deactivate default quoting.', TCMSLogChange::INFO_MESSAGE_LEVEL_INFO);
