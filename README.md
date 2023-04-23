**How to Install the Extension:**

Under your website root folder, run the below commands sequentially.

1. composer require aiopsgroup/magento2-splunk-monitoring
2. php bin/magento setup:upgrade --keep-generated
3. php bin/magento setup:di:compile
4. php bin/magento cache:flush
