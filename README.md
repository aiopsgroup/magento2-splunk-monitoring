**CX Monitoring** provides insights about log messages and order events as well as dashboards and alerts based on 100+ business KPIs.
For a free evaluation please contact us at <sales@aiopsgroup.com>

**How to Install Splunk Cloud Monitoring Extension:**
### **Install via composer (recommend)**
Run the following command in Magento 2 root folder:

1. composer require aiopsgroup/magento2-splunk-monitoring
2. php bin/magento setup:upgrade --keep-generated
3. php bin/magento setup:di:compile
4. php bin/magento cache: flush

**Enable Splunk Monitoring Extension:**

1. Go to Admin Panel and click on **Stores=>Monitoring=>Splunk Configuration**

2. Now Enable Splunk Monitoring Extension and flush Cache

**Create Admin User Role to generate admin token:**
Magento provides a separate token service for administrators. When you request a token, the service returns a unique access token in exchange for the username and password for a Magento account.

Go to Admin Panel and create a User with following mandatory Resources.
1. Splunk Monitoring
2. Sales

\*\*Share new user credentials with the AIOPSMonitoring team to start consuming API (Application Programming Interface) with shared credentials.
